<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Supplier;

class SupplierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $suppliers = [
            [
                'supplier_id' => 'SUP001',
                'supplier_code' => 'SUP001',
                'supplier_name' => 'PT. Teknologi Maju Indonesia',
                'contact_person' => 'Budi Santoso',
                'phone' => '081234567890',
                'email' => 'budi.santoso@teknologimaju.co.id',
                'address' => 'Jl. Sudirman No. 123, Jakarta Pusat, DKI Jakarta 10110',
                'is_active' => true,
            ],
            [
                'supplier_id' => 'SUP002',
                'supplier_code' => 'SUP002',
                'supplier_name' => 'CV. Sejahtera Abadi',
                'contact_person' => 'Siti Nurhaliza',
                'phone' => '087654321098',
                'email' => 'siti.nurhaliza@sejahteraabadi.com',
                'address' => 'Jl. Diponegoro No. 45, Bandung, Jawa Barat 40112',
                'is_active' => true,
            ],
            [
                'supplier_id' => 'SUP003',
                'supplier_code' => 'SUP003',
                'supplier_name' => 'Toko Elektronik Jaya',
                'contact_person' => 'Ahmad Rahman',
                'phone' => '085123456789',
                'email' => 'ahmad.rahman@elektronikjaya.net',
                'address' => 'Jl. Malioboro No. 78, Yogyakarta, DIY 55213',
                'is_active' => false,
            ],
        ];

        foreach ($suppliers as $supplier) {
            Supplier::create($supplier);
        }
    }
}
