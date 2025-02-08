<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EditReasonSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('edit_reasons')->insert([
            [
                'reason' => '新しい情報の追加のため',
            ],
            [
                'reason' => '入力内容にミスがあったため',
            ],
            [
                'reason' => '利用状況が変わったため',
            ],
            [
                'reason' => '使用者が変わったため',
            ],
            [
                'reason' => '利用場所が変わったため',
            ],
            [
                'reason' => '保管場所が変わったため',
            ],
            [
                'reason' => '点検の予定日が決まったため',
            ],
            [
                'reason' => '廃棄の予定日が決まったため',
            ],
            [
                'reason' => '備品の状態に変化があったため',
            ],
            [
                'reason' => 'その他（理由詳細に記載）',
            ],
        ]);
    }
}
