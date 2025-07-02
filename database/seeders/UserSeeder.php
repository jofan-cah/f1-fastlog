<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $users = [
            [
                'user_id' => 'USR001',
                'username' => 'admin',
                'email' => 'admin@sistem-logistik.com',
                'email_verified_at' => $now,
                'password' => Hash::make('admin123'),
                'full_name' => 'Administrator Sistem',
                'user_level_id' => 'LVL001',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'user_id' => 'USR002',
                'username' => 'logistik1',
                'email' => 'logistik@sistem-logistik.com',
                'email_verified_at' => $now,
                'password' => Hash::make('logistik123'),
                'full_name' => 'Staff Logistik 1',
                'user_level_id' => 'LVL002',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'user_id' => 'USR003',
                'username' => 'teknisi1',
                'email' => 'teknisi@sistem-logistik.com',
                'email_verified_at' => $now,
                'password' => Hash::make('teknisi123'),
                'full_name' => 'Teknisi 1',
                'user_level_id' => 'LVL003',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'user_id' => 'USR004',
                'username' => 'logistik2',
                'email' => 'logistik2@sistem-logistik.com',
                'email_verified_at' => $now,
                'password' => Hash::make('logistik123'),
                'full_name' => 'Staff Logistik 2',
                'user_level_id' => 'LVL002',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'user_id' => 'USR005',
                'username' => 'teknisi2',
                'email' => 'teknisi2@sistem-logistik.com',
                'email_verified_at' => $now,
                'password' => Hash::make('teknisi123'),
                'full_name' => 'Teknisi 2',
                'user_level_id' => 'LVL003',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        ];

        DB::table('users')->insert($users);
    }
}
