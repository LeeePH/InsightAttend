<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSubstitutionRequestsTable extends Migration
{
    public function up()
    {
        Schema::create('substitution_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('emp_id');
            $table->string('absent_teacher_name');
            $table->string('substitute_teacher_name');
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
        Schema::dropIfExists('substitution_requests');
    }
}
