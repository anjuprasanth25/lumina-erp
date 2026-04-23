<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('exchange_rates', function (Blueprint $table) {
            $table->id();
            $table->integer('year');
            $table->tinyInteger('month');

            $table->foreignId('from_currency_id')->constrained('currencies');
            $table->foreignId('to_currency_id')->constrained('currencies');

            $table->decimal('rate', 15, 6);
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');

            $table->unique(['year', 'month', 'from_currency_id', 'to_currency_id'], 'unique_rate_per_month');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exchange_rates');
    }
};
