<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DiscountApplication extends Model
{
    const STATUS_PENDING = 0;
    const STATUS_APPROVED = 1;
    const STATUS_REJECTED = 2;

    protected $fillable = [
        'emp_id',
        'term_semester',
        'school_year',
        'date_request',
        'employee_department',
        'employee_position',
        'date_hire',
        'employment_status',
        'school',
        'student_name',
        'student_no',
        'track_program',
        'grade_year_level',
        'student_department',
        'discount_applied',
        'family_relationship',
        'privilege_child_order',
        'tuition_fee_discount_percent',
        'status',
        'reviewed_by',
        'reviewed_at',
        'remarks',
        'supporting_documents',
    ];

    protected $casts = [
        'supporting_documents' => 'array',
        'date_request' => 'date',
        'date_hire' => 'date',
        'reviewed_at' => 'datetime',
        'tuition_fee_discount_percent' => 'decimal:2',
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

