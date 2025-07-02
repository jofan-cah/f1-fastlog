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
        Schema::create('transaction_details', function (Blueprint $table) {
            $table->string('transaction_detail_id', 50)->primary();
            $table->string('transaction_id', 50);
            $table->string('item_detail_id', 50);
            $table->string('status_before', 20)->nullable(); // Saran: 'available', 'used', 'damaged', 'maintenance', 'reserved'
            $table->string('status_after', 20)->nullable(); // Saran: 'available', 'used', 'damaged', 'maintenance', 'reserved'
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('transaction_id')->references('transaction_id')->on('transactions')->onDelete('cascade');
            $table->foreign('item_detail_id')->references('item_detail_id')->on('item_details');
            $table->index(['transaction_id', 'item_detail_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaction_details');
    }
};
