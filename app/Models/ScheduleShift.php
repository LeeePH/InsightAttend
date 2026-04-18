<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScheduleShift extends Model
{
    use HasFactory;

    protected $fillable = [
        'schedule_id',
        'shift_code',
        'name',
        'time_in',
        'time_out',
        'break_minutes',
        'grace_minutes',
        'is_off',
    ];

    public function schedule()
    {
        return $this->belongsTo(Schedule::class);
    }

    public function breaks()
    {
        return $this->hasMany(ScheduleShiftBreak::class, 'schedule_shift_id');
    }
}
