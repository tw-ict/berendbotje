<?php

namespace App\Discord\Fun\Music;

use App\Discord\Core\AccessLevels;
use App\Discord\Core\MessageCommand;

class AddSong extends MessageCommand
{
    public function accessLevel(): AccessLevels
    {
        return AccessLevels::USER;
    }

    public function trigger(): string
    {
        return 'addsong';
    }

    public function __construct()
    {
        parent::__construct();
        $this->requiredArguments = 1;
        $this->usageString = __('bot.music.usage-addsong');
    }

    public function action(): void
    {
        $musicPlayer = MusicPlayer::getPlayer();
        $musicPlayer->addToQueue($this->arguments[0]);
        $this->message->channel->sendMessage(__('bot.music.added'));
    }
}