<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InspectionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('inspections')->insert([
            [
                'id'                        => 1,
                'item_id'                   => 84,
                'inspection_scheduled_date' => '2025/10/05',
                'inspection_date'           => null,
                'status'                    => 0,
                'inspection_person'         => null,
                'details'                   => null,
                'created_at'                => Carbon::createFromFormat('Y/m/d H:i:s', '2024/10/01 15:11:00'),
                'updated_at'                => Carbon::createFromFormat('Y/m/d H:i:s', '2024/10/01 15:11:00'),
            ],
            [
                'id'                        => 2,
                'item_id'                   => 85,
                'inspection_scheduled_date' => '2025/10/05',
                'inspection_date'           => null,
                'status'                    => 0,
                'inspection_person'         => null,
                'details'                   => null,
                'created_at'                => Carbon::createFromFormat('Y/m/d H:i:s', '2024/10/01 15:11:00'),
                'updated_at'                => Carbon::createFromFormat('Y/m/d H:i:s', '2024/10/01 15:11:00'),
            ],
            [
                'id'                        => 3,
                'item_id'                   => 86,
                'inspection_scheduled_date' => '2025/10/05',
                'inspection_date'           => null,
                'status'                    => 0,
                'inspection_person'         => null,
                'details'                   => null,
                'created_at'                => Carbon::createFromFormat('Y/m/d H:i:s', '2024/10/01 15:11:00'),
                'updated_at'                => Carbon::createFromFormat('Y/m/d H:i:s', '2024/10/01 15:11:00'),
            ],
            [
                'id'                        => 4,
                'item_id'                   => 87,
                'inspection_scheduled_date' => '2025/3/27',
                'inspection_date'           => null,
                'status'                    => 0,
                'inspection_person'         => null,
                'details'                   => null,
                'created_at'                => Carbon::createFromFormat('Y/m/d H:i:s', '2024/10/01 15:11:00'),
                'updated_at'                => Carbon::createFromFormat('Y/m/d H:i:s', '2024/10/01 15:11:00'),
            ],
            [
                'id'                        => 5,
                'item_id'                   => 89,
                'inspection_scheduled_date' => '2025/10/20',
                'inspection_date'           => '2024-10-20',
                'status'                    => 1,
                'inspection_person'         => '管理者大川',
                'details'                   => "購入後最初の点検を行いました。問題はありませんでした。\n今後年に一度の定期的な点検を行います。",
                'created_at'                => Carbon::createFromFormat('Y/m/d H:i:s', '2024/10/01 15:11:00'),
                'updated_at'                => Carbon::createFromFormat('Y/m/d H:i:s', '2024/10/20 15:50:00'),
            ],
        ]);
    }
}
