<?php

namespace App\Discord\Bump;

use App\Discord\Core\AccessLevels;
use App\Discord\Core\Bot;
use App\Discord\Core\Command\SlashAndMessageIndexCommand;
use App\Discord\Core\EmbedBuilder;
use App\Discord\Helper;
use App\Models\Bumper;
use Discord\Parts\Embed\Embed;

class BumpStatistics extends SlashAndMessageIndexCommand
{
    public function accessLevel(): AccessLevels
    {
        return AccessLevels::NONE;
    }

    public function trigger(): string
    {
        return 'bumpstats';
    }


    public function getEmbed(): Embed
    {
        $this->total = Bumper::count();
        $description = "";
        foreach (Bumper::orderBy('count', 'desc')->skip($this->offset)->limit($this->perPage)->get() as $index => $bumper) {
            $description .= Helper::indexPrefix($index);
            $description .= "**{$bumper->user->discord_tag}** •  {$bumper->count}\n";
        }
        return EmbedBuilder::create(Bot::get()->discord())
            ->setTitle(__('bot.bump.title'))
            ->setFooter(__('bot.bump.footer'))
            ->setDescription(__('bot.bump.description', ['bumpers' => $description]))
            ->getEmbed();

    }
}
