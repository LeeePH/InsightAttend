<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ClassSectionsAndTimetableSectionLink extends Migration
{
    public function up(): void
    {
        Schema::create('class_sections', function (Blueprint $table) {
            $table->id();
            $table->string('department_key', 16);
            $table->unsignedTinyInteger('year_level')->nullable();
            $table->string('section_label', 64);
            $table->timestamps();

            $table->unique(['department_key', 'section_label']);
            $table->index('department_key');
        });

        Schema::table('employee_timetable_entries', function (Blueprint $table) {
            $table->foreignId('class_section_id')
                ->nullable()
                ->after('course_id')
                ->constrained('class_sections')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('employee_timetable_entries', function (Blueprint $table) {
            $table->dropForeign(['class_section_id']);
            $table->dropColumn('class_section_id');
        });

        Schema::dropIfExists('class_sections');
    }
}
