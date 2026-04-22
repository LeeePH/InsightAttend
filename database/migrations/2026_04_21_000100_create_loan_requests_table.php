<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLoanRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('loan_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('emp_id');
            $table->date('date_filed');
            $table->string('civil_status', 16);
            $table->string('contact_number', 32)->nullable();
            $table->date('hire_date')->nullable();
            $table->decimal('amount_requested', 10, 2);
            $table->decimal('amount_approved', 10, 2)->nullable();

            $table->json('purpose_flags')->nullable(); // purpose keys selected (checkbox group)

            // Conditional details based on purpose
            $table->string('hospital_patient_name')->nullable();
            $table->string('hospital_relationship')->nullable();
            $table->unsignedSmallInteger('hospital_age')->nullable();

            $table->text('calamity_details')->nullable();
            $table->string('bereavement_relationship')->nullable();

            $table->string('tuition_child_name')->nullable();
            $table->unsignedSmallInteger('tuition_child_age')->nullable();
            $table->string('tuition_child_level')->nullable();

            $table->string('dental_patient_name')->nullable();
            $table->string('dental_relationship')->nullable();
            $table->unsignedSmallInteger('dental_age')->nullable();

            $table->string('other_purpose')->nullable();

            $table->boolean('employee_statement')->default(false);
            $table->json('supporting_documents')->nullable();

            $table->tinyInteger('status')->default(0); // 0: pending, 1: approved, 2: rejected
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
        Schema::dropIfExists('loan_requests');
    }
}

