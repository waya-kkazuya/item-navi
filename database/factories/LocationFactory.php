<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Location>
 */
class LocationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $locations = ['作業室１', '作業室２', '廊下', '給湯室', 'トイレ', '玄関',
                '階段', '休憩室', '相談室', '事務所', '倉庫', 'その他'];

        return [
            'name' => $this->faker->randomElement($locations)
        ];
    }
}
