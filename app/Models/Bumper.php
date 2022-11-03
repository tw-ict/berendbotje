<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Bumper extends Model
{
    protected $fillable = ['count'];


    public static function byGuild($guildId)
    {
        return Bumper::whereHas('user', function (Builder $query) use ($guildId) {
            $query->where('guild_id', '=', $guildId);
        });
    }

    /**
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(DiscordUser::class);
    }
}


