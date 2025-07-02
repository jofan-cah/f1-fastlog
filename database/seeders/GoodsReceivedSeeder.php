<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class GoodsReceivedSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        // Hapus data lama jika ada
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('item_details')->truncate();
        DB::table('goods_received_details')->truncate();
        DB::table('goods_receiveds')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // ================================================================
        // GOODS RECEIVED
        // ================================================================

        $goodsReceived = [
            // GR 1 - Complete receiving dari PO1 (ZTE Modem)
            [
                'gr_id' => 'GR000001',
                'receive_number' => 'GR2025001',
                'po_id' => 'PO000001',
                'supplier_id' => 'SUP001',
                'receive_date' => $now->copy()->subDays(2)->toDateString(),
                'status' => 'complete',
                'notes' => 'Penerimaan lengkap modem ZTE sesuai PO. Kondisi barang baik.',
                'received_by' => 'USR002', // Logistik
                'created_at' => $now->copy()->subDays(2),
                'updated_at' => $now->copy()->subDays(2),
            ],

            // GR 2 - Partial receiving dari PO2 (Router & Switch) - Pengiriman 1
            [
                'gr_id' => 'GR000002',
                'receive_number' => 'GR2025002',
                'po_id' => 'PO000002',
                'supplier_id' => 'SUP002',
                'receive_date' => $now->copy()->subDays(3)->toDateString(),
                'status' => 'partial',
                'notes' => 'Penerimaan sebagian - Switch datang lengkap, Router baru 10 dari 15 unit.',
                'received_by' => 'USR002', // Logistik
                'created_at' => $now->copy()->subDays(3),
                'updated_at' => $now->copy()->subDays(3),
            ],
        ];

        // Insert Goods Received
        DB::table('goods_receiveds')->insert($goodsReceived);

        // ================================================================
        // GOODS RECEIVED DETAILS
        // ================================================================

        $grDetails = [
            // GR 1 Details - ZTE Modem Complete Receiving
            [
                'gr_detail_id' => 'GRD000001',
                'gr_id' => 'GR000001',
                'item_id' => 'ITM001', // ZTE F609
                'quantity_received' => 50,
                'quantity_to_stock' => 30, // 30 unit ke gudang
                'quantity_to_ready' => 20, // 20 unit langsung siap pakai
                'unit_price' => 350000.00,
                'batch_number' => 'ZTE2025A001',
                'expiry_date' => null, // Elektronik tidak expire
                'notes' => 'Split: 30 unit ke gudang, 20 unit untuk deployment immediate',
                'created_at' => $now->copy()->subDays(2),
                'updated_at' => $now->copy()->subDays(2),
            ],
            [
                'gr_detail_id' => 'GRD000002',
                'gr_id' => 'GR000001',
                'item_id' => 'ITM002', // ZTE F660
                'quantity_received' => 25,
                'quantity_to_stock' => 25, // Semua ke gudang
                'quantity_to_ready' => 0,
                'unit_price' => 500000.00,
                'batch_number' => 'ZTE2025B002',
                'expiry_date' => null,
                'notes' => 'Semua unit masuk gudang untuk stock',
                'created_at' => $now->copy()->subDays(2),
                'updated_at' => $now->copy()->subDays(2),
            ],

            // GR 2 Details - Partial Router & Switch Receiving
            [
                'gr_detail_id' => 'GRD000003',
                'gr_id' => 'GR000002',
                'item_id' => 'ITM006', // TP-Link Archer C6
                'quantity_received' => 10, // Hanya 10 dari 15 yang dipesan
                'quantity_to_stock' => 7, // 7 unit ke gudang
                'quantity_to_ready' => 3, // 3 unit langsung pakai
                'unit_price' => 850000.00,
                'batch_number' => 'TPL2025C001',
                'expiry_date' => null,
                'notes' => 'Partial delivery - 5 unit sisanya menyusul minggu depan',
                'created_at' => $now->copy()->subDays(3),
                'updated_at' => $now->copy()->subDays(3),
            ],
            [
                'gr_detail_id' => 'GRD000004',
                'gr_id' => 'GR000002',
                'item_id' => 'ITM010', // TP-Link Switch
                'quantity_received' => 20, // Complete delivery
                'quantity_to_stock' => 15, // 15 unit ke gudang
                'quantity_to_ready' => 5, // 5 unit langsung install
                'unit_price' => 450000.00,
                'batch_number' => 'TPL2025D001',
                'expiry_date' => null,
                'notes' => 'Complete delivery - 5 unit untuk instalasi immediate di cabang',
                'created_at' => $now->copy()->subDays(3),
                'updated_at' => $now->copy()->subDays(3),
            ],
        ];

        // Insert GR Details
        DB::table('goods_received_details')->insert($grDetails);

        // ================================================================
        // ITEM DETAILS - Individual Tracking per Unit
        // ================================================================

        $itemDetails = [];
        $detailCounter = 1;

        // Generate ItemDetails untuk setiap unit yang diterima
        foreach ($grDetails as $grDetail) {
            $itemCode = $this->getItemCode($grDetail['item_id']);

            for ($i = 1; $i <= $grDetail['quantity_received']; $i++) {
                $serialNumber = $this->generateSerialNumber($itemCode, $i);
                $customAttributes = $this->getCustomAttributes($grDetail['item_id'], $i);

                // Tentukan status berdasarkan split
                $status = 'available';
                $location = 'GUDANG-A';

                if ($i <= $grDetail['quantity_to_ready']) {
                    $status = 'used'; // Langsung terpakai
                    $location = 'DEPLOYMENT';
                }

                $itemDetails[] = [
                    'item_detail_id' => 'ITD' . str_pad($detailCounter, 8, '0', STR_PAD_LEFT),
                    'gr_detail_id' => $grDetail['gr_detail_id'],
                    'item_id' => $grDetail['item_id'],
                    'serial_number' => $serialNumber,
                    'custom_attributes' => json_encode($customAttributes),
                    'qr_code' => null, // Will be generated later if needed
                    'status' => $status,
                    'location' => $location,
                    'notes' => $this->getItemNotes($grDetail['item_id'], $status),
                    'created_at' => $grDetail['created_at'],
                    'updated_at' => $grDetail['updated_at'],
                ];

                $detailCounter++;
            }
        }

        // Insert Item Details
        DB::table('item_details')->insert($itemDetails);

        $this->command->info('✅ Goods Received & Item Details berhasil di-seed!');
        $this->command->info('📊 Total GR: ' . count($goodsReceived));
        $this->command->info('📦 Total GR Details: ' . count($grDetails));
        $this->command->info('🔍 Total Item Details: ' . count($itemDetails));
        $this->command->info('📋 Individual Tracking Summary:');

        $availableCount = count(array_filter($itemDetails, fn($item) => $item['status'] === 'available'));
        $usedCount = count(array_filter($itemDetails, fn($item) => $item['status'] === 'used'));

        $this->command->info("   Available: {$availableCount} units");
        $this->command->info("   Used: {$usedCount} units");
        $this->command->info("   Total Individual Items: " . count($itemDetails) . " units");
    }

    // Helper method untuk get item code
    private function getItemCode($itemId): string
    {
        $itemCodes = [
            'ITM001' => 'ZTE-F609',
            'ITM002' => 'ZTE-F660',
            'ITM006' => 'TPL-C6',
            'ITM010' => 'TPL-SG1008D',
        ];

        return $itemCodes[$itemId] ?? 'UNKNOWN';
    }

    // Helper method untuk generate serial number
    private function generateSerialNumber($itemCode, $sequence): string
    {
        $year = date('Y');
        return "{$itemCode}-{$year}-" . str_pad($sequence, 3, '0', STR_PAD_LEFT);
    }

    // Helper method untuk custom attributes per item type
    private function getCustomAttributes($itemId, $sequence): array
    {
        $attributes = [];

        switch ($itemId) {
            case 'ITM001': // ZTE F609
                $attributes = [
                    'firmware_version' => '1.0.0P6T4_LAN',
                    'mac_address' => 'AA:BB:CC:DD:' . str_pad(dechex($sequence), 2, '0', STR_PAD_LEFT) . ':' . str_pad(dechex($sequence + 10), 2, '0', STR_PAD_LEFT),
                    'power_consumption' => '12W',
                    'wifi_capability' => 'No',
                    'ports' => '4x LAN + 2x POTS',
                ];
                break;

            case 'ITM002': // ZTE F660
                $attributes = [
                    'firmware_version' => '2.0.0P2T1_WIFI',
                    'mac_address' => 'BB:CC:DD:EE:' . str_pad(dechex($sequence), 2, '0', STR_PAD_LEFT) . ':' . str_pad(dechex($sequence + 20), 2, '0', STR_PAD_LEFT),
                    'power_consumption' => '15W',
                    'wifi_capability' => 'Yes - 802.11n',
                    'wifi_password' => 'admin' . $sequence,
                    'ports' => '4x LAN + 2x POTS + WiFi',
                ];
                break;

            case 'ITM006': // TP-Link Archer C6
                $attributes = [
                    'firmware_version' => '1.2.1 Build 20210326',
                    'mac_address' => 'CC:DD:EE:FF:' . str_pad(dechex($sequence), 2, '0', STR_PAD_LEFT) . ':' . str_pad(dechex($sequence + 30), 2, '0', STR_PAD_LEFT),
                    'power_consumption' => '20W',
                    'wifi_capability' => 'Yes - AC1200 Dual Band',
                    'wifi_ssid_2g' => 'TP-Link_' . strtoupper(dechex($sequence)),
                    'wifi_ssid_5g' => 'TP-Link_5G_' . strtoupper(dechex($sequence)),
                    'ports' => '4x LAN + 1x WAN',
                ];
                break;

            case 'ITM010': // TP-Link Switch
                $attributes = [
                    'firmware_version' => 'N/A (Unmanaged)',
                    'mac_address' => 'DD:EE:FF:AA:' . str_pad(dechex($sequence), 2, '0', STR_PAD_LEFT) . ':' . str_pad(dechex($sequence + 40), 2, '0', STR_PAD_LEFT),
                    'power_consumption' => '8W',
                    'switching_capacity' => '16 Gbps',
                    'ports' => '8x Gigabit Ethernet',
                    'auto_negotiation' => 'Yes',
                ];
                break;
        }

        return $attributes;
    }

    // Helper method untuk item notes
    private function getItemNotes($itemId, $status): string
    {
        $itemNames = [
            'ITM001' => 'ZTE F609',
            'ITM002' => 'ZTE F660',
            'ITM006' => 'TP-Link Archer C6',
            'ITM010' => 'TP-Link Switch',
        ];

        $itemName = $itemNames[$itemId] ?? 'Unknown Item';

        if ($status === 'used') {
            return "Unit {$itemName} langsung deploy untuk project urgent";
        }

        return "Unit {$itemName} masuk gudang dalam kondisi baik";
    }
}
