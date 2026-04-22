<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDiscountApplicationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('discount_applications', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('emp_id');
            $table->string('term_semester', 64)->nullable();
            $table->string('school_year', 64)->nullable();
            $table->date('date_request');
            $table->string('employee_department')->nullable();
            $table->string('employee_position')->nullable();
            $table->date('date_hire')->nullable();
            $table->string('employment_status', 32)->nullable(); // probationary|regular
            $table->string('school', 32)->nullable(); // stsn|csta
            $table->string('student_name')->nullable();
            $table->string('student_no')->nullable();
            $table->string('track_program')->nullable();
            $table->string('grade_year_level')->nullable();
            $table->string('student_department', 32)->nullable(); // grade_school|junior_high|senior_high|college
            $table->string('discount_applied', 32)->nullable(); // family_relative|employee_privileges
            $table->string('family_relationship')->nullable();
            $table->string('privilege_child_order', 16)->nullable(); // 1st|2nd|3rd|4th
            $table->decimal('tuition_fee_discount_percent', 5, 2)->nullable();
            $table->json('supporting_documents')->nullable();
            $table->tinyInteger('status')->default(0); // 0 pending, 1 approved, 2 rejected
            $table->unsignedBigInteger('reviewed_by')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->foreign('emp_id')->references('id')->on('employees')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('discount_applications');
    }
}

