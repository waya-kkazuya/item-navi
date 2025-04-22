<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('users')->insert([
            [
                'name'     => 'ゲストユーザー',
                'email'    => 'guest@guest.com',
                'password' => Hash::make(env('GUEST_PASSWORD')),
                'role'     => 0,
            ],
            [
                'name'     => '管理者大川',
                'email'    => 'admin@admin.com',
                'password' => Hash::make(env('ADMIN_PASSWORD')),
                'role'     => 1,
            ],
            [
                'name'     => 'staff',
                'email'    => 'staff@staff.com',
                'password' => Hash::make(env('USER_PASSWORD')),
                'role'     => 5,
            ],
            [
                'name'     => 'user',
                'email'    => 'user@user.com',
                'password' => Hash::make(env('USER_PASSWORD')),
                'role'     => 9,
            ],
        ]);
    }
}
