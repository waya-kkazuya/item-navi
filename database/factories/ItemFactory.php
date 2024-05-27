<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Item>
 */
class ItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $words1 = ['未使用', '使用中'];
        $words2 = ['作業室1', '作業室2', '玄関', '廊下', '給湯室', 'トイレ', '事務室'];
        $words3 = ['購入', 'リース（レンタル）', '譲渡', 'その他'];
        $imagePath =['no_image.jpg', 'sample1.jpg', 'sample2.jpg', 'sample3.jpg', 'sample4.jpg', 'sample5.jpg',
        'sample6.jpg', 'sample7.jpg', 'sample8.jpg', 'sample9.jpg', 'sample10.jpg',];

        return [
            'name' => $this->faker->name,
            'category_id' => $this->faker->numberBetween(1, 5),
            'image_path1' => $this->faker->randomElement($imagePath),
            'image_path2' => $this->faker->randomElement($imagePath),
            'image_path3' => $this->faker->randomElement($imagePath),
            'stocks' => $this->faker->numberBetween(1, 500),
            'usage_status' => $this->faker->randomElement($words1),
            'end_user' => $this->faker->userName,
            'location_of_use_id' => $this->faker->numberBetween(1, 11),
            'storage_location_id' => $this->faker->numberBetween(1, 11),
            'acquisition_category' => $this->faker->randomElement($words3),
            'where_to_buy' => $this->faker->name,
            'price' => $this->faker->numberBetween(100, 50000),
            'date_of_acquisition' => $this->faker->dateTime,
            'inspection_schedule' => $this->faker->dateTime,
            'disposal_schedule' => $this->faker->dateTime,
            'manufacturer' => $this->faker->name,
            'product_number' => $this->faker->numberBetween(1001, 4999),
            // 'vendor_website_url' => $this->faker->url,
            'remarks' => $this->faker->realText(20),
            'qrcode_path' => $this->faker->url
        ];
    }
}
