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
       Schema::create('purchase_orders', function (Blueprint $table) {
            $table->string('po_id', 50)->primary();
            $table->string('po_number', 50)->unique();
            $table->string('supplier_id', 50);
            $table->date('po_date');
            $table->date('expected_date')->nullable();
            $table->string('status', 20)->default('draft'); // Saran: 'draft', 'sent', 'partial', 'received', 'cancelled'
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->text('notes')->nullable();
            $table->string('created_by', 50);
            $table->timestamps();

            $table->foreign('supplier_id')->references('supplier_id')->on('suppliers');
            $table->foreign('created_by')->references('user_id')->on('users');
            $table->index(['supplier_id', 'po_date']);
            $table->index(['status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_orders');
    }
};
