<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTypeAndBreakToSchedulesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('schedules', function (Blueprint $table) {
            $table->string('schedule_type', 16)->default('fixed')->after('slug');
            $table->unsignedSmallInteger('break_minutes')->default(0)->after('time_out');

            // Shifting schedules may not have a single fixed time-in/out.
            $table->time('time_in')->nullable()->change();
            $table->time('time_out')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('schedules', function (Blueprint $table) {
            $table->time('time_in')->nullable(false)->change();
            $table->time('time_out')->nullable(false)->change();

            $table->dropColumn(['schedule_type', 'break_minutes']);
        });
    }
}
