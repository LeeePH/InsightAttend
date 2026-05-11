<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    protected $fillable = [
        'code',
        'name',
        'description',
    ];

    public function timetableEntries()
    {
        return $this->hasMany(EmployeeTimetableEntry::class);
    }
}
