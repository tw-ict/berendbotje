<?php

namespace App\Discord\Fun\Models;

use App\Discord\Core\Models\DiscordUser;
use App\Discord\Core\Models\Guild;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CringeCounter extends Model
{

    protected $table = 'cringe_counter';

    protected $fillable = ['count', 'guild_id'];

    /**
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(DiscordUser::class);
    }

    /**
     * @param $guildId
     * @return mixed
     */
    public static function byGuild($guildId): mixed
    {
        return self::where('guild_id', Guild::get($guildId)->id);
    }

}