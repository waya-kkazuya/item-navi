<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Category;
use App\Models\Location;
use App\Models\RequestStatus;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ItemRequest>
 */
class ItemRequestFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'request_status_id' => RequestStatus::factory(),
            'name' => $this->faker->name(),
            'category_id' => Category::factory(),
            'location_of_use_id' => Location::factory(),
            'requestor' => $this->faker->name(),
            'remarks_from_requestor' => $this->faker->realText(100),
            'manufacturer' => $this->faker->sentence(2),
            'reference' => $this->faker->name(),
            'price' => $this->faker->numberBetween(100, 50000),
        ];
    }
}
