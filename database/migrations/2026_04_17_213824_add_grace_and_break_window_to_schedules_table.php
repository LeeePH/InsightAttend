<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddGraceAndBreakWindowToSchedulesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('schedules', function (Blueprint $table) {
            $table->unsignedSmallInteger('grace_minutes')->default(0)->after('break_minutes');
            $table->time('break_start')->nullable()->after('grace_minutes');
            $table->time('break_end')->nullable()->after('break_start');
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
            $table->dropColumn(['grace_minutes', 'break_start', 'break_end']);
        });
    }
}
