<?php

namespace Database\Seeders;

use App\Domain\Fun\Models\GuildCounts;
use App\Domain\Permission\Models\Permission;
use Illuminate\Database\Seeder;
use App\Domain\Discord\Guild;


class GuildCountSeeder extends Seeder
{
     /**
     * @param Guild $guildModel
     * @return void
     */
    public function processSettings(Guild $guildModel): void
    {
        GuildCounts::factory()->create([
            'current_count_id' => 1,
            'guild_id' => $guildModel->id,
        ]);
    }
}
