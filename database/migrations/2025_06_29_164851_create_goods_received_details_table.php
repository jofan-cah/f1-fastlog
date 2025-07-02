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
       Schema::create('goods_received_details', function (Blueprint $table) {
            $table->string('gr_detail_id', 50)->primary();
            $table->string('gr_id', 50);
            $table->string('item_id', 50);
            $table->integer('quantity_received');
            $table->integer('quantity_to_stock')->default(0); // Ke stok
            $table->integer('quantity_to_ready')->default(0); // Langsung siap pakai
            $table->decimal('unit_price', 12, 2)->default(0);
            $table->string('batch_number', 50)->nullable();
            $table->date('expiry_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('gr_id')->references('gr_id')->on('goods_receiveds')->onDelete('cascade');
            $table->foreign('item_id')->references('item_id')->on('items');
            $table->index(['gr_id', 'item_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('goods_received_details');
    }
};
