<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RequestStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('request_statuses')->insert([
            [
                'status_name' => '未確認'
            ],
            [
                'status_name' => '検討中'
            ],
            [
                'status_name' => '採用'
            ],
            [
                'status_name' => '見送り'
            ],
        ]);
    }
}
