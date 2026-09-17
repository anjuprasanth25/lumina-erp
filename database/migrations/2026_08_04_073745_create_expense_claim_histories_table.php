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
        Schema::create('expense_claim_histories', function (Blueprint $table) {
            $table->id();

            $table->foreignId('expense_claim_id')->constrained('expense_claims')->onDelete('cascade');
            $table->foreignId('action_by')->constrained('users');

            $table->string('action'); // e.g. 'submitted', 'verified', 'approved', 'rejected', 'booked', 'posted'
            $table->string('from_status')->nullable();
            $table->string('to_status');
            $table->text('comments')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expense_claim_histories');
    }
};