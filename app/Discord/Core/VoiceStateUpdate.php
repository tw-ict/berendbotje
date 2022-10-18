<?php

namespace App\Discord\Core;

use Discord\Discord;
use Discord\WebSockets\Event;
use Discord\Parts\WebSockets\VoiceStateUpdate as DVoiceStateUpdate;

/**
 * For some reason the voice_states in the server is not updated after a user switches channel. It should update
 * when you add "Intents::GUILD_VOICE_STATES" to the initial call, yet it does not. So this simple
 * piece of code checks who switched channel and updates the voice_states accordingly.
 */
class VoiceStateUpdate
{
    public function __construct()
    {
        Bot::getDiscord()->on(Event::VOICE_STATE_UPDATE, function (DVoiceStateUpdate $state, Discord $discord, $oldstate) {
            if ($state->channel) {
                foreach ($state->channel->guild->voice_states as $voiceState) {
                    if ($voiceState->user_id === $state->user_id) {
                        $voiceState->channel_id = $state->channel_id;
                    }
                }
            }
        });
    }
}