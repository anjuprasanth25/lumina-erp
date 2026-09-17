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
        Schema::table('expense_claims', function (Blueprint $table) {
            $table->timestamp('verified_at')->nullable()->after('verifier_id');
            $table->timestamp('line_manager_approved_at')->nullable()->after('line_manager_id');
            $table->timestamp('bu_approved_at')->nullable()->after('bu_approver_id');
            $table->timestamp('finance_approved_at')->nullable()->after('finance_approver_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('expense_claims', function (Blueprint $table) {
            $table->dropColumn([
                'verified_at',
                'line_manager_approved_at',
                'bu_approved_at',
                'finance_approved_at',
            ]);
        });
    }
};
