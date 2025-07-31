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
        Schema::table('purchase_orders', function (Blueprint $table) {
            // Workflow Status - menggantikan status yang ada dengan lebih detail
            $table->string('workflow_status', 50)->default('draft_logistic')->after('status');

            // Approval Users
            $table->string('logistic_user_id', 50)->nullable()->after('created_by');
            $table->string('finance_f1_user_id', 50)->nullable()->after('logistic_user_id');
            $table->string('finance_f2_user_id', 50)->nullable()->after('finance_f1_user_id');

            // Approval Timestamps
            $table->timestamp('logistic_approved_at')->nullable()->after('finance_f2_user_id');
            $table->timestamp('finance_f1_approved_at')->nullable()->after('logistic_approved_at');
            $table->timestamp('finance_f2_approved_at')->nullable()->after('finance_f1_approved_at');

            // Rejection Info
            $table->text('rejection_reason')->nullable()->after('finance_f2_approved_at');
            $table->string('rejected_by_level', 20)->nullable()->after('rejection_reason');
            $table->timestamp('rejected_at')->nullable()->after('rejected_by_level');

            // Payment Information
            $table->string('payment_method', 50)->nullable()->after('rejected_at');

            $table->string('virtual_account_number')->nullable()->after('payment_method');
            $table->decimal('payment_amount', 15, 2)->nullable()->after('virtual_account_number');
            $table->string('payment_status', 30)->default('pending')->after('payment_amount');

            // Payment Options yang bisa dipilih Finance F1
            $table->json('available_payment_options')->nullable()->after('payment_status');

            // Bank/Payment Details
            $table->string('bank_name')->nullable()->after('available_payment_options');
            $table->string('account_number')->nullable()->after('bank_name');
            $table->string('account_holder')->nullable()->after('account_number');

            // Due date untuk payment
            $table->date('payment_due_date')->nullable()->after('account_holder');

            // Finance F1 Notes (untuk payment options, supplier selection)
            $table->text('finance_f1_notes')->nullable()->after('payment_due_date');

            // FINANCE RBP Notes (untuk approval/rejection)
            $table->text('finance_f2_notes')->nullable()->after('finance_f1_notes');

            // Add foreign key constraints
            $table->foreign('logistic_user_id')->references('user_id')->on('users')->onDelete('set null');
            $table->foreign('finance_f1_user_id')->references('user_id')->on('users')->onDelete('set null');
            $table->foreign('finance_f2_user_id')->references('user_id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            // Drop foreign keys first
            $table->dropForeign(['logistic_user_id']);
            $table->dropForeign(['finance_f1_user_id']);
            $table->dropForeign(['finance_f2_user_id']);

            // Drop columns
            $table->dropColumn([
                'workflow_status',
                'logistic_user_id',
                'finance_f1_user_id',
                'finance_f2_user_id',
                'logistic_approved_at',
                'finance_f1_approved_at',
                'finance_f2_approved_at',
                'rejection_reason',
                'rejected_by_level',
                'rejected_at',
                'payment_method',
                'virtual_account_number',
                'payment_amount',
                'payment_status',
                'available_payment_options',
                'bank_name',
                'account_number',
                'account_holder',
                'payment_due_date',
                'finance_f1_notes',
                'finance_f2_notes'
            ]);
        });
    }
};
