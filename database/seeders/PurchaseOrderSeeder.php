<?php
// ================================================================
// 1. database/seeders/PurchaseOrderSeeder.php
// Command: php artisan make:seeder PurchaseOrderSeeder
// ================================================================

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PurchaseOrderSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        // Hapus data lama jika ada
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('po_details')->truncate();
        DB::table('purchase_orders')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // ================================================================
        // PURCHASE ORDERS
        // ================================================================

        $purchaseOrders = [
            // PO 1 - ZTE Modem Order (Status: received - sudah selesai)
            [
                'po_id' => 'PO000001',
                'po_number' => 'PO2025001',
                'supplier_id' => 'SUP001', // Assuming supplier ada
                'po_date' => $now->copy()->subDays(15)->toDateString(),
                'expected_date' => $now->copy()->subDays(5)->toDateString(),
                'status' => 'received',
                'total_amount' => 2750000.00,
                'notes' => 'Order modem ZTE untuk project deployment Q1 2025',
                'created_by' => 'USR001', // Admin
                'created_at' => $now->copy()->subDays(15),
                'updated_at' => $now->copy()->subDays(2),
            ],

            // PO 2 - Router & Switch Order (Status: partial - sebagian diterima)
            [
                'po_id' => 'PO000002',
                'po_number' => 'PO2025002',
                'supplier_id' => 'SUP002',
                'po_date' => $now->copy()->subDays(10)->toDateString(),
                'expected_date' => $now->copy()->addDays(5)->toDateString(),
                'status' => 'partial',
                'total_amount' => 1850000.00,
                'notes' => 'Order perangkat networking untuk kantor cabang',
                'created_by' => 'USR002', // Logistik
                'created_at' => $now->copy()->subDays(10),
                'updated_at' => $now->copy()->subDays(3),
            ],

            // PO 3 - Cable & Accessories (Status: sent - sudah dikirim ke supplier)
            [
                'po_id' => 'PO000003',
                'po_number' => 'PO2025003',
                'supplier_id' => 'SUP001',
                'po_date' => $now->copy()->subDays(5)->toDateString(),
                'expected_date' => $now->copy()->addDays(10)->toDateString(),
                'status' => 'sent',
                'total_amount' => 850000.00,
                'notes' => 'Order kabel dan aksesoris untuk maintenance',
                'created_by' => 'USR002', // Logistik
                'created_at' => $now->copy()->subDays(5),
                'updated_at' => $now->copy()->subDays(5),
            ],

            // PO 4 - Urgent Low Stock Order (Status: draft - masih draft)
            [
                'po_id' => 'PO000004',
                'po_number' => 'PO2025004',
                'supplier_id' => 'SUP001',
                'po_date' => $now->toDateString(),
                'expected_date' => $now->copy()->addDays(7)->toDateString(),
                'status' => 'draft',
                'total_amount' => 1200000.00,
                'notes' => 'Urgent order untuk item dengan stok rendah',
                'created_by' => 'USR002', // Logistik
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        // Insert Purchase Orders
        DB::table('purchase_orders')->insert($purchaseOrders);

        // ================================================================
        // PO DETAILS
        // ================================================================

        $poDetails = [
            // PO 1 Details - ZTE Modem Order
            [
                'po_detail_id' => 'POD000001',
                'po_id' => 'PO000001',
                'item_id' => 'ITM001', // ZTE F609
                'quantity_ordered' => 50,
                'unit_price' => 350000.00,
                'total_price' => 17500000.00,
                'quantity_received' => 50, // Fully received
                'notes' => 'Modem untuk deployment area Jakarta',
                'created_at' => $now->copy()->subDays(15),
                'updated_at' => $now->copy()->subDays(2),
            ],
            [
                'po_detail_id' => 'POD000002',
                'po_id' => 'PO000001',
                'item_id' => 'ITM002', // ZTE F660
                'quantity_ordered' => 25,
                'unit_price' => 500000.00,
                'total_price' => 12500000.00,
                'quantity_received' => 25, // Fully received
                'notes' => 'Modem WiFi untuk area premium',
                'created_at' => $now->copy()->subDays(15),
                'updated_at' => $now->copy()->subDays(2),
            ],

            // PO 2 Details - Router & Switch Order
            [
                'po_detail_id' => 'POD000003',
                'po_id' => 'PO000002',
                'item_id' => 'ITM006', // TP-Link Archer C6
                'quantity_ordered' => 15,
                'unit_price' => 850000.00,
                'total_price' => 12750000.00,
                'quantity_received' => 10, // Partially received
                'notes' => 'Router untuk kantor cabang',
                'created_at' => $now->copy()->subDays(10),
                'updated_at' => $now->copy()->subDays(3),
            ],
            [
                'po_detail_id' => 'POD000004',
                'po_id' => 'PO000002',
                'item_id' => 'ITM010', // TP-Link Switch
                'quantity_ordered' => 20,
                'unit_price' => 450000.00,
                'total_price' => 9000000.00,
                'quantity_received' => 20, // Fully received
                'notes' => 'Switch 8 port untuk jaringan lokal',
                'created_at' => $now->copy()->subDays(10),
                'updated_at' => $now->copy()->subDays(3),
            ],

            // PO 3 Details - Cable & Accessories
            [
                'po_detail_id' => 'POD000005',
                'po_id' => 'PO000003',
                'item_id' => 'ITM008', // UTP Cat6 Belden
                'quantity_ordered' => 5,
                'unit_price' => 1200000.00,
                'total_price' => 6000000.00,
                'quantity_received' => 0, // Not received yet
                'notes' => 'Kabel UTP untuk instalasi baru',
                'created_at' => $now->copy()->subDays(5),
                'updated_at' => $now->copy()->subDays(5),
            ],
            [
                'po_detail_id' => 'POD000006',
                'po_id' => 'PO000003',
                'item_id' => 'ITM012', // RJ45 Connector
                'quantity_ordered' => 20,
                'unit_price' => 45000.00,
                'total_price' => 900000.00,
                'quantity_received' => 0, // Not received yet
                'notes' => 'Connector untuk terminasi kabel',
                'created_at' => $now->copy()->subDays(5),
                'updated_at' => $now->copy()->subDays(5),
            ],

            // PO 4 Details - Urgent Low Stock Order
            [
                'po_detail_id' => 'POD000007',
                'po_id' => 'PO000004',
                'item_id' => 'ITM003', // ZTE F960
                'quantity_ordered' => 20,
                'unit_price' => 600000.00,
                'total_price' => 12000000.00,
                'quantity_received' => 0, // Not received yet (still draft)
                'notes' => 'Urgent restock untuk ZTE F960',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        // Insert PO Details
        DB::table('po_details')->insert($poDetails);

        $this->command->info('✅ Purchase Orders berhasil di-seed!');
        $this->command->info('📊 Total PO: ' . count($purchaseOrders));
        $this->command->info('📦 Total PO Details: ' . count($poDetails));
        $this->command->info('🔄 Status: draft(1), sent(1), partial(1), received(1)');
    }
}
