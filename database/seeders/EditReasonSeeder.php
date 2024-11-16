<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
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
                'reason' => '入力内容に変更があったため'
            ],
            [
                'reason' => '入力内容にミスがあったため'
            ],
            [
                'reason' => 'その他（理由詳細に記載）'
            ],
        ]);
    }
}
