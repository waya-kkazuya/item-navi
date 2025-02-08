<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Unit>
 */
class UnitFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $units = ['個', '箱', '袋', 'セット', '台', 'ライセンス', '本', 'リットル', '枚', '束'];

        return [
            'name' => $this->faker->randomElement($units),
        ];
    }
}
