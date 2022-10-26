<?php

namespace App\Discord\SimpleReaction;

use App\Discord\Core\AccessLevels;
use App\Discord\Core\Bot;
use App\Discord\Core\EmbedBuilder;
use App\Discord\Core\SlashIndexCommand;
use App\Models\Reaction;
use Discord\Parts\Embed\Embed;

class ReactionsIndex extends SlashIndexCommand
{
    public function accessLevel(): AccessLevels
    {
        return AccessLevels::MOD;
    }

    public function trigger(): string
    {
        return 'reactions';
    }


    public function getEmbed(): Embed
    {
        $this->total = Reaction::count();
        $this->perPage = 20;

        $embedBuilder = EmbedBuilder::create(Bot::getDiscord())
            ->setTitle(__('bot.reactions.title'))
            ->setFooter(__('bot.reactions.footer'))
            ->setDescription(__('bot.reactions.description'));
        foreach (Reaction::skip($this->offset)->limit($this->perPage)->get() as $reaction) {
            $embedBuilder->getEmbed()->addField(['name' => $reaction->trigger, 'value' => $reaction->reaction, 'inline' => true]);
        }
        return $embedBuilder->getEmbed();
    }
}
