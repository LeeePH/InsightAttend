<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRotationAndFieldsToScheduleShiftsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('schedule_shifts', function (Blueprint $table) {
            $table->string('shift_code', 16)->nullable()->after('schedule_id');
            $table->unsignedSmallInteger('grace_minutes')->default(0)->after('break_minutes');
            $table->boolean('is_off')->default(false)->after('grace_minutes');

            $table->index(['schedule_id', 'shift_code']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('schedule_shifts', function (Blueprint $table) {
            $table->dropIndex(['schedule_id', 'shift_code']);
            $table->dropColumn(['shift_code', 'grace_minutes', 'is_off']);
        });
    }
}
