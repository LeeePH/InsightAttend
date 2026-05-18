<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Legacy seeded subject rows that were incorrectly inserted into the courses table.
     *
     * @var array<int, string>
     */
    private array $legacySubjectCodes = [
        'GE 001', 'GE 002', 'GE 003', 'GE 004', 'GE 005', 'GE 006', 'GE 007', 'GE 008', 'GE 009', 'GE 010', 'GE 011',
        'PE 001', 'PE 002', 'PE 003', 'PE 004',
        'NSTP 1', 'NSTP 2',
        'IT 101', 'IT 102', 'IT 103', 'IT 104', 'IT 105', 'IT 106', 'IT 107', 'IT 108', 'IT 109', 'IT 110', 'IT 111',
        'IT 112', 'IT 113', 'IT 114', 'IT 115', 'IT 116', 'IT 117', 'IT 118', 'IT 119', 'IT 120', 'IT 121', 'IT 122', 'IT 123',
        'EDUC 101', 'EDUC 102', 'EDUC 103', 'EDUC 104', 'EDUC 105', 'EDUC 106', 'EDUC 107', 'EDUC 108', 'EDUC 109', 'EDUC 110',
        'EDUC 111', 'EDUC 112', 'EDUC 113', 'EDUC 114', 'EDUC 115', 'EDUC 116', 'EDUC 117', 'EDUC 118', 'EDUC 119', 'EDUC 120',
        'SHTM 101', 'SHTM 102', 'SHTM 103', 'SHTM 104', 'SHTM 105', 'SHTM 106', 'SHTM 107', 'SHTM 108', 'SHTM 109', 'SHTM 110',
        'SHTM 111', 'SHTM 112', 'SHTM 113', 'SHTM 114', 'SHTM 115', 'SHTM 116', 'SHTM 117', 'SHTM 118', 'SHTM 119', 'SHTM 120',
    ];

    /**
     * Intended degree programs for the courses table.
     *
     * @var array<int, array{code: string, name: string}>
     */
    private array $programs = [
        ['code' => 'BSIT', 'name' => 'Bachelor of Science in Information Technology'],
        ['code' => 'BSIS', 'name' => 'Bachelor of Science in Information Systems'],
        ['code' => 'BSED', 'name' => 'Bachelor of Secondary Education'],
        ['code' => 'BEED', 'name' => 'Bachelor of Elementary Education'],
        ['code' => 'BSHM', 'name' => 'Bachelor of Science in Hospitality Management'],
        ['code' => 'BSTM', 'name' => 'Bachelor of Science in Tourism Management'],
    ];

    public function up(): void
    {
        DB::table('courses')
            ->whereIn('code', $this->legacySubjectCodes)
            ->delete();

        foreach ($this->programs as $program) {
            DB::table('courses')->updateOrInsert(
                ['code' => $program['code']],
                [
                    'name' => $program['name'],
                    'description' => null,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }
    }

    public function down(): void
    {
        DB::table('courses')
            ->whereIn('code', array_column($this->programs, 'code'))
            ->delete();
    }
};
