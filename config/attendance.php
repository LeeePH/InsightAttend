<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Attendance notifications
    |--------------------------------------------------------------------------
    |
    | Configure cutoff and scheduling for automated absence alerts.
    |
    */

    // Daily time (24h HH:MM) when absence checks run.
    'absence_check_time' => env('ATTENDANCE_ABSENCE_CHECK_TIME', '10:30'),

    // If an employee has an expected shift start, mark absent if no time-in
    // after this many minutes from shift start (+ grace).
    'absence_cutoff_minutes_after_start' => (int) env('ATTENDANCE_ABSENCE_CUTOFF_MINUTES', 120),

    // Fallback cutoff time when no expected start is resolvable (HH:MM 24h).
    'absence_fallback_cutoff_time' => env('ATTENDANCE_ABSENCE_FALLBACK_CUTOFF', '10:00'),

    // Default channels: database + mail. SMS can be added later.
    // Default to database-only so the system works without SMTP configured.
    // To enable email, set ATTENDANCE_NOTIFICATION_CHANNELS=database,mail and configure MAIL_* env vars.
    // Supported values: database, mail, sms (sms is currently disabled and will be ignored)
    'notification_channels' => array_values(array_filter(
        array_map('trim', explode(',', env('ATTENDANCE_NOTIFICATION_CHANNELS', 'database'))),
        fn ($ch) => $ch !== '' && $ch !== 'sms'
    )),
];

