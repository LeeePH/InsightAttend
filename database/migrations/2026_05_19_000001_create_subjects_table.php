<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSubjectsTable extends Migration
{
    public function up(): void
    {
        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique();
            $table->string('name', 150);
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Add subject_id to timetable entries (alongside existing course_id for backward compat)
        Schema::table('employee_timetable_entries', function (Blueprint $table) {
            $table->unsignedBigInteger('subject_id')->nullable()->after('course_id');
            $table->foreign('subject_id')->references('id')->on('subjects')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('employee_timetable_entries', function (Blueprint $table) {
            $table->dropForeign(['subject_id']);
            $table->dropColumn('subject_id');
        });

        Schema::dropIfExists('subjects');
    }
}
