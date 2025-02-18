<?php

namespace App\Domain\Discord;

use App\Domain\Fun\Models\Command;
use App\Domain\Fun\Models\Reaction;
use App\Domain\Permission\Models\Role;
use App\Domain\Setting\Models\LogSetting;
use App\Domain\Setting\Models\Setting;
use Database\Factories\GuildFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Guild extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'guild_id', 'owner_id'];


    /**
     * @return GuildFactory
     */
    protected static function newFactory(): GuildFactory
    {
        return GuildFactory::new();
    }

    /**
     * @param string $guildId
     * @return mixed
     */
    public static function get(string $guildId): mixed
    {
        return self::where('guild_id', $guildId)->first();
    }

    /**
     * @return BelongsTo
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id', 'id');
    }

    /**
     * @return HasMany
     */
    public function channels(): HasMany
    {
        return $this->hasMany(Channel::class);
    }

    /**
     * @return HasMany
     */
    public function reactions(): HasMany
    {
        return $this->hasMany(Reaction::class);
    }

    /**
     * @return HasMany
     */
    public function commands(): HasMany
    {
        return $this->hasMany(Command::class);
    }

    /**
     * @return HasMany
     */
    public function roles(): HasMany
    {
        return $this->hasMany(Role::class);
    }

    /**
     * @return HasMany
     */
    public function settings(): HasMany
    {
        return $this->hasMany(Setting::class);
    }

    /**
     * @return HasMany
     */
    public function logSettings(): HasMany
    {
        return $this->hasMany(LogSetting::class);
    }
}
