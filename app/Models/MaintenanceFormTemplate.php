<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MaintenanceFormTemplate extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'ui_settings',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'ui_settings' => 'array',
    ];

    public function fields()
    {
        return $this->hasMany(MaintenanceFormField::class, 'maintenance_form_template_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
