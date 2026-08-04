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
        Schema::table('company_role_user', function (Blueprint $table) {

            // 2. Add the new 4-column unique constraint including module_id
            $table->unique(
                ['company_id', 'role_id', 'user_id', 'module_id'],
                'company_role_user_comp_role_user_mod_unique' // Custom shorter name to avoid MySQL max length limits
            );
            $table->dropUnique(['company_id', 'role_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('company_role_user', function (Blueprint $table) {
            $table->unique(
                ['company_id', 'role_id', 'user_id'],
                'company_role_user_company_id_role_id_user_id_unique'
            );

            $table->dropUnique('company_role_user_comp_role_user_mod_unique');
        });
    }
};
