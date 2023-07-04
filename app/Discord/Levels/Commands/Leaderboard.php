<?php

namespace App\Discord\Levels\Commands;

use App\Discord\Core\Builders\EmbedBuilder;
use App\Discord\Core\SlashIndexCommand;
use App\Discord\Levels\Helpers\Helper;
use App\Discord\Roles\Enums\Permission;
use Discord\Parts\Embed\Embed;
use Exception;

class Leaderboard extends SlashIndexCommand
{

    public function permission(): Permission
    {
        return Permission::NONE;
    }

    public function trigger(): string
    {
        return 'leaderboard';
    }

    public function __construct()
    {
        $this->description = __('bot.slash.leaderboard');
        parent::__construct();
    }

    /**
     * @return Embed
     * @throws Exception
     */
    public function getEmbed(): Embed
    {
        $this->total = \App\Discord\Levels\Models\UserXP::byGuild($this->guildId)->count();
        $description = "";
        foreach (\App\Discord\Levels\Models\UserXP::byGuild($this->guildId)->orderBy('xp', 'desc')->skip($this->getOffset($this->getLastUser()))->limit($this->perPage)->get() as $index => $messageCounter) {
            $description .= Helper::indexPrefix($index, $this->getOffset($this->getLastUser()));
            $description .= "Level **{$messageCounter->level}** • {$messageCounter->user->tag()} • {$messageCounter->xp} xp \n";
        }
        return EmbedBuilder::create($this, __('bot.messages.title'), __('bot.messages.description', ['users' => $description]))->getEmbed();
    }
}