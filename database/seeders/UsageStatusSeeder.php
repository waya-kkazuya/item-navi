<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UsageStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('usage_statuses')->insert([
            [
                'name' => '未選択'
            ],
            [
                'name' => '使用中'
            ],
            [
                'name' => '未使用'
            ],
        ]);
    }
}
