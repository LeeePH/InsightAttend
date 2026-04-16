<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Feedback extends Model
{
    protected $table = 'feedback';

    protected $fillable = [
        'emp_id',
        'subject',
        'message',
        'status',
    ];
    
    const STATUS_PENDING = 0;
    const STATUS_READ = 1;
    const STATUS_RESOLVED = 2;
    
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'emp_id');
    }
}
