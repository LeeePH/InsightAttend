<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScheduleShiftBreak extends Model
{
    use HasFactory;

    protected $fillable = [
        'schedule_shift_id',
        'break_start',
        'break_end',
    ];

    public function shift()
    {
        return $this->belongsTo(ScheduleShift::class, 'schedule_shift_id');
    }
}
