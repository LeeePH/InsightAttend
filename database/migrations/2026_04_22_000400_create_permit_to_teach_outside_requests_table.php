<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePermitToTeachOutsideRequestsTable extends Migration
{
    public function up()
    {
        Schema::create('permit_to_teach_outside_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('emp_id');
            $table->string('position_rank')->nullable();
            $table->string('department_school')->nullable();
            $table->string('employment_status', 32)->nullable();
            $table->string('other_school_name');
            $table->string('other_school_address');
            $table->string('institution_type', 32);
            $table->string('institution_type_others')->nullable();
            $table->string('subjects_to_teach');
            $table->string('program_level', 32);
            $table->string('units_or_hours_per_week')->nullable();
            $table->text('teaching_schedule')->nullable();
            $table->date('engagement_from')->nullable();
            $table->date('engagement_to')->nullable();
            $table->boolean('certification_confirmed')->default(true);
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
        Schema::dropIfExists('permit_to_teach_outside_requests');
    }
}
