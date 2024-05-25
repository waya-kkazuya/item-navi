<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\Category;
use App\Models\Edithistory;
use App\Models\ImageTest;
use Illuminate\Database\Seeder;

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
            CategorySeeder::class,
            UserSeeder::class,
            ItemSeeder::class,
            EdithistorySeeder::class,
            ImageTestSeeder::class
        ]);

        \App\Models\Item::factory(100)->create();
        \App\Models\Wish::factory(20)->create();


        // \App\Models\Edithistory::factory(1000)->create();

        // $editedAt = now();
        // 2年前から徐々に日数を増やしていく(1~30日ごと)
        $editedAt = now()->subYears(2);

        for ($i = 0; $i < 1000; $i++) {
            \App\Models\Edithistory::factory()->create(['edited_at' => $editedAt]);

            // 3～4件ごとに日時をランダムに変更
            if ($i % rand(3, 4) == 0) {
                $editedAt = $editedAt->addDays(rand(1, 3));
            }
        }
    }
}
