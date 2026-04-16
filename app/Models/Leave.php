<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Leave extends Model
{
    const STATUS_PENDING = 0;
    const STATUS_APPROVED = 1;
    const STATUS_REJECTED = 2;
    
    const TYPE_SICK = 1;
    const TYPE_ANNUAL = 2;
    const TYPE_PERSONAL = 3;
    const TYPE_UNPAID = 4;
    const TYPE_MATERNITY = 5;
    const TYPE_PATERNITY = 6;
    const TYPE_VACATION = 7;
    const TYPE_EMERGENCY = 8;
    const TYPE_OTHER = 9;
    
    protected $fillable = [
        'emp_id', 
        'leave_date', 
        'leave_date_end',
        'leave_days',
        'leave_time', 
        'reason',
        'type',
        'status',
        'approved_by',
        'approved_at',
        'remarks',
        'supporting_documents',
    ];

    protected $casts = [
        'supporting_documents' => 'array',
    ];

    /**
     * Public URLs for stored supporting files (disk: public).
     *
     * @return array<int, string>
     */
    public function supportingDocumentUrls(): array
    {
        $paths = $this->supporting_documents ?? [];
        $urls = [];
        foreach ($paths as $path) {
            if (is_string($path) && $path !== '') {
                $urls[] = Storage::disk('public')->url($path);
            }
        }

        return $urls;
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'emp_id');
    }
    
    public function approver()
    {
        return $this->belongsTo(Employee::class, 'approved_by');
    }
    
    public function getStatusLabelAttribute()
    {
        switch($this->status) {
            case self::STATUS_PENDING:
                return '<span class="badge badge-warning">Pending</span>';
            case self::STATUS_APPROVED:
                return '<span class="badge badge-success">Approved</span>';
            case self::STATUS_REJECTED:
                return '<span class="badge badge-danger">Rejected</span>';
            default:
                return '<span class="badge badge-secondary">Unknown</span>';
        }
    }
    
    public function getTypeLabelAttribute()
    {
        switch($this->type) {
            case self::TYPE_SICK:
                return 'Sick Leave';
            case self::TYPE_ANNUAL:
                return 'Annual Leave';
            case self::TYPE_PERSONAL:
                return 'Personal Leave';
            case self::TYPE_UNPAID:
                return 'Unpaid Leave';
            case self::TYPE_MATERNITY:
                return 'Maternity Leave';
            case self::TYPE_PATERNITY:
                return 'Paternity Leave';
            case self::TYPE_VACATION:
                return 'Vacation Leave';
            case self::TYPE_EMERGENCY:
                return 'Emergency Leave';
            default:
                return 'Other Leave';
        }
    }
}
