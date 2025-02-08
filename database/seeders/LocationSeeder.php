<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('locations')->insert([
            [
                'name' => '作業室１',
            ],
            [
                'name' => '作業室２',
            ],
            [
                'name' => '廊下',
            ],
            [
                'name' => '給湯室',
            ],
            [
                'name' => 'トイレ',
            ],
            [
                'name' => '玄関',
            ],
            [
                'name' => '階段',
            ],
            [
                'name' => '休憩室',
            ],
            [
                'name' => '相談室',
            ],
            [
                'name' => '事務所',
            ],
            [
                'name' => '倉庫',
            ],
            [
                'name' => 'その他',
            ],
        ]);
    }
}
