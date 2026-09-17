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
        Schema::create('expense_claims', function (Blueprint $table) {
            $table->id();
            $table->string('claim_number')->unique();

            $table->foreignId('company_id')->constrained('companies');
            $table->foreignId('employee_id')->constrained('employees');
            $table->foreignId('department_id')->constrained('departments');
            $table->foreignId('currency_id')->constrained('currencies');

            $table->foreignId('verifier_id')->nullable()->constrained('users');
            $table->foreignId('line_manager_id')->nullable()->constrained('users');
            $table->foreignId('bu_approver_id')->nullable()->constrained('users');
            $table->foreignId('finance_approver_id')->nullable()->constrained('users');

            $table->date('claim_date');
            $table->foreignId('expense_type_id')->constrained('expense_types');
            $table->boolean('is_billed_to_client')->default(false);
            $table->string('client_name')->nullable();
            $table->boolean('has_policy_exception')->default(false);
            $table->text('exception_reason')->nullable();

            $table->string('status')->default('draft');

            $table->decimal('total_amount_claim_currency', 12, 2)->default(0.00);
            $table->decimal('total_vat_claim_currency', 12, 2)->default(0.00);
            $table->decimal('total_amount_local_currency', 12, 2)->default(0.00);

            // Finance Actions
            $table->string('booking_reference_code')->nullable();
            $table->timestamp('booked_at')->nullable();
            $table->foreignId('booked_by')->nullable()->constrained('users');

            $table->timestamp('posted_at')->nullable();
            $table->foreignId('posted_by')->nullable()->constrained('users');

            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expense_claims');
    }
};
