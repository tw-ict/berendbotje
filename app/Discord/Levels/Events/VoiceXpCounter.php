<?php

namespace App\Discord\Levels\Events;

use App\Discord\Core\DiscordEvent;
use App\Discord\Core\Enums\Setting;
use App\Discord\Core\Models\DiscordUser;
use App\Discord\Levels\Helpers\Helper;
use Discord\Discord;
use Discord\Parts\WebSockets\VoiceStateUpdate as DVoiceStateUpdate;
use Discord\WebSockets\Event;

class VoiceXpCounter extends DiscordEvent
{
    /**
     * @param $guild
     * @param $user
     * @param $oldstate
     * @return void
     */
    private function leaveVoice($guild, $user, $oldstate): void
    {
        if ($guild->getSetting(Setting::ENABLE_VOICE_XP)) {
            $duration = $guild->leftVoice($oldstate->user_id);
            $xp = $guild->getSetting(Setting::VOICE_XP_COUNT);
            $cooldown = $guild->getSetting(Setting::XP_COOLDOWN);
            $amount = round(($duration / $cooldown) * $xp);

            $messageCounters = $user->messageCounters()->where('guild_id', $guild->model->id)->get();
            $messageCounter = new \App\Discord\Levels\Models\UserXP([
                'count' => 0,
                'guild_id' => $guild->model->id,
                'xp' => $amount,
                'voice_seconds' => $duration,
            ]);

            if ($messageCounters->isEmpty()) {
                $user->messageCounters()->save($messageCounter);
            } else {
                $messageCounter = $messageCounters->first();
                $messageCounter->update([
                    'xp' => $messageCounter->xp + $amount,
                    'voice_seconds' => $messageCounter->voice_seconds + $duration,
                ]);
            }
            $messageCounter->update(['level' => Helper::calcLevel($messageCounter->xp)]);
        }
    }

    public function registerEvent(): void
    {
        $this->discord->on(Event::VOICE_STATE_UPDATE, function (DVoiceStateUpdate $state, Discord $discord, $oldstate) {
            $guild = $this->bot->getGuild($state->guild_id ?? $oldstate->guild_id);
            $user = DiscordUser::get($state->user_id);

            if ($state->channel) {
                if (!isset($oldstate)) {
                    if (!$guild->getChannel($state->channel_id) && !$state->self_mute && !$state->self_deaf) {
                        $guild->joinedVoice($state->user_id);
                    }
                } elseif ($guild->getChannel($state->channel_id) && $guild->getChannel($state->channel_id)->no_xp) {
                    $this->leaveVoice($guild, $user, $state);
                } else if ($state->self_mute || $state->self_deaf) {
                    $this->leaveVoice($guild, $user, $state);
                } elseif ($oldstate->self_mute || $oldstate->self_deaf || $guild->getChannel($oldstate->channel_id)) {
                    $guild->joinedVoice($state->user_id);
                }
            } else {
                $this->leaveVoice($guild, $user, $state);
            }
        });
    }
}