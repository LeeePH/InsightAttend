<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    protected $fillable = [
        'user_id',
        'user_name',
        'role_slug',
        'action',
        'route_name',
        'method',
        'url',
        'ip_address',
        'user_agent',
        'status_code',
        'description',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Activity text without raw HTTP method prefixes (e.g. "GET ") for display.
     */
    public function getFriendlyDescriptionAttribute(): string
    {
        $text = trim((string) ($this->description ?? ''));
        if ($text === '') {
            return '';
        }

        $text = preg_replace('/\b(GET|POST|PUT|PATCH|DELETE|HEAD|OPTIONS)\b\s+/i', '', $text) ?? $text;
        $text = preg_replace('/\s{2,}/', ' ', $text) ?? $text;

        return trim($text);
    }
}
