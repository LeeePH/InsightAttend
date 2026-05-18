<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            // Add employee_number column
            $table->string('employee_number', 20)->nullable()->after('name');

            // Rename skills → educational_background
            $table->renameColumn('skills', 'educational_background');

            // Rename achievements → work_experience
            $table->renameColumn('achievements', 'work_experience');
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn('employee_number');
            $table->renameColumn('educational_background', 'skills');
            $table->renameColumn('work_experience', 'achievements');
        });
    }
};
