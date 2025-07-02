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
       Schema::create('activity_logs', function (Blueprint $table) {
            $table->string('log_id', 50)->primary();
            $table->string('user_id', 50);
            $table->string('table_name', 50);
            $table->string('record_id', 50);
            $table->string('action', 20); // Saran: 'create', 'update', 'delete', 'login', 'logout'
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('user_id')->on('users');
            $table->index(['user_id', 'created_at']);
            $table->index(['table_name', 'record_id']);
            $table->index(['action', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
