<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // ================================================
        // 1. UPDATE GOODS_RECEIVEDS TABLE
        // ================================================
        Schema::table('goods_receiveds', function (Blueprint $table) {
            // Ubah po_id jadi nullable untuk support non-PO receipts
            $table->string('po_id', 50)->nullable()->change();

            // Tambah kolom untuk direct receipts
            $table->enum('receipt_type', ['po_based', 'direct'])->default('po_based')->after('received_by');
            $table->string('delivery_note_number', 100)->nullable()->after('receipt_type');
            $table->string('invoice_number', 100)->nullable()->after('delivery_note_number');
            $table->string('external_reference', 200)->nullable()->after('invoice_number');

            // Update foreign key constraint - hapus yang lama, buat yang baru dengan nullable
            $table->dropForeign(['po_id']);
            $table->foreign('po_id')->references('po_id')->on('purchase_orders')->nullOnDelete();
        });

        // ================================================
        // 2. UPDATE GOODS_RECEIVED_DETAILS TABLE
        // ================================================
        Schema::table('goods_received_details', function (Blueprint $table) {
            // Tambah kolom po_detail_id untuk link ke PO detail (nullable untuk direct receipts)
            $table->string('po_detail_id', 50)->nullable()->after('item_id');

            // Tambah kolom total_price (calculated field)
            $table->decimal('total_price', 15, 2)->default(0)->after('unit_price');

            // Tambah kolom untuk zero-value reasons dan condition notes
            $table->string('zero_value_reason', 50)->nullable()->after('notes');
            $table->text('condition_notes')->nullable()->after('zero_value_reason');

            // Foreign key untuk po_detail_id
            $table->foreign('po_detail_id')->references('po_detail_id')->on('po_details')->nullOnDelete();

            // Index untuk performa
            $table->index(['po_detail_id']);
        });

        // ================================================
        // 3. UPDATE EXISTING DATA
        // ================================================

        // Update existing goods_receiveds records - set receipt_type = 'po_based' untuk yang ada PO
        DB::table('goods_receiveds')
            ->whereNotNull('po_id')
            ->update(['receipt_type' => 'po_based']);

        // Set total_price = quantity_received * unit_price untuk existing records
        DB::statement('
            UPDATE goods_received_details
            SET total_price = quantity_received * unit_price
            WHERE total_price = 0 AND unit_price > 0
        ');

        // Link existing goods_received_details dengan po_details
        // Matching berdasarkan gr -> po -> po_details dengan item yang sama
        DB::statement('
            UPDATE goods_received_details grd
            INNER JOIN goods_receiveds gr ON grd.gr_id = gr.gr_id
            INNER JOIN po_details pd ON gr.po_id = pd.po_id AND grd.item_id = pd.item_id
            SET grd.po_detail_id = pd.po_detail_id
            WHERE gr.receipt_type = "po_based"
            AND gr.po_id IS NOT NULL
            AND grd.po_detail_id IS NULL
        ');

        // Handle existing records yang mungkin sudah ada dengan po_id = null
        $existingDirectReceipts = DB::table('goods_receiveds')
            ->whereNull('po_id')
            ->count();

        if ($existingDirectReceipts > 0) {
            // Update existing direct receipts
            DB::table('goods_receiveds')
                ->whereNull('po_id')
                ->update(['receipt_type' => 'direct']);

            // Update receive numbers untuk direct receipts jika diperlukan
            $directReceipts = DB::table('goods_receiveds')
                ->whereNull('po_id')
                ->where('receipt_type', 'direct')
                ->orderBy('receive_date')
                ->get();

            foreach ($directReceipts as $index => $receipt) {
                $year = date('Y', strtotime($receipt->receive_date));
                $newReceiveNumber = 'GRD' . $year . str_pad($index + 1, 3, '0', STR_PAD_LEFT);

                // Cek apakah receive_number sudah sesuai format GRD
                if (!str_starts_with($receipt->receive_number, 'GRD')) {
                    DB::table('goods_receiveds')
                        ->where('gr_id', $receipt->gr_id)
                        ->update(['receive_number' => $newReceiveNumber]);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // ================================================
        // ROLLBACK GOODS_RECEIVED_DETAILS TABLE
        // ================================================
        Schema::table('goods_received_details', function (Blueprint $table) {
            // Hapus foreign key dan index
            $table->dropForeign(['po_detail_id']);
            $table->dropIndex(['po_detail_id']);

            // Hapus kolom yang ditambahkan
            $table->dropColumn([
                'po_detail_id',
                'total_price',
                'zero_value_reason',
                'condition_notes'
            ]);
        });

        // ================================================
        // ROLLBACK GOODS_RECEIVEDS TABLE
        // ================================================
        Schema::table('goods_receiveds', function (Blueprint $table) {
            // Hapus foreign key baru
            $table->dropForeign(['po_id']);

            // Hapus kolom yang ditambahkan
            $table->dropColumn([
                'receipt_type',
                'delivery_note_number',
                'invoice_number',
                'external_reference'
            ]);

            // Kembalikan po_id jadi required dan foreign key lama
            $table->string('po_id', 50)->nullable(false)->change();
            $table->foreign('po_id')->references('po_id')->on('purchase_orders');
        });

        // ================================================
        // RESET DATA (Optional - hati-hati dengan data loss)
        // ================================================
        // Uncomment jika ingin reset data, tapi hati-hati bisa hilang data
        /*
        DB::table('goods_receiveds')
            ->update(['receipt_type' => 'po_based']);
        */
    }
};
