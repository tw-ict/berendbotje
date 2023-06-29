<?php

namespace App\Discord\Core;

use App\Discord\Core\Enums\Setting as SettingEnum;
use App\Models\DiscordUser;
use App\Models\MentionGroup;
use App\Models\Timeout;
use Carbon\Carbon;
use Discord\Discord;
use Discord\Parts\Channel\Message;
use Discord\WebSockets\Event;
use Exception;

/**
 * @property $roleReplies       List of mention replies for this guild which require a certain role.
 * @property $noRoleReplies     List of mention replies for this guild which require NOT to have a certain role.
 * @property $lastResponses     List of responses recently used (60 sec) so no duplicates are send.
 */
class MentionResponder
{
    private Discord $discord;
    private Bot $bot;
    private string $guildId;
    private int $guildModelId;
    private array $roleReplies = [];
    private array $noRoleReplies = [];
    private array $userReplies = [];
    private array $lastResponses = [];
    private array $lastMessages = [];

    /**
     * @throws Exception
     */
    public function __construct(string $guildId, Bot $bot)
    {
        $this->discord = $bot->discord;
        $this->bot = $bot;
        $this->guildId = $guildId;
        $this->guildModelId = \App\Models\Guild::get($guildId)->id;
        $this->loadReplies();
        $this->registerMentionResponder();
    }

    /**
     * @return void
     */
    public function loadReplies(): void
    {
        $this->roleReplies = [];
        $this->noRoleReplies = [];
        foreach (MentionGroup::byGuild($this->guildId)->get() as $mentionGroup) {

            $replies = [];
            foreach ($mentionGroup->replies as $reply) {
                if ($mentionGroup->multiplier > 1) {
                    $replies = array_merge($replies, array_fill(0, $mentionGroup->multiplier, $reply->reply));
                } else {
                    $replies[] = $reply->reply;
                }
            }

            if ($mentionGroup->has_role) {
                $this->roleReplies[$mentionGroup->name] = $replies;
            } elseif ($mentionGroup->has_user) {
                $this->userReplies[$mentionGroup->name] = $replies;
            } else {
                $this->noRoleReplies[$mentionGroup->name] = $replies;
            }
        }
    }

    /**
     * @return void
     * @throws Exception
     */
    private function registerMentionResponder(): void
    {
        $this->discord->on(Event::MESSAGE_CREATE, function (Message $message, Discord $discord) {
            if ($message->author->bot || !$message->guild_id || $message->guild_id !== $this->guildId ||
                !str_contains($message->content, $discord->user->id) ||
                !$this->bot->getGuild($this->guildId)?->getSetting(SettingEnum::ENABLE_MENTION_RESPONDER)) {
                return;
            }

            if (str_contains($message->content, '?give')) {
                $message->reply('Thanks! 😎');
                return;
            }

            // $this->checkLastResponses();

            if (isset($this->lastMessages[$message->author->id])) {
                $messages = $this->lastMessages[$message->author->id];
                $now = Carbon::now();

                foreach ($messages as $index => $lastMessage) {
                    if ($now->diffInSeconds($lastMessage) > 60) {
                        unset($this->lastMessages[$message->author->id][$index]);
                    }
                }

                $messages = $this->lastMessages[$message->author->id];
                if (count($messages) === 5) {
                    $message->reply($this->getRandom($this->roleReplies['Blocked'] ?? []));
                    $this->lastMessages[$message->author->id][] = Carbon::now();
                    return;
                }

                if (count($messages) >= 6) {
                    return;
                }

                if (count($messages) >= 3) {
                    $message->reply($this->getRandom($this->roleReplies['Annoyed'] ?? []));
                    $this->lastMessages[$message->author->id][] = Carbon::now();
                    return;
                }
            }


            $roles = collect($message->member->roles);
            $responses = [];

            foreach ($this->roleReplies as $group => $replies) {
                if (is_int($group) && $roles->contains('id', $group)) {
                    $responses = array_merge($responses, $replies);
                }
            }

            foreach ($this->noRoleReplies as $group => $replies) {
                if (is_int($group) && !$roles->contains('id', $group)) {
                    $responses = array_merge($responses, $replies);
                }
            }

            foreach ($this->userReplies as $user => $replies) {
                if (is_int($user) && $message->author->id == $user) {
                    $responses = array_merge($responses, $replies);
                }
            }

            $discordUser = DiscordUser::get($message->author->id);
            $cringeCounter = $discordUser->cringeCounters()->where('guild_id', $this->guildModelId)->get()->first()->count ?? 0;
            $bumpCounter = $discordUser->bumpCounters()->where('guild_id', $this->guildModelId)->selectRaw('*, sum(count) as total')->first();
            $timeoutCounter = Timeout::byGuild($message->guild_id)->where(['discord_id' => $message->author->id])->count();

            if ($bumpCounter->total > 100) {
                $responses = array_merge($responses, $this->roleReplies['BumpCounter'] ?? []);
            }
            if ($timeoutCounter > 1) {
                $responses = array_merge($responses, $this->roleReplies['Muted'] ?? []);
            }
            if ($cringeCounter > 10) {
                $responses = array_merge($responses, $this->roleReplies['CringeCounter'] ?? []);
            }

            // Replies for everyone
            $responses = array_merge($responses, $this->roleReplies['Default'] ?? []);


            if (!empty($responses)) {
                $message->reply($this->getRandom($responses));
                $this->lastMessages[$message->author->id][] = Carbon::now();
            }
        });
    }

    /**
     * @return void
     */
    private function checkLastResponses(): void
    {
        foreach ($this->lastResponses as $lastResponse => $date) {
            $now = Carbon::now();
            if ($now->diffInSeconds($date) > 60) {
                unset($this->lastResponses[$lastResponse]);
            }
        }
    }

    /**
     * @param array $array
     * @return mixed
     * @throws Exception
     */
    private function getRandom(array $array): mixed
    {
        return $array[random_int(0, (count($array) - 1))];
//        while (isset($this->lastResponses[$response])) {
//            $response = $array[random_int(0, (count($array) - 1))];
//            if (count($this->lastResponses) === count($array)) {
//                $this->lastResponses = [];
//                break;
//            }
//        }
//        $this->lastResponses[$response] = Carbon::now();
//        return $response;
    }

}
