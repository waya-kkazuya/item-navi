<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Wish>
 */
class WishFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $words2 = ['作業室1', '作業室2', '玄関', '廊下', '給湯室', 'トイレ', '事務室'];
        $words4 = ['未確認', '検討中', '採用', '見送り'];

        return [
            'name' => $this->faker->name,
            'category_id' => $this->faker->numberBetween(1, 5),
            'vendor' => $this->faker->name,
            'location_of_use' => $this->faker->randomElement($words2),
            'storage_location' => $this->faker->randomElement($words2),
            'price' => $this->faker->numberBetween(100, 50000),
            'reference_site_url' => $this->faker->url,
            'applicant' => $this->faker->name,
            'comment_from_applicant' => $this->faker->realText(20),
            'decision_status' => $this->faker->randomElement($words4),
            'comment_from_administrator' => $this->faker->realText(20),
        ];
    }
}
