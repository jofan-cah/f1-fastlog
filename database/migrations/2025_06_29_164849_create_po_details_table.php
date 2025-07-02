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
      Schema::create('po_details', function (Blueprint $table) {
            $table->string('po_detail_id', 50)->primary();
            $table->string('po_id', 50);
            $table->string('item_id', 50);
            $table->integer('quantity_ordered');
            $table->decimal('unit_price', 12, 2)->default(0);
            $table->decimal('total_price', 15, 2)->default(0);
            $table->integer('quantity_received')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('po_id')->references('po_id')->on('purchase_orders')->onDelete('cascade');
            $table->foreign('item_id')->references('item_id')->on('items');
            $table->index(['po_id', 'item_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('po_details');
    }
};
