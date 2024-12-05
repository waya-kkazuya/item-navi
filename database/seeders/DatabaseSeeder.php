<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;


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
            RequestStatusSeeder::class,
            ItemSeeder::class,
            EdithistorySeeder::class,
            InspectionSeeder::class,
            DisposalSeeder::class,
            StockTransactionSeeder::class,
            NotificationSeeder::class,
            ItemRequestSeeder::class,
        ]);
    }
}
