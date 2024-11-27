<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ItemRequestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('item_requests')->insert([
            [
                'request_status_id' => 1,
                'name' => '空気清浄機',
                'category_id' => 4,
                'location_of_use_id' => 1,
                'requestor' => '田中',
                'remarks_from_requestor' => '空気が乾燥しているため',
                'manufacturer' => 'シャープ',
                'reference' => 'Amazon',
                'price' => 21000,
                'created_at' => '2024/10/05 10:05:00'
            ],
            [
                'request_status_id' => 2,
                'name' => '延長コード',
                'category_id' => 11,
                'location_of_use_id' => 2,
                'requestor' => '鈴木',
                'remarks_from_requestor' => '観葉植物',
                'manufacturer' => '',
                'reference' => 'Amazon',
                'price' => 10000,
                'created_at' => '2024/10/15 13:11:00'
            ],
            [
                'request_status_id' => 2,
                'name' => '使い捨て手袋',
                'category_id' => 1,
                'location_of_use_id' => 5,
                'requestor' => '井上',
                'remarks_from_requestor' => 'なくなったため',
                'manufacturer' => 'NEOTRIL',
                'reference' => 'Amazon',
                'price' => 1700,
                'created_at' => '2024/11/10 11:11:00'
            ],
            [
                'request_status_id' => 2,
                'name' => '付箋（ポストイット）',
                'category_id' => 1,
                'location_of_use_id' => 1,
                'requestor' => '清水',
                'remarks_from_requestor' => 'あると便利なため',
                'manufacturer' => 'スリーエム(3M)',
                'reference' => 'Amazon',
                'price' => 930,
                'created_at' => '2021/11/25 15:20:00'
            ],
        ]);
    }
}
