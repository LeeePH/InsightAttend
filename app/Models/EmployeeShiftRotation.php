<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeShiftRotation extends Model
{
    use HasFactory;

    protected $fillable = [
        'emp_id',
        'schedule_id',
        'start_date',
        'pattern_json',
    ];

    protected $casts = [
        'start_date' => 'date',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'emp_id');
    }

    public function schedule()
    {
        return $this->belongsTo(Schedule::class);
    }
}
