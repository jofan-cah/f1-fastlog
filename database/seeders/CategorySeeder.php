<?php

// ================================================================
// database/seeders/CategorySeeder.php
// Command: php artisan make:seeder CategorySeeder
// ================================================================

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        // Hapus data lama jika ada
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('categories')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // ================================================================
        // PARENT CATEGORIES (Level 1)
        // ================================================================

        $parentCategories = [
            [
                'category_id' => 'CAT001',
                'category_name' => 'Elektronik',
                'description' => 'Perangkat elektronik dan komponen listrik',
                'parent_id' => null,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'category_id' => 'CAT002',
                'category_name' => 'Networking',
                'description' => 'Perangkat jaringan dan infrastruktur IT',
                'parent_id' => null,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'category_id' => 'CAT003',
                'category_name' => 'Kabel & Koneksi',
                'description' => 'Kabel, connector, dan aksesoris koneksi',
                'parent_id' => null,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'category_id' => 'CAT004',
                'category_name' => 'Peralatan Kantor',
                'description' => 'Peralatan dan perlengkapan kantor',
                'parent_id' => null,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'category_id' => 'CAT005',
                'category_name' => 'Tools & Equipment',
                'description' => 'Alat kerja dan peralatan teknis',
                'parent_id' => null,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'category_id' => 'CAT006',
                'category_name' => 'Consumables',
                'description' => 'Barang habis pakai dan supplies',
                'parent_id' => null,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        ];

        // Insert parent categories
        DB::table('categories')->insert($parentCategories);

        // ================================================================
        // CHILD CATEGORIES (Level 2)
        // ================================================================

        $childCategories = [
            // Elektronik Children
            [
                'category_id' => 'CAT007',
                'category_name' => 'Komputer & Laptop',
                'description' => 'PC, laptop, dan aksesoris komputer',
                'parent_id' => 'CAT001',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'category_id' => 'CAT008',
                'category_name' => 'Monitor & Display',
                'description' => 'Monitor, TV, proyektor, dan perangkat display',
                'parent_id' => 'CAT001',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'category_id' => 'CAT009',
                'category_name' => 'Storage & Memory',
                'description' => 'Hard disk, SSD, RAM, flash drive',
                'parent_id' => 'CAT001',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'category_id' => 'CAT010',
                'category_name' => 'Power Supply',
                'description' => 'PSU, UPS, stabilizer, dan adaptor',
                'parent_id' => 'CAT001',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],

            // Networking Children
            [
                'category_id' => 'CAT011',
                'category_name' => 'Router & Modem',
                'description' => 'Router, modem, dan gateway internet',
                'parent_id' => 'CAT002',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'category_id' => 'CAT012',
                'category_name' => 'Switch & Hub',
                'description' => 'Network switch, hub, dan splitter',
                'parent_id' => 'CAT002',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'category_id' => 'CAT013',
                'category_name' => 'Wireless Equipment',
                'description' => 'Access point, antenna, dan perangkat wireless',
                'parent_id' => 'CAT002',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'category_id' => 'CAT014',
                'category_name' => 'Network Security',
                'description' => 'Firewall, VPN gateway, security appliance',
                'parent_id' => 'CAT002',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],

            // Kabel & Koneksi Children
            [
                'category_id' => 'CAT015',
                'category_name' => 'Kabel UTP',
                'description' => 'Kabel UTP Cat5e, Cat6, Cat6a, patch cord',
                'parent_id' => 'CAT003',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'category_id' => 'CAT016',
                'category_name' => 'Kabel Fiber Optic',
                'description' => 'Kabel fiber, patch cord fiber, pigtail',
                'parent_id' => 'CAT003',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'category_id' => 'CAT017',
                'category_name' => 'Kabel Power',
                'description' => 'Kabel listrik, extension, power cord',
                'parent_id' => 'CAT003',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'category_id' => 'CAT018',
                'category_name' => 'Connector & Jack',
                'description' => 'RJ45, fiber connector, keystone jack',
                'parent_id' => 'CAT003',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],

            // Peralatan Kantor Children
            [
                'category_id' => 'CAT019',
                'category_name' => 'Printer & Scanner',
                'description' => 'Printer, scanner, fax, dan multifungsi',
                'parent_id' => 'CAT004',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'category_id' => 'CAT020',
                'category_name' => 'Furniture IT',
                'description' => 'Rak server, meja komputer, cabinet',
                'parent_id' => 'CAT004',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'category_id' => 'CAT021',
                'category_name' => 'Telepon & Komunikasi',
                'description' => 'Telepon, headset, intercom, PABX',
                'parent_id' => 'CAT004',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],

            // Tools & Equipment Children
            [
                'category_id' => 'CAT022',
                'category_name' => 'Network Tools',
                'description' => 'Cable tester, crimping tool, punch down',
                'parent_id' => 'CAT005',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'category_id' => 'CAT023',
                'category_name' => 'Measurement Tools',
                'description' => 'Multimeter, oscilloscope, power meter',
                'parent_id' => 'CAT005',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'category_id' => 'CAT024',
                'category_name' => 'Hand Tools',
                'description' => 'Obeng, tang, solder, toolkit',
                'parent_id' => 'CAT005',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],

            // Consumables Children
            [
                'category_id' => 'CAT025',
                'category_name' => 'Cartridge & Toner',
                'description' => 'Tinta printer, toner, ribbon',
                'parent_id' => 'CAT006',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'category_id' => 'CAT026',
                'category_name' => 'Cleaning Supplies',
                'description' => 'Cleaning kit, alcohol, tissue, blower',
                'parent_id' => 'CAT006',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'category_id' => 'CAT027',
                'category_name' => 'Stationery IT',
                'description' => 'Label, cable tie, tape, mounting',
                'parent_id' => 'CAT006',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        ];

        // Insert child categories
        DB::table('categories')->insert($childCategories);

        // ================================================================
        // GRANDCHILD CATEGORIES (Level 3) - Examples
        // ================================================================

        $grandchildCategories = [
            // Komputer & Laptop subcategories
            [
                'category_id' => 'CAT028',
                'category_name' => 'Desktop PC',
                'description' => 'PC desktop, workstation, mini PC',
                'parent_id' => 'CAT007',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'category_id' => 'CAT029',
                'category_name' => 'Laptop & Notebook',
                'description' => 'Laptop, notebook, ultrabook',
                'parent_id' => 'CAT007',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'category_id' => 'CAT030',
                'category_name' => 'Server Hardware',
                'description' => 'Server rack, blade server, tower server',
                'parent_id' => 'CAT007',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],

            // Router & Modem subcategories
            [
                'category_id' => 'CAT031',
                'category_name' => 'ADSL/VDSL Modem',
                'description' => 'Modem ADSL, VDSL, dan DSL',
                'parent_id' => 'CAT011',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'category_id' => 'CAT032',
                'category_name' => 'Wireless Router',
                'description' => 'Router WiFi, mesh router, gaming router',
                'parent_id' => 'CAT011',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'category_id' => 'CAT033',
                'category_name' => 'Enterprise Router',
                'description' => 'Router enterprise, core router, edge router',
                'parent_id' => 'CAT011',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],

            // Kabel UTP subcategories
            [
                'category_id' => 'CAT034',
                'category_name' => 'UTP Cat5e',
                'description' => 'Kabel UTP kategori 5e, indoor/outdoor',
                'parent_id' => 'CAT015',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'category_id' => 'CAT035',
                'category_name' => 'UTP Cat6',
                'description' => 'Kabel UTP kategori 6, shielded/unshielded',
                'parent_id' => 'CAT015',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        ];

        // Insert grandchild categories
        DB::table('categories')->insert($grandchildCategories);

        // ================================================================
        // SPECIFIC MODELS (Level 4) - Model/Brand Spesifik
        // ================================================================

        $specificModels = [
            // ADSL/VDSL Modem Models
            [
                'category_id' => 'CAT036',
                'category_name' => 'ZTE F609',
                'description' => 'Modem ONT ZTE F609 GPON',
                'parent_id' => 'CAT031',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'category_id' => 'CAT037',
                'category_name' => 'ZTE F660',
                'description' => 'Modem ONT ZTE F660 GPON WiFi',
                'parent_id' => 'CAT031',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'category_id' => 'CAT038',
                'category_name' => 'ZTE F960',
                'description' => 'Modem ONT ZTE F960 GPON Dual Band',
                'parent_id' => 'CAT031',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'category_id' => 'CAT039',
                'category_name' => 'Huawei HG8245H',
                'description' => 'Modem ONT Huawei HG8245H GPON',
                'parent_id' => 'CAT031',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'category_id' => 'CAT040',
                'category_name' => 'Fiberhome HG6245D',
                'description' => 'Modem ONT Fiberhome HG6245D GPON',
                'parent_id' => 'CAT031',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],

            // Wireless Router Models
            [
                'category_id' => 'CAT041',
                'category_name' => 'TP-Link Archer C6',
                'description' => 'Router WiFi TP-Link Archer C6 AC1200',
                'parent_id' => 'CAT032',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'category_id' => 'CAT042',
                'category_name' => 'TP-Link Archer AX73',
                'description' => 'Router WiFi TP-Link Archer AX73 AX5400',
                'parent_id' => 'CAT032',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'category_id' => 'CAT043',
                'category_name' => 'Asus RT-AX55',
                'description' => 'Router WiFi Asus RT-AX55 AX1800',
                'parent_id' => 'CAT032',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'category_id' => 'CAT044',
                'category_name' => 'Mikrotik hAP ac²',
                'description' => 'Router WiFi Mikrotik hAP ac² RBD52G-5HacD2HnD',
                'parent_id' => 'CAT032',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],

            // Enterprise Router Models
            [
                'category_id' => 'CAT045',
                'category_name' => 'Mikrotik CCR1009',
                'description' => 'Router Mikrotik Cloud Core CCR1009-7G-1C-1S+',
                'parent_id' => 'CAT033',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'category_id' => 'CAT046',
                'category_name' => 'Cisco ISR4321',
                'description' => 'Router Cisco ISR4321 Integrated Services',
                'parent_id' => 'CAT033',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],

            // Switch Models
            [
                'category_id' => 'CAT047',
                'category_name' => 'TP-Link TL-SG1008D',
                'description' => 'Switch TP-Link TL-SG1008D 8-Port Gigabit',
                'parent_id' => 'CAT012',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'category_id' => 'CAT048',
                'category_name' => 'TP-Link TL-SG1024D',
                'description' => 'Switch TP-Link TL-SG1024D 24-Port Gigabit',
                'parent_id' => 'CAT012',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'category_id' => 'CAT049',
                'category_name' => 'Cisco SG350-28',
                'description' => 'Switch Cisco SG350-28 28-Port Managed',
                'parent_id' => 'CAT012',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],

            // Access Point Models
            [
                'category_id' => 'CAT050',
                'category_name' => 'Ubiquiti UniFi AP AC Pro',
                'description' => 'Access Point Ubiquiti UniFi AP AC Pro',
                'parent_id' => 'CAT013',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'category_id' => 'CAT051',
                'category_name' => 'TP-Link EAP245',
                'description' => 'Access Point TP-Link EAP245 AC1750',
                'parent_id' => 'CAT013',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],

            // Monitor Models
            [
                'category_id' => 'CAT052',
                'category_name' => 'Dell P2419H',
                'description' => 'Monitor Dell P2419H 24" IPS Full HD',
                'parent_id' => 'CAT008',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'category_id' => 'CAT053',
                'category_name' => 'LG 24MK430H',
                'description' => 'Monitor LG 24MK430H 24" IPS Full HD',
                'parent_id' => 'CAT008',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],

            // Laptop Models
            [
                'category_id' => 'CAT054',
                'category_name' => 'Lenovo ThinkPad E14',
                'description' => 'Laptop Lenovo ThinkPad E14 Gen 3',
                'parent_id' => 'CAT029',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'category_id' => 'CAT055',
                'category_name' => 'HP ProBook 440 G8',
                'description' => 'Laptop HP ProBook 440 G8',
                'parent_id' => 'CAT029',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],

            // UTP Cable Specific
            [
                'category_id' => 'CAT056',
                'category_name' => 'Belden Cat6 UTP',
                'description' => 'Kabel UTP Belden Cat6 305m Indoor',
                'parent_id' => 'CAT035',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'category_id' => 'CAT057',
                'category_name' => 'Commscope Cat6 UTP',
                'description' => 'Kabel UTP Commscope Cat6 305m Indoor',
                'parent_id' => 'CAT035',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],

            // Power Supply Models
            [
                'category_id' => 'CAT058',
                'category_name' => 'APC Smart-UPS 1000VA',
                'description' => 'UPS APC Smart-UPS SC1000I 1000VA',
                'parent_id' => 'CAT010',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'category_id' => 'CAT059',
                'category_name' => 'ICA ST1212B',
                'description' => 'UPS ICA ST1212B 1200VA',
                'parent_id' => 'CAT010',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],

            // Printer Models
            [
                'category_id' => 'CAT060',
                'category_name' => 'HP LaserJet P1102',
                'description' => 'Printer HP LaserJet P1102 Mono',
                'parent_id' => 'CAT019',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'category_id' => 'CAT061',
                'category_name' => 'Canon Pixma G2010',
                'description' => 'Printer Canon Pixma G2010 Ink Tank',
                'parent_id' => 'CAT019',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        ];

        // Insert specific model categories
        DB::table('categories')->insert($specificModels);

        $this->command->info('✅ Categories berhasil di-seed!');
        $totalCategories = count($parentCategories) + count($childCategories) + count($grandchildCategories) + count($specificModels);
        $this->command->info('📊 Total categories: ' . $totalCategories);
        $this->command->info('🌳 Struktur: 6 parent → 21 child → 8 grandchild → 26 specific models');
        $this->command->info('🔧 Termasuk model spesifik: ZTE F960, F660, F609, dll');
    }
}

// ================================================================
// Update DatabaseSeeder.php
// ================================================================

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UserLevelSeeder::class,
            UserSeeder::class,
            CategorySeeder::class, // Tambahkan ini
        ]);
    }
}
