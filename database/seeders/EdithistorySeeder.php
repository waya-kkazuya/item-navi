<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Category;

class EdithistorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    // ItemSeedrで作成するid:1のダミーデータの在庫数の遷移を手動で作成　
    public function run(): void
    {
        // $stock = 3;
        // $data = [];
        // $daysAgo = 30;
        // $decreaseDay = rand(1, 2); // 初回の減少日をランダムに設定
        // $editUsers = ['admin', 'staff', 'user'];

        // for ($i = 0; $i <= $daysAgo; $i++) {
        //     if ($i === $decreaseDay) {
        //         $stock += 1; // 在庫数を増やす
        //         $randomUser = $editUsers[array_rand($editUsers)]; 
        //         $data[] = [
        //             'edit_type' => '通常',
        //             'operation_type' => '編集',
        //             'item_id' => 1,
        //             'category_id' => 1,
        //             'edited_field'=> 'stock',
        //             'old_value' => $stock + 1,
        //             'new_value'=> $stock,
        //             'edit_user' => $randomUser,
        //             'edited_at' => date('Y-m-d', strtotime("-$i day")), // 日付を2~3日ずつ遡る
        //             'created_at' => now()
        //         ];
        //         $decreaseDay += rand(2, 3); // 次の減少日を設定
        //     }
        // }

        // DB::table('edithistories')->insert($data);



        DB::table('edithistories')->insert([
            [
                'edit_mode' => 'normal', // 通常か、棚卸
                'operation_type' => 'create', //store(新規作成),update(編集更新),stock_in(入庫・在庫追加),stock_out(出庫・在庫消費),delete(廃棄),restore(復元)
                'item_id' => 1,
                'edited_field'=> null,
                'old_value' => null,
                'new_value'=> null,
                'edit_user' => 'admin',
                'created_at' => '2024*04-01', //created_atは処理が行われたタイミング
            ],
            [
                'edit_mode' => 'normal', // 通常か、棚卸
                'operation_type' => 'update', //store(新規作成),update(編集更新),stock_in(入庫・在庫追加),stock_out(出庫・在庫消費),delete(廃棄),restore(復元)
                'item_id' => 1,
                'edited_field'=> 'stock',
                'old_value' => 20,
                'new_value'=> 18,
                'edit_user' => 'admin',
                'created_at' => '2024*04-02', //created_atは処理が行われたタイミング
            ],
            [
                'edit_mode' => 'normal',
                'operation_type' => 'update', //store(新規作成),update(編集更新),stock_in(入庫・在庫追加),stock_out(出庫・在庫消費),delete(廃棄),restore(復元)
                'item_id' => 1,
                'edited_field'=> 'name',
                'old_value' => 'ペーパータオル1',
                'new_value'=> 'ペーパータオル',
                'edit_user' => 'admin',
                'created_at' => '2024*04-03', //created_atは処理が行われたタイミング
            ],
            [
                'edit_mode' => 'normal',
                'operation_type' => 'update', //store(新規作成),update(編集更新),stock_in(入庫・在庫追加),stock_out(出庫・在庫消費),delete(廃棄),restore(復元)
                'item_id' => 1,
                'edited_field'=> 'usage_status_id',
                'old_value' => 1,
                'new_value'=> 2,
                'edit_user' => 'admin',
                'created_at' => '2024*04-04', //created_atは処理が行われたタイミング
            ],
            [
                'edit_mode' => 'normal',
                'operation_type' => 'delete', //store(新規作成),update(編集更新),stock_in(入庫・在庫追加),stock_out(出庫・在庫消費),delete(廃棄),restore(復元)
                'item_id' => 1,
                'edited_field'=> null,
                'old_value' => null,
                'new_value'=> null,
                'edit_user' => 'admin',
                'created_at' => '2024*04-05', //created_atは処理が行われたタイミング
            ],
            [
                'edit_mode' => 'normal',
                'operation_type' => 'restore', //store(新規作成),update(編集更新),stock_in(入庫・在庫追加),stock_out(出庫・在庫消費),delete(廃棄),restore(復元)
                'item_id' => 1,
                'edited_field'=> null,
                'old_value' => null,
                'new_value'=> null,
                'edit_user' => 'admin',
                'created_at' => '2024*04-06', //created_atは処理が行われたタイミング
            ],
        ]);
    }
}
