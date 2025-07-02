<?php

// ================================================================
// database/seeders/ItemSeeder.php
// Command: php artisan make:seeder ItemSeeder
// ================================================================

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ItemSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        // Hapus data lama jika ada
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('items')->truncate();
        DB::table('stocks')->truncate(); // Karena akan auto-create stocks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // ================================================================
        // ZTE MODEM ITEMS - FOKUS UTAMA
        // ================================================================

        $zteModems = [
            [
                'item_id' => 'ITM001',
                'item_code' => 'ZTE-F609',
                'item_name' => 'ZTE F609 GPON ONT Modem',
                'category_id' => 'CAT036', // ZTE F609 category
                'unit' => 'pcs',
                'min_stock' => 20,
                'description' => 'Modem ONT ZTE F609 GPON dengan 4 port Ethernet, 2 port POTS, WiFi 802.11n. Kompatibel dengan jaringan fiber optic GPON.',
                'qr_code' => null,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'item_id' => 'ITM002',
                'item_code' => 'ZTE-F660',
                'item_name' => 'ZTE F660 GPON ONT WiFi Modem',
                'category_id' => 'CAT037', // ZTE F660 category
                'unit' => 'pcs',
                'min_stock' => 15,
                'description' => 'Modem ONT ZTE F660 GPON dengan WiFi AC, 4 port Gigabit Ethernet, 2 port POTS, USB port. Dual band WiFi 2.4GHz/5GHz.',
                'qr_code' => null,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'item_id' => 'ITM003',
                'item_code' => 'ZTE-F960',
                'item_name' => 'ZTE F960 GPON ONT Dual Band WiFi',
                'category_id' => 'CAT038', // ZTE F960 category
                'unit' => 'pcs',
                'min_stock' => 10,
                'description' => 'Modem ONT ZTE F960 GPON premium dengan dual band WiFi AC1200, 4 port Gigabit Ethernet, 2 port POTS, 2 port USB 2.0.',
                'qr_code' => null,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        // Insert ZTE Modems
        DB::table('items')->insert($zteModems);

        // ================================================================
        // MODEM LAINNYA - SAMPLING
        // ================================================================

        $otherModems = [
            [
                'item_id' => 'ITM004',
                'item_code' => 'HW-HG8245H',
                'item_name' => 'Huawei HG8245H GPON ONT',
                'category_id' => 'CAT039', // Huawei HG8245H category
                'unit' => 'pcs',
                'min_stock' => 10,
                'description' => 'Modem ONT Huawei HG8245H GPON dengan 4 port Ethernet, 2 port POTS, WiFi 802.11n, USB port.',
                'qr_code' => null,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'item_id' => 'ITM005',
                'item_code' => 'FH-HG6245D',
                'item_name' => 'Fiberhome HG6245D GPON ONT',
                'category_id' => 'CAT040', // Fiberhome HG6245D category
                'unit' => 'pcs',
                'min_stock' => 8,
                'description' => 'Modem ONT Fiberhome HG6245D GPON dengan 4 port Gigabit Ethernet, 2 port POTS, WiFi dual band.',
                'qr_code' => null,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        // Insert Other Modems
        DB::table('items')->insert($otherModems);

        // ================================================================
        // ROUTER WIFI - SAMPLING
        // ================================================================

        $routers = [
            [
                'item_id' => 'ITM006',
                'item_code' => 'TPL-C6',
                'item_name' => 'TP-Link Archer C6 AC1200 WiFi Router',
                'category_id' => 'CAT041', // TP-Link Archer C6 category
                'unit' => 'pcs',
                'min_stock' => 5,
                'description' => 'Router WiFi TP-Link Archer C6 AC1200 dual band dengan 4 antena eksternal, 4 port Gigabit, MU-MIMO.',
                'qr_code' => null,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'item_id' => 'ITM007',
                'item_code' => 'ASUS-AX55',
                'item_name' => 'Asus RT-AX55 AX1800 WiFi 6 Router',
                'category_id' => 'CAT043', // Asus RT-AX55 category
                'unit' => 'pcs',
                'min_stock' => 3,
                'description' => 'Router WiFi 6 Asus RT-AX55 AX1800 dengan teknologi OFDMA, 4 antena eksternal, AiProtection.',
                'qr_code' => null,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        // Insert Routers
        DB::table('items')->insert($routers);

        // ================================================================
        // KABEL UTP - SAMPLING
        // ================================================================

        $cables = [
            [
                'item_id' => 'ITM008',
                'item_code' => 'UTP-CAT6-BLD',
                'item_name' => 'Belden Cat6 UTP Cable 305m',
                'category_id' => 'CAT056', // Belden Cat6 UTP category
                'unit' => 'roll',
                'min_stock' => 2,
                'description' => 'Kabel UTP Belden Cat6 305 meter indoor, 23 AWG, unshielded twisted pair, warna biru.',
                'qr_code' => null,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'item_id' => 'ITM009',
                'item_code' => 'UTP-CAT6-CSC',
                'item_name' => 'Commscope Cat6 UTP Cable 305m',
                'category_id' => 'CAT057', // Commscope Cat6 UTP category
                'unit' => 'roll',
                'min_stock' => 2,
                'description' => 'Kabel UTP Commscope Cat6 305 meter indoor, 23 AWG, certified performance, warna abu-abu.',
                'qr_code' => null,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        // Insert Cables
        DB::table('items')->insert($cables);

        // ================================================================
        // SWITCH & ACCESS POINT - SAMPLING
        // ================================================================

        $networkDevices = [
            [
                'item_id' => 'ITM010',
                'item_code' => 'TPL-SG1008D',
                'item_name' => 'TP-Link TL-SG1008D 8-Port Gigabit Switch',
                'category_id' => 'CAT047', // TP-Link TL-SG1008D category
                'unit' => 'pcs',
                'min_stock' => 5,
                'description' => 'Switch TP-Link TL-SG1008D 8-port Gigabit unmanaged, plug and play, casing metal.',
                'qr_code' => null,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'item_id' => 'ITM011',
                'item_code' => 'UBQ-AC-PRO',
                'item_name' => 'Ubiquiti UniFi AP AC Pro Access Point',
                'category_id' => 'CAT050', // Ubiquiti UniFi AP AC Pro category
                'unit' => 'pcs',
                'min_stock' => 3,
                'description' => 'Access Point Ubiquiti UniFi AP AC Pro dual band AC1750, PoE powered, indoor/outdoor.',
                'qr_code' => null,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        // Insert Network Devices
        DB::table('items')->insert($networkDevices);

        // ================================================================
        // ACCESSORIES & CONSUMABLES - SAMPLING
        // ================================================================

        $accessories = [
            [
                'item_id' => 'ITM012',
                'item_code' => 'RJ45-CONN',
                'item_name' => 'RJ45 Connector Cat6 (100pcs)',
                'category_id' => 'CAT018', // Connector & Jack category
                'unit' => 'box',
                'min_stock' => 10,
                'description' => 'Connector RJ45 untuk kabel Cat6, gold plated, isi 100 pieces per box.',
                'qr_code' => null,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'item_id' => 'ITM013',
                'item_code' => 'PWR-ADPT-12V',
                'item_name' => 'Power Adapter 12V 2A Universal',
                'category_id' => 'CAT010', // Power Supply category
                'unit' => 'pcs',
                'min_stock' => 15,
                'description' => 'Adaptor daya universal 12V 2A dengan berbagai tip connector untuk modem dan router.',
                'qr_code' => null,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'item_id' => 'ITM014',
                'item_code' => 'CBL-TIE-BLK',
                'item_name' => 'Cable Tie Hitam 200mm (100pcs)',
                'category_id' => 'CAT027', // Stationery IT category
                'unit' => 'pack',
                'min_stock' => 20,
                'description' => 'Cable tie warna hitam ukuran 200mm, tahan UV, isi 100 pieces per pack.',
                'qr_code' => null,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        // Insert Accessories
        DB::table('items')->insert($accessories);

        // ================================================================
        // AUTO-CREATE INITIAL STOCKS
        // ================================================================

        $allItems = array_merge($zteModems, $otherModems, $routers, $cables, $networkDevices, $accessories);
        $stocks = [];

        foreach ($allItems as $index => $item) {
            $stockId = 'STK' . str_pad($index + 1, 3, '0', STR_PAD_LEFT);

            // Tentukan initial stock berdasarkan item
            $initialStock = $this->getInitialStock($item['item_code']);

            $stocks[] = [
                'stock_id' => $stockId,
                'item_id' => $item['item_id'],
                'quantity_available' => $initialStock['available'],
                'quantity_used' => $initialStock['used'],
                'total_quantity' => $initialStock['total'],
                'last_updated' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        // Insert Stocks
        DB::table('stocks')->insert($stocks);

        $this->command->info('✅ Items berhasil di-seed!');
        $this->command->info('📊 Total items: ' . count($allItems));
        $this->command->info('🔧 Fokus: 3 ZTE Modem utama + ' . (count($allItems) - 3) . ' item sampling');
        $this->command->info('📦 Items with initial stock:');

        foreach ($allItems as $item) {
            $stock = $this->getInitialStock($item['item_code']);
            $this->command->info("   {$item['item_code']}: {$stock['total']} unit");
        }
    }

    /**
     * Get initial stock data per item
     */
    private function getInitialStock(string $itemCode): array
    {
        // Stock data berdasarkan item code
        $stockData = [
            // ZTE Modems - Stock banyak karena fokus utama
            'ZTE-F609' => ['total' => 150, 'used' => 25, 'available' => 125],
            'ZTE-F660' => ['total' => 100, 'used' => 15, 'available' => 85],
            'ZTE-F960' => ['total' => 75, 'used' => 10, 'available' => 65],

            // Other Modems - Stock sedang
            'HW-HG8245H' => ['total' => 50, 'used' => 8, 'available' => 42],
            'FH-HG6245D' => ['total' => 30, 'used' => 5, 'available' => 25],

            // Routers - Stock sedikit
            'TPL-C6' => ['total' => 25, 'used' => 3, 'available' => 22],
            'ASUS-AX55' => ['total' => 15, 'used' => 2, 'available' => 13],

            // Cables - Stock roll
            'UTP-CAT6-BLD' => ['total' => 10, 'used' => 2, 'available' => 8],
            'UTP-CAT6-CSC' => ['total' => 8, 'used' => 1, 'available' => 7],

            // Network Devices
            'TPL-SG1008D' => ['total' => 20, 'used' => 3, 'available' => 17],
            'UBQ-AC-PRO' => ['total' => 12, 'used' => 2, 'available' => 10],

            // Accessories - Stock banyak karena consumable
            'RJ45-CONN' => ['total' => 50, 'used' => 5, 'available' => 45],
            'PWR-ADPT-12V' => ['total' => 40, 'used' => 8, 'available' => 32],
            'CBL-TIE-BLK' => ['total' => 100, 'used' => 15, 'available' => 85],
        ];

        return $stockData[$itemCode] ?? ['total' => 0, 'used' => 0, 'available' => 0];
    }
}
