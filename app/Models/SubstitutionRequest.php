<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubstitutionRequest extends Model
{
    const STATUS_PENDING = 0;
    const STATUS_APPROVED = 1;
    const STATUS_REJECTED = 2;

    protected $fillable = [
        'emp_id',
        'absent_teacher_name',
        'substitute_teacher_name',
        'entries',
        'status',
        'reviewed_by',
        'reviewed_at',
        'remarks',
    ];

    protected $casts = [
        'entries' => 'array',
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
