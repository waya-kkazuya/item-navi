<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AcquisitionMethodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('acquisition_methods')->insert([
            [
                'name' => '購入'
            ],
            [
                'name' => 'レンタル'
            ],
            [
                'name' => 'リース'
            ],
            [
                'name' => '譲渡'
            ],
            [
                'name' => 'サブスクリプション'
            ],
            [
                'name' => 'その他'
            ],
        ]);
    }
}
