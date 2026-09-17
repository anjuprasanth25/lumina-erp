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
        Schema::create('expense_claim_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('expense_claim_id')->constrained('expense_claims')->onDelete('cascade');
            $table->foreignId('expense_category_id')->constrained('expense_categories');

            $table->date('bill_date');
            $table->string('invoice_number')->nullable();
            $table->string('supplier_client_name')->nullable();
            $table->string('attendee_employee_names')->nullable();
            $table->text('description')->nullable();

            $table->string('charged_to_type')->nullable();
            $table->string('charged_to_id')->nullable();

            $table->foreignId('item_currency_id')->constrained('currencies');
            $table->decimal('exchange_rate', 10, 6)->default(1.000000);
            $table->decimal('amount', 12, 2);
            $table->decimal('vat_amount', 12, 2)->default(0.00);
            $table->decimal('amount_local_currency', 12, 2);

            // Attachment Files
            $table->string('attachment_path')->nullable();
            $table->string('attachment_ref')->nullable();


            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expense_claim_items');
    }
};