<?php

namespace Database\Factories;

use App\Domain\Fun\Models\GuildCounts;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Discord\Guild>
 */
class GuildCountsFactory extends Factory
{
    protected $model = GuildCounts::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'guild_id' => $this->faker->randomKey,
            'current_count_id' => $this->faker->randomKey,
        ];
    }
}
