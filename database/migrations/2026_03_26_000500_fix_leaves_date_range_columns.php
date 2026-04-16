<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class FixLeavesDateRangeColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('leaves')) {
            return;
        }

        Schema::table('leaves', function (Blueprint $table) {
            if (!Schema::hasColumn('leaves', 'leave_date_end')) {
                $table->date('leave_date_end')->nullable()->after('leave_date');
            }

            if (!Schema::hasColumn('leaves', 'leave_days')) {
                $table->unsignedInteger('leave_days')->default(1)->after('leave_date_end');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (!Schema::hasTable('leaves')) {
            return;
        }

        Schema::table('leaves', function (Blueprint $table) {
            if (Schema::hasColumn('leaves', 'leave_days')) {
                $table->dropColumn('leave_days');
            }
            if (Schema::hasColumn('leaves', 'leave_date_end')) {
                $table->dropColumn('leave_date_end');
            }
        });
    }
}
