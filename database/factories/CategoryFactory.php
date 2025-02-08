<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Category>
 */
class CategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */

    protected $model = Category::class;

    public function definition(): array
    {
        $categories = [
            '消耗品',
            'IT機器',
            'ソフトウェアアカウント',
            '電化製品',
            '防災用品',
            'オフィス用品',
            'オフィス家具',
            '作業道具',
            '清掃用具',
            '食料品',
            'その他',
        ];

        return [
            'name' => $this->faker->randomElement($categories),
        ];
    }
}
