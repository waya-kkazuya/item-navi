<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ImageTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('image_tests')->insert([
            [
                'name' => 'テスト',
                'file_name' => 'storage/items/vvrfzBLIH38FgobtmDzuBw8II2lxhlVtIfzuqM3v.jpg',
            ],
        ]);
    }
}
