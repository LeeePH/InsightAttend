<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMaintenanceRecordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('maintenance_records', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('employee_id');
            $table->unsignedInteger('processed_by');
            $table->string('form_type'); // departure | status_change
            $table->string('from_status')->nullable();
            $table->string('to_status')->nullable();
            $table->date('effective_date')->nullable();
            $table->text('reason')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
            $table->foreign('processed_by')->references('id')->on('users')->onDelete('cascade');
            $table->index(['employee_id', 'created_at']);
            $table->index(['form_type', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('maintenance_records', function (Blueprint $table) {
            $table->dropForeign(['employee_id']);
            $table->dropForeign(['processed_by']);
        });

        Schema::dropIfExists('maintenance_records');
    }
}
