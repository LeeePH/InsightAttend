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

        // $employee->department can be either a plain string column value
        // or a Department model instance (when the relation is loaded).
        // Handle both cases safely.
        $deptRaw = $employee->getRawOriginal('department') ?? $employee->getAttributes()['department'] ?? null;
        $fromDept = self::inferKeyFromText(is_string($deptRaw) ? $deptRaw : null);
        if ($fromDept) {
            return $fromDept;
        }

        // Try the related Department model's name
        $relation = $employee->relationLoaded('department') ? $employee->getRelation('department') : null;
        if ($relation instanceof \App\Models\Department && $relation->name) {
            $fromRelation = self::inferKeyFromText($relation->name);
            if ($fromRelation) {
                return $fromRelation;
            }
        }

        return self::inferKeyFromText(is_string($employee->position) ? $employee->position : null);
    }
}
