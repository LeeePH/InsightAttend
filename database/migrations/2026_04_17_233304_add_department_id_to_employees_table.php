<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddDepartmentIdToEmployeesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 1) Add FK column (nullable first for smooth migration)
        Schema::table('employees', function (Blueprint $table) {
            $table->unsignedBigInteger('department_id')->nullable()->after('department');
            $table->index(['department_id']);
        });

        // 2) Backfill: create departments from existing string values
        $rows = DB::table('employees')
            ->select('department')
            ->whereNotNull('department')
            ->where('department', '!=', '')
            ->distinct()
            ->get();

        foreach ($rows as $r) {
            $name = trim((string) $r->department);
            if ($name === '') continue;
            DB::table('departments')->updateOrInsert(
                ['name' => $name],
                ['description' => null, 'is_active' => 1, 'created_at' => now(), 'updated_at' => now()]
            );
        }

        // 3) Set department_id based on department name
        $departments = DB::table('departments')->select('id', 'name')->get();
        foreach ($departments as $d) {
            DB::table('employees')
                ->where('department', $d->name)
                ->update(['department_id' => $d->id]);
        }

        // 4) Add FK constraint
        Schema::table('employees', function (Blueprint $table) {
            $table->foreign('department_id')->references('id')->on('departments')->nullOnDelete();
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
            $table->dropForeign(['department_id']);
            $table->dropIndex(['department_id']);
            $table->dropColumn('department_id');
        });
    }
}
