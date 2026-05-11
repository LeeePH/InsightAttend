<?php

namespace App\Services;

use App\Models\Employee;

class SchedulingDepartmentService
{
    public const KEYS = ['IT', 'EDUC', 'SHTM'];

    public static function labels(): array
    {
        return [
            'IT' => 'Information Technology (IT)',
            'EDUC' => 'Education (EDUC)',
            'SHTM' => 'Hospitality & Tourism (SHTM)',
        ];
    }

    public static function schoolTitles(): array
    {
        return [
            'IT' => 'School of Information Technology',
            'EDUC' => 'School of Education',
            'SHTM' => 'School of Hospitality and Tourism Management',
        ];
    }

    public static function isValidKey(?string $key): bool
    {
        return $key && in_array(strtoupper($key), self::KEYS, true);
    }

    /**
     * Best-effort mapping from free-text department name (or position) to IT / EDUC / SHTM.
     */
    public static function inferKeyFromText(?string $text): ?string
    {
        if ($text === null || trim($text) === '') {
            return null;
        }
        $u = strtoupper($text);

        $patterns = [
            'IT' => ['INFORMATION TECHNOLOGY', ' BSIT', ' BSIS', ' IT ', ' IT-', 'SCHOOL OF IT', ' SIT '],
            'EDUC' => ['EDUC', 'BSED', 'BEED', ' ELEMENTARY', ' SECONDARY', 'TEACHER EDUCATION'],
            'SHTM' => ['HOSPITALITY', 'TOURISM', ' SHTM', 'BSTM', 'BSHM', 'CULINARY', ' HOTEL'],
        ];

        foreach ($patterns as $key => $needles) {
            foreach ($needles as $n) {
                if (str_contains($u, $n)) {
                    return $key;
                }
            }
        }

        return null;
    }

    public static function resolveEmployeeDepartmentKey(Employee $employee): ?string
    {
        if ($employee->schedule_department_key && self::isValidKey($employee->schedule_department_key)) {
            return strtoupper($employee->schedule_department_key);
        }

        $fromDept = self::inferKeyFromText($employee->department);
        if ($fromDept) {
            return $fromDept;
        }

        if ($employee->relationLoaded('department') && $employee->department) {
            return self::inferKeyFromText($employee->department->name);
        }

        return self::inferKeyFromText($employee->position);
    }
}
