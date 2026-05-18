<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeTimetableEntry extends Model
{
    protected $fillable = [
        'employee_id',
        'course_id',
        'subject_id',
        'class_section_id',
        'day_of_week',
        'time_start',
        'time_end',
        'time_blocks',
        'room',
        'department_key',
        'slot_label',
    ];

    protected $casts = [
        'time_blocks' => 'array',
    ];

    public function resolvedTimeBlocks(): array
    {
        $blocks = collect($this->time_blocks ?: [])
            ->filter(fn ($block) => !empty($block['time_start']) && !empty($block['time_end']))
            ->map(fn ($block) => [
                'time_start' => substr((string) $block['time_start'], 0, 5),
                'time_end' => substr((string) $block['time_end'], 0, 5),
            ])
            ->values()
            ->all();

        if (!empty($blocks)) {
            return $blocks;
        }

        return [[
            'time_start' => substr((string) $this->time_start, 0, 5),
            'time_end' => substr((string) $this->time_end, 0, 5),
        ]];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'course_id');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    public function classSection(): BelongsTo
    {
        return $this->belongsTo(ClassSection::class, 'class_section_id');
    }
}
