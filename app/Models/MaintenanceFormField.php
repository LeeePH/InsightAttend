<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MaintenanceFormField extends Model
{
    protected $fillable = [
        'maintenance_form_template_id',
        'label',
        'field_key',
        'field_type',
        'field_options',
        'placeholder',
        'help_text',
        'column_class',
        'validation_rules',
        'is_required',
        'sort_order',
    ];

    protected $casts = [
        'is_required' => 'boolean',
    ];

    public function template()
    {
        return $this->belongsTo(MaintenanceFormTemplate::class, 'maintenance_form_template_id');
    }
}
