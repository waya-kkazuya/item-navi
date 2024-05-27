<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Category;

class ItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('items')->insert([
            [
                'name' => 'ペーパータオル',
                // 'category' => '消耗品',
                'category_id' => 1,
                'image_path1' => 'sample1.jpg',
                'image_path2' => 'sample4.jpg',
                'image_path3' => 'sample5.jpg',
                'stocks' => '100',
                'usage_status' => '使用中',
                'end_user' => '後藤',
                'location_of_use_id' => 4,
                'storage_location_id' => 11,
                'acquisition_category' => '購入',
                'where_to_buy' => 'Amazon',
                'price' => '500',
                'date_of_acquisition' => '2021/01/01 11:11:11',
                'inspection_schedule' => '2021/07/01 11:11:11',
                'disposal_schedule' => '2021/12/01 11:11:11',
                'manufacturer' => 'エリエール',
                'product_number' => '8001',
                'remarks' => 'あいうえおさしすせそたちつてと',
                'qrcode_path' => 'qrcode_pathてすと',
                'created_at' => '2021/01/01 11:11:11'
            ],
            [
                'name' => 'ノートパソコン',
                'category_id' => Category::all()->random()->id,
                'image_path1' => 'sample2.jpg',
                'image_path2' => 'sample6.jpg',
                'image_path3' => 'sample7.jpg',
                'stocks' => '20',
                'usage_status' => '使用中',
                'end_user' => '伊地知',
                'location_of_use_id' => 1,
                'storage_location_id' => 11,
                'acquisition_category' => '購入',
                'where_to_buy' => 'Amazon',
                'price' => '30000',
                'date_of_acquisition' => '2021/01/01 11:11:11',
                'inspection_schedule' => '2021/07/01 11:11:11',
                'disposal_schedule' => '2021/12/01 11:11:11',
                'manufacturer' => 'HP',
                'product_number' => '1001',
                'remarks' => 'あいうえおさしすせそたちつてと',
                'qrcode_path' => 'qrcode_pathてすと',
                'created_at' => '2021/01/01 11:11:11'
            ],
            [
                'name' => 'オフィス机',
                'category_id' => Category::all()->random()->id,
                'image_path1' => 'sample3.jpg',
                'image_path2' => 'sample8.jpg',
                'image_path3' => 'sample9.jpg',
                'stocks' => '5',
                'usage_status' => '使用中',
                'end_user' => '山田',
                'location_of_use_id' => 2,
                'storage_location_id' => 11,
                'acquisition_category' => '購入',
                'where_to_buy' => '楽天',
                'price' => '10000',
                'date_of_acquisition' => '2021/01/01 11:11:11',
                'inspection_schedule' => '2021/07/01 11:11:11',
                'disposal_schedule' => '2021/12/01 11:11:11',
                'manufacturer' => 'ニトリ',
                'product_number' => '5001',
                'remarks' => 'あいうえおさしすせそたちつてと',
                'qrcode_path' => 'qrcode_pathてすと',
                'created_at' => '2021/01/01 11:11:11'
            ],
        ]);
    }
}
