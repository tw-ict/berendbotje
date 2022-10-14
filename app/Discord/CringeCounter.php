<?php

namespace App\Discord;

use App\Models\Admin;
use App\Models\Bumper;
use App\Models\Reaction;
use Discord\Discord;
use Discord\Parts\Channel\Message;
use Discord\WebSockets\Event;

class CringeCounter
{
    public function __construct(Discord $discord, Bot $bot)
    {
        $discord->on(Event::MESSAGE_CREATE, function (Message $message, Discord $discord) use ($bot) {
            if ($message->author->bot) {
                return;
            }

            if (str_starts_with($message->content, $bot->getPrefix() . 'cringestats')) {
                foreach (\App\Models\CringeCounter::orderBy('count', 'desc')->limit(10)->get() as $cringeCounter) {
                    $message->channel->sendMessage($cringeCounter->discord_username . ' -> ' . $cringeCounter->count);
                }
            }

            if (str_starts_with($message->content, $bot->getPrefix() . 'cringe ')) {
                $parameters = explode(' ', $message->content);
                if (!isset($parameters[1])) {
                    $message->channel->sendMessage("Provide arguments noob");
                }
                $cringeCounter = \App\Models\CringeCounter::where(['discord_tag' => $parameters[1]])->first();

                if ($cringeCounter) {
                    $message->channel->sendMessage("Cringe counter for " . $cringeCounter->discord_tag . " is " . $cringeCounter->count);
                } else {
                    $message->channel->sendMessage($parameters[1] . " isn't cringe..");
                }

            }

            if (str_starts_with($message->content, $bot->getPrefix() . 'addcringe ')) {
                if (!Admin::isAdmin($message->author->id)) {
                    return;
                }
                $parameters = explode(' ', $message->content);
                if (!isset($parameters[1])) {
                    $message->channel->sendMessage("Provide arguments noob");
                } else {
                    $cringeCounter = \App\Models\CringeCounter::where(['discord_tag' => $parameters[1]])->first();

                    if ($cringeCounter) {
                        $cringeCounter->count = $cringeCounter->count + 1;
                        $cringeCounter->save();
                    } else {
                        $cringeCounter = \App\Models\CringeCounter::create([
                            'discord_tag' => $parameters[1],
                            'discord_id' => $parameters[1],
                            'discord_username' => $parameters[1],
                            'count' => 1
                        ]);
                    }


                    $message->channel->sendMessage("Cringe counter for " . $cringeCounter->discord_tag . " increased to " . $cringeCounter->count);


                }
            }


        });
    }

}
