<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('categories')->insert([
            [
                'name' => '消耗品'
            ],
            [
                'name' => 'IT機器'
            ],
            [
                'name' => 'ソフトウェアアカウント'
            ],
            [
                'name' => '電化製品'
            ],
            [
                'name' => '防災用品'
            ],
            [
                'name' => 'オフィス用品'
            ],
            [
                'name' => 'オフィス家具'
            ],
            [
                'name' => '作業道具'
            ],
            [
                'name' => '清掃用具'
            ],
            [
                'name' => '食料品'
            ],
            [
                'name' => 'その他'
            ],
        ]);
    }
}
