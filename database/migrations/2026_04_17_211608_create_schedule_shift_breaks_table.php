<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateScheduleShiftBreaksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('schedule_shift_breaks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('schedule_shift_id');
            $table->time('break_start');
            $table->time('break_end');
            $table->timestamps();

            $table->foreign('schedule_shift_id')->references('id')->on('schedule_shifts')->onDelete('cascade');
            $table->index(['schedule_shift_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('schedule_shift_breaks');
    }
}
