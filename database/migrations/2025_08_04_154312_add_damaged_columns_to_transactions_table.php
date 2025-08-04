
<?php

// ================================================================
// STEP 1: Migration untuk table transactions
// Command: php artisan make:migration add_damaged_columns_to_transactions_table
// ================================================================

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
        Schema::table('transactions', function (Blueprint $table) {
            // Kolom untuk damage level
            $table->enum('damage_level', ['light', 'medium', 'heavy', 'total'])
                  ->nullable()
                  ->after('notes')
                  ->comment('Level kerusakan: light=ringan, medium=sedang, heavy=berat, total=rusak total');

            // Kolom untuk damage reason
            $table->enum('damage_reason', [
                'accident', 'wear', 'misuse', 'environment',
                'manufacturing', 'electrical', 'mechanical',
                'water_damage', 'theft_vandalism', 'other'
            ])->nullable()
              ->after('damage_level')
              ->comment('Alasan kerusakan');

            // Kolom untuk repair estimate (opsional untuk damage heavy)
            $table->decimal('repair_estimate', 15, 2)
                  ->nullable()
                  ->after('damage_reason')
                  ->comment('Estimasi biaya repair (khusus untuk kerusakan berat)');

            // Index untuk performa query
            $table->index(['damage_level'], 'idx_transactions_damage_level');
            $table->index(['damage_reason'], 'idx_transactions_damage_reason');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            // Drop indexes first
            $table->dropIndex('idx_transactions_damage_level');
            $table->dropIndex('idx_transactions_damage_reason');

            // Drop columns
            $table->dropColumn(['damage_level', 'damage_reason', 'repair_estimate']);
        });
    }
};
