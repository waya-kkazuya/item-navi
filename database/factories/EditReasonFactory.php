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
        $edit_reasons = [
            '新しい情報の追加のため',
            '入力内容にミスがあったため',
            '利用状況が変わったため',
            '使用者が変わったため',
            '利用場所が変わったため',
            '保管場所が変わったため',
            '点検の予定日が決まったため',
            '廃棄の予定日が決まったため',
            '備品の状態に変化があったため',
            'その他（理由詳細に記載）',
        ];

        return [
            'reason' => $this->faker->randomElement($edit_reasons),
        ];
    }
}
