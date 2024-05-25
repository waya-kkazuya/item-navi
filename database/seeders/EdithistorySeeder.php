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
        $stock = 3;
        $data = [];
        $daysAgo = 30;
        $decreaseDay = rand(1, 2); // 初回の減少日をランダムに設定
        $editUsers = ['admin', 'staff', 'user'];

        for ($i = 0; $i <= $daysAgo; $i++) {
            if ($i === $decreaseDay) {
                $stock += 1; // 在庫数を増やす
                $randomUser = $editUsers[array_rand($editUsers)]; 
                $data[] = [
                    'operation_type' => '編集',
                    'edit_type' => '通常',
                    'action_type' => '出庫',
                    'item_id' => 1,
                    'category_id' => 1,
                    'edited_field'=> 'stocks',
                    'old_value' => $stock + 1,
                    'new_value'=> $stock,
                    'edit_user' => $randomUser,
                    'edited_at' => date('Y-m-d', strtotime("-$i day")), // 日付を2~3日ずつ遡る
                    'created_at' => now()
                ];
                $decreaseDay += rand(2, 3); // 次の減少日を設定
            }
        }

        DB::table('edithistories')->insert($data);



        // DB::table('edithistories')->insert([
        //     [
        //         'operation_type' => '編集',
        //         'edit_type' => '通常',
        //         'action_type' => '出庫',
        //         'item_id' => 1,
        //         'category_id' => 1,
        //         'edited_field'=> 'stocks',
        //         'old_value' => 100,
        //         'new_value'=> 99,
        //         'edit_user' => 'admin',
        //         'edited_at' => '2024*04-01',
        //     ],
        //     [
        //         'operation_type' => '編集',
        //         'edit_type' => '通常',
        //         'action_type' => '出庫',
        //         'item_id' => 1,
        //         'category_id' => 1,
        //         'edited_field'=> 'stocks',
        //         'old_value' => 99,
        //         'new_value'=> 97,
        //         'edit_user' => 'admin',
        //         'edited_at' => '2024*04-01',
        //     ],
        // ]);
    }
}
