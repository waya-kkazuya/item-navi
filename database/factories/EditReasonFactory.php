<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\EditReason>
 */
class EditReasonFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $edit_reasons = ['入力の修正', '在庫数の修正', '画像の変更', '日付の変更', 'その他'];

        return [
            'reason' => $this->faker->randomElement($edit_reasons)
        ];
    }
}
