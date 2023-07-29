<?php

namespace App\Discord\CustomMessages\Models;

use App\Discord\Core\Models\Guild;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomMessage extends Model
{
    use HasFactory;

    protected $fillable = ['message', 'level', 'type', 'guild_id'];

    /**
     * @param $guildId
     * @return mixed
     */
    public static function byGuild($guildId): mixed
    {
        return self::where('guild_id', Guild::get($guildId)->id);
    }

    public static function level($guildId): mixed
    {
        return self::byGuild($guildId)->where('type', \App\Discord\CustomMessages\Enums\CustomMessage::LEVEL->value);
    }

    public static function welcome($guildId): mixed
    {
        return self::byGuild($guildId)->where('type', \App\Discord\CustomMessages\Enums\CustomMessage::WELCOME->value);
    }
}
