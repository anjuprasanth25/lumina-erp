<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->foreignId('temp_line_manager_id')
                ->nullable()
                ->after('line_manager_id')
                ->constrained('users')
                ->nullOnDelete();
        });

        $employees = DB::table('employees')->whereNotNull('line_manager_id')->get();
        foreach ($employees as $emp) {
            $lineManagerUserId = DB::table('users')
                ->where('employee_id', $emp->line_manager_id)
                ->value('id');
            if ($lineManagerUserId) {
                DB::table('employees')
                    ->where('id', $emp->id)
                    ->update(['temp_line_manager_id' => $lineManagerUserId]);
            }
        }

        Schema::table('employees', function (Blueprint $table) {
            $table->dropForeign(['line_manager_id']);
            $table->dropColumn('line_manager_id');
        });

        Schema::table('employees', function (Blueprint $table) {
            $table->foreignId('line_manager_id')
                ->nullable()
                ->after('department_id')
                ->constrained('users')
                ->nullOnDelete();
        });

        DB::statement('UPDATE employees SET line_manager_id = temp_line_manager_id');

        Schema::table('employees', function (Blueprint $table) {
            $table->dropForeign(['temp_line_manager_id']);
            $table->dropColumn('temp_line_manager_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropForeign(['line_manager_id']);
            $table->dropColumn('line_manager_id');
        });
    }
};
