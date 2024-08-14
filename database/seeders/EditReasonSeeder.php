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
                'reason' => '入力の修正'
            ],
            [
                'reason' => '在庫数の修正'
            ],
            [
                'reason' => '画像の変更'
            ],
            [
                'reason' => 'その他'
            ],
        ]);
    }
}
