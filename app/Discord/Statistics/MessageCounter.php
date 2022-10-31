<?php

namespace App\Discord\Statistics;

use App\Discord\Core\Bot;
use App\Models\DiscordUser;
use Carbon\Carbon;
use Discord\Discord;
use Discord\Parts\Channel\Message;
use Discord\WebSockets\Event;

class MessageCounter
{
    public function __construct()
    {
        Bot::getDiscord()->on(Event::MESSAGE_CREATE, function (Message $message, Discord $discord) {
            if ($message->author->bot) {
                return;
            }
            $lastMessageDate = Bot::get()->getLastMessage($message->author->id);

            if ($lastMessageDate->diffInSeconds(Carbon::now()) >= Bot::get()->getSetting('xp_cooldown')) {
                $user = DiscordUser::firstOrCreate([
                    'discord_id' => $message->author->id,
                    'discord_tag' => "<@{$message->author->id}>",
                ]);
                Bot::get()->setLastMessage($message->author->id);
                if ($user->messageCounter) {
                    $user->messageCounter()->update(['count' => $user->messageCounter->count + 1]);
                    Bot::get()->setLastMessage($message->author->id);
                } else {
                    $user->messageCounter()->save(new \App\Models\MessageCounter(['count' => 1]));
                }
            }
        });
    }

}
