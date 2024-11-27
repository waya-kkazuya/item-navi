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
        $this->call([
            UserSeeder::class,
            CategorySeeder::class,
            UsageStatusSeeder::class,
            AcquisitionMethodSeeder::class,
            LocationSeeder::class,
            UnitSeeder::class,
            EditReasonSeeder::class,
            ItemSeeder::class,
            EdithistorySeeder::class,
            RequestStatusSeeder::class,
            ItemRequestSeeder::class,
        ]);
    }
}
