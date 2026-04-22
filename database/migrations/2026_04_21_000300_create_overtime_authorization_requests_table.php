<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOvertimeAuthorizationRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('overtime_authorization_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('emp_id');
            $table->date('date_filed');
            $table->string('employee_name');
            $table->string('employee_position')->nullable();
            $table->string('employee_department')->nullable();
            $table->json('entries'); // date/work schedule/overtime/reason rows
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
        Schema::dropIfExists('overtime_authorization_requests');
    }
}

