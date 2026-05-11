<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddCourseAndTimetableSettings extends Migration
{
    public function up(): void
    {
        Schema::table('employee_timetable_entries', function (Blueprint $table) {
            $table->unsignedBigInteger('course_id')->nullable()->after('employee_id');
            $table->foreign('course_id')->references('id')->on('courses')->nullOnDelete();
            $table->index(['course_id', 'day_of_week']);
        });

        Schema::create('timetable_settings', function (Blueprint $table) {
            $table->string('key', 100)->primary();
            $table->text('value')->nullable();
        });

        DB::table('timetable_settings')->insert([
            ['key' => 'school_name', 'value' => 'Colegio de Sta. Teresa De Avila'],
            ['key' => 'school_address', 'value' => 'Add school address here'],
            ['key' => 'semester_label', 'value' => '1st Semester'],
            ['key' => 'school_year', 'value' => '2026-2027'],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('timetable_settings');

        Schema::table('employee_timetable_entries', function (Blueprint $table) {
            $table->dropForeign(['course_id']);
            $table->dropIndex(['course_id', 'day_of_week']);
            $table->dropColumn('course_id');
        });
    }
}
