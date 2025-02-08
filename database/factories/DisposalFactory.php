<?php

namespace Database\Factories;

use App\Models\Item;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Disposal>
 */
class DisposalFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'item_id'                 => Item::factory(),
            'disposal_scheduled_date' => $this->faker->dateTime(),
            'disposal_date'           => $this->faker->dateTime(),
            'disposal_person'         => $this->faker->name(),
            'details'                 => $this->faker->realText(100),
        ];
    }
}
