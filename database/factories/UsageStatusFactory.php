<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\UsageStatus>
 */
class UsageStatusFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $usage_statuses = ['使用中', '未使用'];

        return [
            'name' => $this->faker->randomElement($usage_statuses),
        ];
    }
}
