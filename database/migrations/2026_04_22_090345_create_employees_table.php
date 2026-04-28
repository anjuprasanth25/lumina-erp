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
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('last_name');
            $table->string('email')->unique();
            $table->boolean('is_active')->default(true);

            $table->foreignId('company_id')->constrained('companies');
            $table->date('date_of_joining');
            $table->date('date_of_leaving')->nullable();

            $table->foreignId('designation_id')->constrained('designations');
            $table->foreignId('department_id')->constrained('departments');
            $table->foreignId('billing_type_id')->nullable()->constrained('billing_types');
            $table->foreignId('country_id')->constrained('countries');
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
