<?php

namespace App\Discord\Core;

use App\Models\Setting;
use Carbon\Carbon;
use \App\Models\Guild as GuildModel;

/**
 * Guild settings are loaded on boot and only updated when the actual setting is changed using commands.
 *
 * When a new command or reaction is added a new instance of either class is instantiated. I cannot manually destroy
 * these instances when the command or reaction is deleted, so I keep track of them here and make sure they do not fire.
 * @see SimpleCommand
 * @see SimpleReaction
 * @property $deletedCommands   List deleted commands so they do not trigger.
 * @property $deletedReactions  List of deleted reactions so they do not rigger.
 * @property $mediaChannel      List of channels marked as media, add or remove any channels whenever you like.
 *
 * @TODO find better solution for deleted commands and reactions.. probably step away from having a single instance per trigger
 */
class Guild
{
    private array $mediaChannels = [];
    private array $deletedCommands = [];
    private array $deletedReactions = [];
    private array $settings = [];
    private array $lastMessages = [];
    public GuildModel $model;

    /**
     * @param GuildModel $guild
     */
    public function __construct(GuildModel $guild)
    {
        $this->model = $guild;

        foreach ($this->model->settings as $setting) {
            $this->settings[$setting->key] = $setting->value;
        }

        foreach ($this->model->mediaChannels as $channel) {
            $this->mediaChannels[$channel->channel] = $channel->channel;
        }

    }

    /**
     * @param string $channel
     * @return void
     */
    public function addMediaChannel(string $channel): void
    {
        $this->mediaChannels[$channel] = $channel;
    }

    /**
     * @param string $channel
     * @return void
     */
    public function delMediaChannel(string $channel): void
    {
        unset($this->mediaChannels[$channel]);
    }


    /**
     * @param string $userId
     * @return Carbon
     */
    public function getLastMessage(string $userId): Carbon
    {
        if (isset($this->lastMessages[$userId])) {
            return $this->lastMessages[$userId];
        }
        return Carbon::now()->subMinutes(100);
    }

    /**
     * @param string $userId
     * @return void
     */
    public function setLastMessage(string $userId): void
    {
        $this->lastMessages[$userId] = Carbon::now();
    }


    /**
     * @param string $key
     * @param $value
     * @return void
     */
    public function setSetting(string $key, $value): void
    {
        $this->settings[$key] = $value;

        $setting = Setting::getSetting($key, $this->model->guild_id);


        $setting->value = $value;
        $setting->save();
    }

    /**
     * @param string $setting
     * @return false|mixed
     */
    public function getSetting(string $setting): mixed
    {
        return $this->settings[$setting] ?? "";
    }

    /**
     * @param string $command
     * @return void
     */
    public function deleteCommand(string $command): void
    {
        $this->deletedCommands[] = strtolower($command);
    }

    /**
     * @param string $reaction
     * @return void
     */
    public function deleteReaction(string $reaction): void
    {
        $this->deletedReactions[] = strtolower($reaction);
    }

    /**
     * @return array
     */
    public function getSettings(): array
    {
        return $this->settings ?? [];
    }

    /**
     * @return array
     */
    public function getMediaChannels(): array
    {
        return $this->mediaChannels ?? [];
    }

    /**
     * @return array
     */
    public function getDeletedCommands(): array
    {
        return $this->deletedCommands;
    }

    /**
     * @return array
     */
    public function getDeletedReactions(): array
    {
        return $this->deletedReactions;
    }

    /**
     * @return array
     */
    public function getLastMessages(): array
    {
        return $this->lastMessages;
    }
}
