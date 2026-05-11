<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->date('date_hired')->nullable()->after('department_id');
            $table->string('employment_type', 30)->nullable()->after('employment_status');
            $table->text('skills')->nullable()->after('employment_type');
            $table->text('achievements')->nullable()->after('skills');
            $table->string('emergency_contact_name')->nullable()->after('achievements');
            $table->string('emergency_contact_relationship')->nullable()->after('emergency_contact_name');
            $table->string('emergency_contact_phone')->nullable()->after('emergency_contact_relationship');
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn([
                'date_hired',
                'employment_type',
                'skills',
                'achievements',
                'emergency_contact_name',
                'emergency_contact_relationship',
                'emergency_contact_phone',
            ]);
        });
    }
};
