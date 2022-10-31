<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Timeout extends Model
{
    protected $fillable = ['discord_id', 'discord_username', 'reason', 'length', 'giver_id'];


    /**
     * @return BelongsTo
     */
    public function giver(): BelongsTo
    {
        return $this->belongsTo(DiscordUser::class);
    }

}
