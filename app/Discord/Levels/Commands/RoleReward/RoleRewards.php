<?php

namespace App\Discord\Levels\Commands\RoleReward;

use App\Discord\Core\Builders\EmbedBuilder;
use App\Discord\Core\SlashIndexCommand;
use App\Domain\Fun\Models\RoleReward;
use App\Domain\Permission\Enums\Permission;
use Discord\Parts\Embed\Embed;
use Discord\Parts\Interactions\Interaction;
use Exception;

class RoleRewards extends SlashIndexCommand
{

    public function permission(): Permission
    {
        return Permission::NONE;
    }

    public function trigger(): string
    {
        return 'list';
    }

    public function __construct()
    {
        $this->description = __('bot.slash.rewards');
        parent::__construct();
    }

    /**
     * @return Embed
     * @throws Exception
     */
    public function getEmbed(): Embed
    {
        $this->total = RoleReward::byGuild($this->guildId)->count();

        $description = "";
        foreach (RoleReward::byGuild($this->guildId)->orderBy('level', 'desc')->skip($this->getOffset($this->getLastUser()))->limit($this->perPage)->get() as $index => $roleReward) {
            $description .= "**Level {$roleReward->level}** • {$roleReward->roleTag()} \n";
        }
        return EmbedBuilder::create($this, __('bot.rewards.title'), __('bot.rewards.description', ['rewards' => $description]))->getEmbed();
    }

    /**
     * @param Interaction $interaction
     * @return array
     */
    public function autoComplete(Interaction $interaction): array
    {
        return [];
    }
}
