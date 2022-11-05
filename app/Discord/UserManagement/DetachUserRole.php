<?php

namespace App\Discord\UserManagement;

use App\Discord\Core\Command\MessageCommand;
use App\Discord\Core\EmbedFactory;
use App\Models\DiscordUser;
use App\Models\Role;

class DetachUserRole extends MessageCommand
{

    public function permission(): string
    {
        return 'attach-role';
    }

    public function trigger(): string
    {
        return 'unsetrole';
    }

    public function __construct()
    {
        $this->requiredArguments = 2;
        $this->requiresMention = true;
        $this->usageString = __('bot.roles.usage-detachrole');

        parent::__construct();
    }

    public function action(): void
    {
        if (!Role::exists($this->guildId, $this->arguments[0])) {
            $this->message->channel->sendMessage(EmbedFactory::failedEmbed(__('bot.roles.not-exist', ['role' => $this->arguments[0]])));
            return;
        }
        $role = Role::get($this->guildId, $this->arguments[0]);

        $user = DiscordUser::get($this->arguments[1]);
        $user->roles()->detach($role);

        $this->message->channel->sendMessage(EmbedFactory::successEmbed(__('bot.roles.role-detached', ['role' => $role->name, 'user' => $user->tag()])));
    }
}
