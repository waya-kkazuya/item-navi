<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DisposalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('disposals')->insert([
            [
                'id'                      => 1,
                'item_id'                 => 11,
                'disposal_scheduled_date' => null,
                'disposal_date'           => '2024-10-15',
                'disposal_person'         => '管理者大川',
                'details'                 => '壊れたため、廃棄します。',
                'created_at'              => Carbon::createFromFormat('Y/m/d H:i:s', '2024/10/15 15:04:00'),
                'updated_at'              => Carbon::createFromFormat('Y/m/d H:i:s', '2024/10/15 15:52:00'),
            ],
            [
                'id'                      => 2,
                'item_id'                 => 26,
                'disposal_scheduled_date' => null,
                'disposal_date'           => '2024-10-15',
                'disposal_person'         => '管理者大川',
                'details'                 => '壊れたため、廃棄します。',
                'created_at'              => Carbon::createFromFormat('Y/m/d H:i:s', '2024/10/15 15:05:00'),
                'updated_at'              => Carbon::createFromFormat('Y/m/d H:i:s', '2024/10/15 15:53:00'),
            ],
            [
                'id'                      => 3,
                'item_id'                 => 73,
                'disposal_scheduled_date' => '2025-04-05',
                'disposal_date'           => null,
                'disposal_person'         => null,
                'details'                 => null,
                'created_at'              => Carbon::createFromFormat('Y/m/d H:i:s', '2024/10/01 15:10:00'),
                'updated_at'              => Carbon::createFromFormat('Y/m/d H:i:s', '2024/10/01 15:10:00'),
            ],
            [
                'id'                      => 4,
                'item_id'                 => 88,
                'disposal_scheduled_date' => '2026-03-25',
                'disposal_date'           => null,
                'disposal_person'         => null,
                'details'                 => null,
                'created_at'              => Carbon::createFromFormat('Y/m/d H:i:s', '2024/10/01 15:11:00'),
                'updated_at'              => Carbon::createFromFormat('Y/m/d H:i:s', '2024/10/01 15:11:00'),
            ],
        ]);
    }
}
