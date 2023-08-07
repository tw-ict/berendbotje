<?php

namespace App\Domain\Fun\Models;

use App\Domain\Discord\Guild;
use App\Domain\Discord\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Database\Factories\GuildCountsFactory;

class GuildCounts extends Model
{
    use HasFactory;

    protected $table = 'guild_count_threads';

    protected $fillable = ['guild_id'];


    /**
     * @param $guildId
     * @return mixed
     */
    public static function byGuild($guildId): mixed
    {
        return self::where('guild_id', Guild::get($guildId)->id);
    }

    /**
     * @return GuildCountsFactory
     */
    protected static function newFactory(): GuildCountsFactory
    {
        return GuildCountsFactory::new();
    }

}
