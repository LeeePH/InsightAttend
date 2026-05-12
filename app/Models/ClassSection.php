<?php

namespace App\Models;

use App\Services\SchedulingDepartmentService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClassSection extends Model
{
    protected $fillable = [
        'department_key',
        'year_level',
        'section_label',
    ];

    protected $casts = [
        'year_level' => 'integer',
    ];

    public function timetableEntries(): HasMany
    {
        return $this->hasMany(EmployeeTimetableEntry::class, 'class_section_id');
    }

    public function getDisplayTitleAttribute(): string
    {
        $y = $this->year_level ? ' · Year '.$this->year_level : '';

        return $this->section_label.' ('.$this->department_key.$y.')';
    }

    public function scopeForDepartment($query, string $departmentKey)
    {
        $key = strtoupper($departmentKey);

        return $query->where('department_key', $key);
    }

    public static function validDepartmentKey(string $key): bool
    {
        return SchedulingDepartmentService::isValidKey($key);
    }
}
