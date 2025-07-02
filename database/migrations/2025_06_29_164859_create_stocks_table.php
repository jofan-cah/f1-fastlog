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
        Schema::create('stocks', function (Blueprint $table) {
            $table->string('stock_id', 50)->primary();
            $table->string('item_id', 50);
            $table->integer('quantity_available')->default(0); // Stok tersedia
            $table->integer('quantity_used')->default(0); // Stok terpakai
            $table->integer('total_quantity')->default(0); // Total stok
            $table->timestamp('last_updated')->useCurrent()->useCurrentOnUpdate();
            $table->timestamps();

            $table->foreign('item_id')->references('item_id')->on('items');
            $table->unique('item_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stocks');
    }
};
