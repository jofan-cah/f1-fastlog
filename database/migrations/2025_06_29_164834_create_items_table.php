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
       Schema::create('items', function (Blueprint $table) {
            $table->string('item_id', 50)->primary();
            $table->string('item_code', 50)->unique();
            $table->string('item_name', 200);
            $table->string('category_id', 50);
            $table->string('unit', 20); // pcs, meter, kg, dll
            $table->integer('min_stock')->default(0); // untuk auto PO
            $table->text('description')->nullable();
            $table->string('qr_code')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('category_id')->references('category_id')->on('categories');
            $table->index(['item_code', 'category_id']);
            $table->index(['qr_code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
