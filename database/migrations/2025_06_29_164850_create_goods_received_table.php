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
        Schema::create('goods_receiveds', function (Blueprint $table) {
            $table->string('gr_id', 50)->primary();
            $table->string('receive_number', 50)->unique();
            $table->string('po_id', 50);
            $table->string('supplier_id', 50);
            $table->date('receive_date');
            $table->string('status', 20)->default('partial'); // Saran: 'partial', 'complete'
            $table->text('notes')->nullable();
            $table->string('received_by', 50);
            $table->timestamps();

            $table->foreign('po_id')->references('po_id')->on('purchase_orders');
            $table->foreign('supplier_id')->references('supplier_id')->on('suppliers');
            $table->foreign('received_by')->references('user_id')->on('users');
            $table->index(['po_id', 'receive_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('goods_receiveds');
    }
};
