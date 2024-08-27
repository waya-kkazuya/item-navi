<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ItemRequestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('item_requests')->insert([
            [
                'name' => 'ペーパータオルA',
                'category_id' => 2,
                'location_of_use_id' => 4,
                'requestor' => '田中',
                'remarks_from_requestor' => 'なくなったため',
                'request_status' => '検討中',
                'manufacturer' => 'テスト',
                'reference' => 'Amazon',
                'price' => 500,
                'created_at' => '2021/01/01 11:11:11'
            ],
            [
                'name' => 'ペーパータオルB',
                'category_id' => 2,
                'location_of_use_id' => 4,
                'requestor' => '田中',
                'remarks_from_requestor' => 'なくなったため',
                'request_status' => '検討中',
                'manufacturer' => 'テスト',
                'reference' => 'Amazon',
                'price' => 500,
                'created_at' => '2021/01/01 11:11:11'
            ],
        ]);
    }
}
