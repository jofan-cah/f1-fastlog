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
        Schema::table('categories', function (Blueprint $table) {
            //

             Schema::table('categories', function (Blueprint $table) {
            $table->string('code_category', 3)->nullable()->after('category_id');
        });
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            //
              Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn('code_category');
        });
        });
    }
};
