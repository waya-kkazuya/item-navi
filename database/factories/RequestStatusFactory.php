<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\RequestStatus>
 */
class RequestStatusFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $request_statuses = [
            '未確認',
            '検討中',
            '採用',
            '見送り'
        ];

        return [
            'status_name' => $this->faker->randomElement($request_statuses),
        ];
    }
}
