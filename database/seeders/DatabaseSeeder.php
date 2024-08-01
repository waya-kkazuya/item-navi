<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\Category;
use App\Models\Edithistory;
use App\Models\ImageTest;
use App\Models\InventoryPlan;
use App\Models\Location;
use App\Models\Unit;
use Illuminate\Database\Seeder;
use Carbon\Carbon;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        $this->call([
            UserSeeder::class,
            CategorySeeder::class,
            UsageStatusSeeder::class,
            AcquisitionMethodSeeder::class,
            LocationSeeder::class,
            UnitSeeder::class,
            // ItemSeeder::class,
            // EdithistorySeeder::class,
            // ImageTestSeeder::class
        ]);



        // itemsとWishesのダミーデータ投入
        // \App\Models\Item::factory(100)->create();
        // \App\Models\Wish::factory(20)->create();

        // $items = \App\Models\Item::all();
        // $startDate = Carbon::create(2024, 2, 1);

        // InventoryPlan::factory(3)->create()
        // ->each(function(InventoryPlan $inventoryPlan) use($items, $startDate) {
        //     $inventoryPlan->items()->attach(
        //         $items->random(rand(25,30))->pluck('id')->toArray(),
        //         [
        //             'inventory_date' => $startDate->addDays(rand(0, 59)), // 2024年2月1日~2024年3月31日の間の期間_棚卸し期間を想定
        //             'inventory_person' => collect(['admin', 'staff', 'user'])->random(), // admin,staff,userの中のどれかの文字列
        //             'insuffcient_data_status' => (bool)random_int(0, 1), // booleanの値
        //             'insuffcient_data_details' => Str::random(20), // テキスト
        //             'unknown_assets_status' => (bool)random_int(0, 1), // booleanの値
        //             'unknown_assets_details' => Str::random(20), // テキスト
        //             'inventory_status' => (bool)random_int(0, 1), // boolean値
        //         ]
        //     );
        // });


        // 棚卸しの履歴を作成した跡
        // InventoryPlan::factory(3)->create()
        // ->each(function(InventoryPlan $inventoryPlan) use($items, $startDate) {
        //     $items->random(rand(25,30))->pluck('id')->each(function($itemId) use($inventoryPlan, $startDate) {
        //         $startDateClone = clone $startDate;
        //         $inventoryPlan->items()->attach(
        //             $itemId,
        //             [
        //                 'inventory_date' => $startDateClone->addDays(rand(0, 59)), // 2024年2月1日~2024年3月31日の間の期間_棚卸し期間を想定
        //                 'inventory_person' => collect(['admin', 'staff', 'user'])->random(), // admin,staff,userの中のどれかの文字列
        //                 'insuffcient_data_status' => (bool)random_int(0, 1), // booleanの値
        //                 'insuffcient_data_details' => Str::random(20), // テキスト
        //                 'unknown_assets_status' => (bool)random_int(0, 1), // booleanの値
        //                 'unknown_assets_details' => Str::random(20), // テキスト
        //                 'inventory_status' => (bool)random_int(0, 1), // boolean値
        //             ]
        //         );
        //     });
        // });



        // リアルな感じで均等に編集履歴が登録されるようなコード
        // \App\Models\Edithistory::factory(1000)->create();

        // $editedAt = now();
        // 2年前から徐々に日数を増やしていく(1~30日ごと)
        // $editedAt = now()->subYears(2);

        // for ($i = 0; $i < 1000; $i++) {
        //     \App\Models\Edithistory::factory()->create(['edited_at' => $editedAt]);

        //     // 3～4件ごとに日時をランダムに変更
        //     if ($i % rand(3, 4) == 0) {
        //         $editedAt = $editedAt->addDays(rand(1, 3));
        //     }
        // }

    }
}
