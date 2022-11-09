<?php

namespace App\Discord\Fun\Cringe;

use App\Discord\Core\Command\SlashAndMessageCommand;
use App\Discord\Core\EmbedFactory;
use App\Discord\Core\Permission;
use App\Models\DiscordUser;
use Discord\Builders\MessageBuilder;
use Discord\Http\Exceptions\NoPermissionsException;
use Discord\Parts\Interactions\Command\Option;

class DecreaseCringe extends SlashAndMessageCommand
{
    public function permission(): Permission
    {
        return Permission::DEL_CRINGE;
    }

    public function trigger(): string
    {
        return 'delcringe';
    }

    public function __construct()
    {
        $this->requiredArguments = 1;
        $this->requiresMention = true;
        $this->usageString = __('bot.cringe.usage-delcringe');
        $this->slashCommandOptions = [
            [
                'name' => 'user_mention',
                'description' => 'Mention',
                'type' => Option::USER,
                'required' => true,
            ],
        ];

        parent::__construct();
    }

    /**
     * @throws NoPermissionsException
     */
    public function action(): MessageBuilder
    {
        $user = DiscordUser::get($this->arguments[0]);
        $guildModel = \App\Models\Guild::get($this->guildId);
        $cringeCounters = $user->cringeCounters()->where('guild_id', $guildModel->id)->get();

        if ($cringeCounters->isEmpty()) {
            return EmbedFactory::failedEmbed(__('bot.cringe.not-cringe', ['name' => "<@{$this->arguments[0]}>"]));
        }

        $cringeCounter = $cringeCounters->first();
        $count = $cringeCounter->count - 1;
        if ($count == 0) {
            $cringeCounter->delete();
        } else {
            $cringeCounter->count = $count;
            $cringeCounter->save();
        }

        return EmbedFactory::successEmbed(__('bot.cringe.change', ['name' => $user->tag(), 'count' => $count]));
    }
}
