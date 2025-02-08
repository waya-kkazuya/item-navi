<?php

namespace Database\Seeders;

use Carbon\Carbon;
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
                'id'                     => 1,
                'request_status_id'      => 1,
                'name'                   => '加湿器',
                'category_id'            => 4,
                'location_of_use_id'     => 1,
                'requestor'              => '田中',
                'remarks_from_requestor' => '空気が乾燥しているため',
                'manufacturer'           => 'アイリスオーヤマ',
                'reference'              => 'Amazon',
                'price'                  => 4980,
                'created_at'             => Carbon::createFromFormat('Y/m/d H:i:s', '2024/10/05 10:05:00'),
                'updated_at'             => Carbon::createFromFormat('Y/m/d H:i:s', '2024/10/05 10:05:00'),
            ],
            [
                'id'                     => 2,
                'request_status_id'      => 1,
                'name'                   => '延長コード',
                'category_id'            => 4,
                'location_of_use_id'     => 2,
                'requestor'              => '鈴木',
                'remarks_from_requestor' => 'コンセントまで届かないため',
                'manufacturer'           => 'Elecom',
                'reference'              => 'Amazon',
                'price'                  => 1400,
                'created_at'             => Carbon::createFromFormat('Y/m/d H:i:s', '2024/10/15 13:11:00'),
                'updated_at'             => Carbon::createFromFormat('Y/m/d H:i:s', '2024/10/15 13:11:00'),
            ],
            [
                'id'                     => 3,
                'request_status_id'      => 1,
                'name'                   => '使い捨て手袋',
                'category_id'            => 1,
                'location_of_use_id'     => 5,
                'requestor'              => '井上',
                'remarks_from_requestor' => 'なくなったため',
                'manufacturer'           => 'NEOTRIL',
                'reference'              => 'Amazon',
                'price'                  => 1700,
                'created_at'             => Carbon::createFromFormat('Y/m/d H:i:s', '2024/11/10 11:11:00'),
                'updated_at'             => Carbon::createFromFormat('Y/m/d H:i:s', '2024/11/10 11:11:00'),
            ],
            [
                'id'                     => 4,
                'request_status_id'      => 1,
                'name'                   => '付箋（ポストイット）',
                'category_id'            => 1,
                'location_of_use_id'     => 1,
                'requestor'              => '清水',
                'remarks_from_requestor' => 'あると便利なため',
                'manufacturer'           => 'スリーエム(3M)',
                'reference'              => 'Amazon',
                'price'                  => 930,
                'created_at'             => Carbon::createFromFormat('Y/m/d H:i:s', '2024/11/25 15:20:00'),
                'updated_at'             => Carbon::createFromFormat('Y/m/d H:i:s', '2024/11/25 15:20:00'),
            ],
            [
                'id'                     => 5,
                'request_status_id'      => 1,
                'name'                   => 'マウス',
                'category_id'            => 2,
                'location_of_use_id'     => 1,
                'requestor'              => '中村',
                'remarks_from_requestor' => '壊れたため、新しいものが必要',
                'manufacturer'           => 'ロジクール',
                'reference'              => 'Amazon',
                'price'                  => 2500,
                'created_at'             => Carbon::createFromFormat('Y/m/d H:i:s', '2024/10/25 14:20:00'),
                'updated_at'             => Carbon::createFromFormat('Y/m/d H:i:s', '2024/10/25 14:20:00'),
            ],
        ]);
    }
}
