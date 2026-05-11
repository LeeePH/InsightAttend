<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class EmployeeTimetableAndScheduleRoles extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('managed_schedule_department', 16)->nullable()->after('pin_code');
        });

        Schema::table('employees', function (Blueprint $table) {
            $table->string('schedule_department_key', 16)->nullable()->after('department');
        });

        Schema::create('employee_timetable_entries', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('employee_id');
            $table->unsignedTinyInteger('day_of_week');
            $table->time('time_start');
            $table->time('time_end');
            $table->string('room', 64);
            $table->string('department_key', 16);
            $table->string('slot_label', 128)->nullable();
            $table->timestamps();

            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
            $table->index(['day_of_week', 'room']);
            $table->index(['employee_id', 'day_of_week']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_timetable_entries');

        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn('schedule_department_key');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('managed_schedule_department');
        });
    }
}
