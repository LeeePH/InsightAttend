<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class LoanRequest extends Model
{
    const STATUS_PENDING = 0;
    const STATUS_APPROVED = 1;
    const STATUS_REJECTED = 2;

    protected $fillable = [
        'emp_id',
        'date_filed',
        'civil_status',
        'contact_number',
        'hire_date',
        'amount_requested',
        'amount_approved',
        'purpose_flags',
        'hospital_patient_name',
        'hospital_relationship',
        'hospital_age',
        'calamity_details',
        'bereavement_relationship',
        'tuition_child_name',
        'tuition_child_age',
        'tuition_child_level',
        'dental_patient_name',
        'dental_relationship',
        'dental_age',
        'other_purpose',
        'employee_statement',
        'status',
        'reviewed_by',
        'reviewed_at',
        'remarks',
        'supporting_documents',
    ];

    protected $casts = [
        'purpose_flags' => 'array',
        'supporting_documents' => 'array',
        'employee_statement' => 'boolean',
        'date_filed' => 'date',
        'hire_date' => 'date',
        'reviewed_at' => 'datetime',
        'amount_requested' => 'decimal:2',
        'amount_approved' => 'decimal:2',
    ];

    /**
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

    public function reviewer()
    {
        return $this->belongsTo(Employee::class, 'reviewed_by');
    }
}

