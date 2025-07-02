<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MainSeeder extends Seeder
{
    public function run(): void
    {
        // Hapus data lama jika ada
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('users')->truncate();
        DB::table('user_levels')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Insert data baru
        $this->call([
            UserLevelSeeder::class,
            UserSeeder::class,
            CategorySeeder::class,
            SupplierSeeder::class,
            // ItemSeeder::class, // Tambahkan ini
            // PurchaseOrderSeeder::class, // Tambahkan ini
            // GoodsReceivedSeeder::class, // Tambahkan ini
        ]);

        $this->command->info('✅ User levels dan users berhasil di-seed!');
        $this->command->info('📋 Login credentials:');
        $this->command->info('   Admin: admin / admin123');
        $this->command->info('   Logistik: logistik1 / logistik123');
        $this->command->info('   Teknisi: teknisi1 / teknisi123');
    }
}
