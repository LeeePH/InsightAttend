<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMaintenanceFieldsToEmployeesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->string('employment_status')->default('active')->after('department');
            $table->date('departure_date')->nullable()->after('employment_status');
            $table->text('departure_reason')->nullable()->after('departure_date');
            $table->unsignedInteger('status_updated_by')->nullable()->after('departure_reason');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn(['employment_status', 'departure_date', 'departure_reason', 'status_updated_by']);
        });
    }
}
