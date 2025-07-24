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
        //

        Schema::table('item_details', function (Blueprint $table) {
            $table->string('kondisi')->nullable()->after('location'); // optional: sesuaikan letak kolom
        });
         Schema::table('transaction_details', function (Blueprint $table) {
            $table->string('kondisi_after')->nullable()->after('notes'); // optional: sesuaikan letak kolom
            $table->string('kondisi_before')->nullable()->after('notes'); // optional: sesuaikan letak kolom
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
        Schema::table('item_details', function (Blueprint $table) {
            $table->dropColumn('kondisi');
        });
        Schema::table('transaction_details', function (Blueprint $table) {
            $table->dropColumn('kondisi_after');
            $table->dropColumn('kondisi_before');
        });
    }
};
