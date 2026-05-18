<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subject extends Model
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
