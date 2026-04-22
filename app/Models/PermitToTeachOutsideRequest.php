<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PermitToTeachOutsideRequest extends Model
{
    const STATUS_PENDING = 0;
    const STATUS_APPROVED = 1;
    const STATUS_REJECTED = 2;

    protected $fillable = [
        'emp_id',
        'position_rank',
        'department_school',
        'employment_status',
        'other_school_name',
        'other_school_address',
        'institution_type',
        'institution_type_others',
        'subjects_to_teach',
        'program_level',
        'units_or_hours_per_week',
        'teaching_schedule',
        'engagement_from',
        'engagement_to',
        'certification_confirmed',
        'status',
        'reviewed_by',
        'reviewed_at',
        'remarks',
    ];

    protected $casts = [
        'certification_confirmed' => 'boolean',
        'engagement_from' => 'date',
        'engagement_to' => 'date',
        'reviewed_at' => 'datetime',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'emp_id');
    }

    public function reviewer()
    {
        return $this->belongsTo(Employee::class, 'reviewed_by');
    }
}
