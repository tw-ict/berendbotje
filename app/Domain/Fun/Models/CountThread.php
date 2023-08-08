<?php

namespace App\Domain\Fun\Models;

use App\Domain\Discord\Guild;
use App\Domain\Discord\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class CountThread extends Model
{
    protected $table = 'count_threads';

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
     * @return BelongsToMany
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'count_thread_users', 'count_thread_id', 'user_id');
    }



}
