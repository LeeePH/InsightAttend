<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUndertimeAuthorizationRequestsTable extends Migration
{
    public function up()
    {
        Schema::create('undertime_authorization_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('emp_id');
            $table->date('date_filed');
            $table->string('employee_name');
            $table->string('employee_position')->nullable();
            $table->string('employee_department')->nullable();
            $table->json('entries');
            $table->tinyInteger('status')->default(0);
            $table->unsignedBigInteger('reviewed_by')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->foreign('emp_id')->references('id')->on('employees')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('undertime_authorization_requests');
    }
}
