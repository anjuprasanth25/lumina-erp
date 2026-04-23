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
        Schema::create('employee_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->date('dob');
            $table->string('gender');
            $table->date('date_of_joining');
            $table->date('date_of_leaving')->nullable();

            $table->foreignId('designation_id')->constrained('designations');
            $table->foreignId('department_id')->constrained('departments');
            $table->foreignId('billing_type_id')->nullable()->constrained('billing_types');
            $table->foreignId('country_id')->constrained('countries');

            $table->string('family_status');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_details');
    }
};
