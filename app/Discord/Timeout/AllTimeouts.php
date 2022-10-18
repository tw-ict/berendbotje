<?php

namespace App\Discord\Timeout;

use App\Discord\Core\AccessLevels;
use App\Discord\Core\Bot;
use App\Discord\Core\Command;
use App\Discord\Core\EmbedBuilder;
use App\Models\Timeout;

class AllTimeouts extends Command
{
    public function accessLevel(): AccessLevels
    {
        return AccessLevels::MOD;
    }

    public function trigger(): string
    {
        return 'timeouts';
    }

    public function action(): void
    {
        $embed = EmbedBuilder::create(Bot::getDiscord(),
            __('bot.timeout.title'),
            __('bot.timeout.footer'),
            '');
        $embed->setDescription(__('bot.timeout.count', ['count' => Timeout::count()]));
        foreach (Timeout::limit(10)->orderBy('created_at', 'desc')->get() as $timeout) {
            $embed = TimeoutHelper::timeoutLength($embed, $timeout);
        }
        $this->message->channel->sendEmbed($embed);
    }
}