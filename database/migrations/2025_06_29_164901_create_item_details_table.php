<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
       Schema::create('item_details', function (Blueprint $table) {
            $table->string('item_detail_id', 50)->primary();
            $table->string('gr_detail_id', 50);
            $table->string('item_id', 50);
            $table->string('serial_number', 100)->nullable();
            $table->json('custom_attributes')->nullable(); // {"cable_length": "5m", "color": "black", dll}
            $table->string('qr_code')->nullable();
            $table->string('status', 20)->default('available'); // Saran: 'available', 'used', 'damaged', 'maintenance', 'reserved'
            $table->string('location', 100)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('gr_detail_id')->references('gr_detail_id')->on('goods_received_details');
            $table->foreign('item_id')->references('item_id')->on('items');
            $table->index(['qr_code']);
            $table->index(['serial_number']);
            $table->index(['status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('item_details');
    }
};
