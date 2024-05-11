<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\Category;
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
            ImageTestSeeder::class
        ]);

        \App\Models\Item::factory(100)->create();
        \App\Models\Wish::factory(20)->create();
        \App\Models\Edithistory::factory(1000)->create();
    }
}
