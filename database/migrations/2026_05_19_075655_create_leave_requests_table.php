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
        Schema::create('leave_requests', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('leave_type_id')->constrained('leave_types')->restrictOnDelete();

            $table->date('start_date');
            $table->date('end_date');
            $table->integer('total_days');
            $table->text('reason')->nullable();

            $table->enum('status', ['pending_manager', 'pending_hr', 'approved', 'rejected'])
                ->default('pending_manager');

            $table->foreignId('destination_country_id')
                ->nullable()
                ->constrained('countries');
            $table->string('alternative_contact_no')->nullable();

            $table->foreignId('manager_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('manager_notes')->nullable();
            $table->timestamp('manager_actioned_at')->nullable();

            $table->foreignId('hr_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('hr_notes')->nullable();
            $table->timestamp('hr_actioned_at')->nullable();


            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_requests');
    }
};
