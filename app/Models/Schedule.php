<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    protected $fillable = [
        'slug',
        'schedule_type',
        'time_in',
        'time_out',
        'break_minutes',
        'grace_minutes',
        'break_start',
        'break_end',
    ];

    public function getRouteKeyName()
    {
        return 'slug';
    }

    public function employees()
    {
        return $this->belongsToMany('App\Models\Employee', 'schedule_employees', 'schedule_id', 'emp_id');
    }

    public function shifts()
    {
        return $this->hasMany(ScheduleShift::class);
    }
}
