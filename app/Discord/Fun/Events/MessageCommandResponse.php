<?php

namespace App\Discord\Fun\Events;

use App\Discord\Core\Bot;
use App\Discord\Core\Enums\Setting;
use App\Discord\Core\Guild;
use App\Discord\Core\Interfaces\MessageCreateAction;
use App\Discord\Moderation\Models\Channel;
use Discord\Http\Exceptions\NoPermissionsException;
use Discord\Parts\Channel\Message;

class MessageCommandResponse implements MessageCreateAction
{
    /**
     * @throws NoPermissionsException
     */
    public function execute(Bot $bot, Guild $guildModel, Message $message, ?Channel $channel): void
    {
        if (!$guildModel->getSetting(Setting::ENABLE_COMMANDS)) {
            return;
        }
        $guildModel->model->refresh();
        foreach ($guildModel->model->commands as $command) {
            if (strtolower($message->content) === strtolower($command->trigger)) {
                $message->channel->sendMessage($command->response);
            }
        }
    }
}