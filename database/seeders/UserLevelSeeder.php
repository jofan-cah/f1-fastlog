<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class UserLevelSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $userLevels = [
            [
                'user_level_id' => 'LVL001',
                'level_name' => 'Admin',
                'description' => 'Administrator sistem dengan akses penuh ke semua fitur',
                'permissions' => json_encode([
                    'dashboard' => ['read'],
                    'users' => ['create', 'read', 'update', 'delete'],
                    'user_levels' => ['create', 'read', 'update', 'delete'],
                    'categories' => ['create', 'read', 'update', 'delete'],
                    'suppliers' => ['create', 'read', 'update', 'delete'],
                    'items' => ['create', 'read', 'update', 'delete'],
                    'purchase_orders' => ['create', 'read', 'update', 'delete', 'approve'],
                    'goods_receiveds' => ['create', 'read', 'update', 'delete'],
                    'stocks' => ['create', 'read', 'update', 'delete', 'adjust'],
                    'transactions' => ['create', 'read', 'update', 'delete', 'approve'],
                    'reports' => ['read', 'export'],
                    'activity_logs' => ['read'],
                    'qr_scanner' => ['read', 'scan'],
                    'settings' => ['read', 'update']
                ]),
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'user_level_id' => 'LVL002',
                'level_name' => 'Logistik',
                'description' => 'Staff logistik untuk mengelola inventory, PO, dan penerimaan barang',
                'permissions' => json_encode([
                    'dashboard' => ['read'],
                    'categories' => ['read'],
                    'suppliers' => ['create', 'read', 'update'],
                    'items' => ['create', 'read', 'update'],
                    'purchase_orders' => ['create', 'read', 'update'],
                    'goods_receiveds' => ['create', 'read', 'update'],
                    'stocks' => ['read', 'adjust'],
                    'transactions' => ['read', 'approve'],
                    'reports' => ['read'],
                    'qr_scanner' => ['read', 'scan']
                ]),
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'user_level_id' => 'LVL003',
                'level_name' => 'Teknisi',
                'description' => 'Teknisi untuk menggunakan barang dan melakukan permintaan',
                'permissions' => json_encode([
                    'dashboard' => ['read'],
                    'items' => ['read'],
                    'stocks' => ['read'],
                    'transactions' => ['create', 'read'],
                    'qr_scanner' => ['read', 'scan']
                ]),
                'created_at' => $now,
                'updated_at' => $now,
            ]
        ];

        DB::table('user_levels')->insert($userLevels);
    }
}
