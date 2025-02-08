<?php

namespace Database\Factories;

use App\Models\Item;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\StockTransaction>
 */
class StockTransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'item_id'          => Item::factory(),
            'transaction_type' => $this->faker->randomElement(['入庫', '出庫', '登録']),
            'quantity'         => $this->faker->numberBetween(1, 10),
            'operator_name'    => $this->faker->name(),
        ];
    }
}
