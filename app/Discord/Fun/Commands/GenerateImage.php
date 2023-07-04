<?php

namespace App\Discord\Fun\Commands;

use App\Discord\Core\SlashCommand;
use App\Discord\Fun\Jobs\ProcessImageGenration;
use App\Discord\Roles\Enums\Permission;
use Discord\Builders\MessageBuilder;
use Discord\Parts\Interactions\Command\Option;

class GenerateImage extends SlashCommand
{

    public function permission(): Permission
    {
        return Permission::OPENAI;
    }

    public function trigger(): string
    {
        return 'image';
    }

    public function __construct()
    {
        $this->description = __('bot.slash.gen-image');
        $this->slashCommandOptions = [
            [
                'name' => 'prompt',
                'description' => 'prompt',
                'type' => Option::STRING,
                'required' => true,
            ],
        ];

        parent::__construct();

    }

    public function action(): MessageBuilder
    {
        ProcessImageGenration::dispatch($this->interaction->channel_id, $this->getOption('prompt'));
        return MessageBuilder::new()->setContent("Generating Image with prompt _{$this->getOption('prompt')}_");
    }
}