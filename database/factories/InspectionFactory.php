<?php

namespace Database\Factories;

use App\Models\Item;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Inspection>
 */
class InspectionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'item_id' => Item::factory(),
            'scheduled_date' => $this->faker->dateTime(),
            'inspection_date' => $this->faker->dateTime(),
            'status' => $this->faker->boolean(),
            'inspection_person' => $this->faker->name(),
            'details' => $this->faker->realText(100),
        ];
    }
}
