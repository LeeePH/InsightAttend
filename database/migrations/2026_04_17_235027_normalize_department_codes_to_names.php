<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class NormalizeDepartmentCodesToNames extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $map = [
            'SIT' => 'Bachelor of Science in Information Technology',
            'SHTM' => 'Bachelor of Science in Hospitality Management',
            'SED' => 'Bachelor of Secondary Education',
        ];

        foreach ($map as $code => $full) {
            // Ensure full department exists
            DB::table('departments')->updateOrInsert(
                ['name' => $full],
                ['description' => null, 'is_active' => 1, 'created_at' => now(), 'updated_at' => now()]
            );

            $fullId = (int) DB::table('departments')->where('name', $full)->value('id');
            $codeId = DB::table('departments')->where('name', $code)->value('id');

            if ($codeId) {
                // Move employees from code department to full department
                DB::table('employees')->where('department_id', $codeId)->update([
                    'department_id' => $fullId,
                    'department' => $full,
                ]);

                // Deactivate the code department
                DB::table('departments')->where('id', $codeId)->update([
                    'is_active' => 0,
                    'updated_at' => now(),
                ]);
            }

            // Also normalize legacy string values if still present
            DB::table('employees')->where('department', $code)->update(['department' => $full, 'department_id' => $fullId]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Intentionally left blank: normalization is not reversible.
    }
}
