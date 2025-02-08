<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AcquisitionMethod>
 */
class AcquisitionMethodFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $aquisition_methods = ['購入', 'レンタル', 'リース', '譲渡', 'サブスクリプション', 'その他'];

        return [
            'name' => $this->faker->randomElement($aquisition_methods),
        ];
    }
}
