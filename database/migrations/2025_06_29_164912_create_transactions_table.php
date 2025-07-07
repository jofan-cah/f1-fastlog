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
        Schema::create('transactions', function (Blueprint $table) {
            $table->string('transaction_id', 50)->primary();
            $table->string('transaction_number', 50)->unique();
            $table->string('transaction_type', 20); // Saran: 'in', 'out', 'transfer', 'adjustment', 'return'
            $table->string('reference_id')->nullable(); // ID dari sistem lain (misal: ticket system)
            $table->string('reference_type', 50)->nullable(); // 'ticket', 'project', 'maintenance', dll
            $table->string('item_id', 50);
            $table->integer('quantity');
            $table->string('from_location', 100)->nullable();
            $table->string('to_location', 100)->nullable();
            $table->text('notes')->nullable();
            $table->string('status', 20)->default('pending'); // Saran: 'pending', 'approved', 'completed', 'cancelled', 'rejected'
            $table->string('created_by', 50);
            $table->string('approved_by', 50)->nullable();
            $table->timestamp('transaction_date')->useCurrent();
            $table->timestamp('approved_date')->nullable();
            $table->timestamps();

            $table->foreign('item_id')->references('item_id')->on('items');
            $table->foreign('created_by')->references('user_id')->on('users');
            $table->foreign('approved_by')->references('user_id')->on('users');
            $table->index(['transaction_type', 'transaction_date']);
            $table->index(['reference_type', 'reference_id']);
            $table->index(['status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
