<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UnitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('units')->insert([
            [
                'name' => '個'
            ],
            [
                'name' => '箱'
            ],
            [
                'name' => '袋'
            ],
            [
                'name' => 'セット'
            ],
            [
                'name' => '台'
            ],
            [
                'name' => 'ライセンス'
            ],
            [
                'name' => '本'
            ],
            [
                'name' => 'リットル'
            ],
            [
                'name' => '枚'
            ],
            [
                'name' => '束'
            ],
        ]);
    }
}
