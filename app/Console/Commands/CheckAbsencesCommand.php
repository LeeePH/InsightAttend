<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Leave;
use App\Models\User;
use App\Notifications\AbsenceAlertNotification;
use App\Services\ShiftResolver;
use Carbon\Carbon;

class CheckAbsencesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'attendance:check-absences {--date= : Date (Y-m-d) to check}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send absence alerts for employees without time-in by cutoff';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $dateOpt = $this->option('date');
        $date = $dateOpt ? Carbon::parse($dateOpt) : today();
        $ymd = $date->toDateString();

        $fallbackCutoff = (string) config('attendance.absence_fallback_cutoff_time', '10:00');
        $cutoffMinutes = (int) config('attendance.absence_cutoff_minutes_after_start', 120);

        $employees = Employee::query()->get();
        $sent = 0;
        $skipped = 0;

        foreach ($employees as $employee) {
            // Skip: date is before the employee was hired/created
            $hireDate = $employee->date_hired
                ? Carbon::parse($employee->date_hired)->startOfDay()
                : Carbon::parse($employee->created_at)->startOfDay();
            if ($date->lt($hireDate)) {
                $skipped++;
                continue;
            }

            $resolved = ShiftResolver::resolve($employee, $date->copy());

            // Skip: no schedule or off day
            if (!$resolved['schedule'] || (($resolved['is_off'] ?? false) === true)) {
                $skipped++;
                continue;
            }

            // Skip: approved leave covering this date
            $onLeave = Leave::query()
                ->where('emp_id', $employee->id)
                ->where('status', Leave::STATUS_APPROVED)
                ->where('leave_date', '<=', $ymd)
                ->where(function ($q) use ($ymd) {
                    $q->whereNull('leave_date_end')->orWhere('leave_date_end', '>=', $ymd);
                })
                ->exists();
            if ($onLeave) {
                $skipped++;
                continue;
            }

            // Skip: already has time-in record on date
            $hasTimeIn = Attendance::query()
                ->where('emp_id', $employee->id)
                ->where('attendance_date', $ymd)
                ->where('type', 0)
                ->exists();
            if ($hasTimeIn) {
                $skipped++;
                continue;
            }

            // Determine cutoff datetime
            $cutoffAt = null;
            if (!empty($resolved['start'])) {
                $grace = (int) ($resolved['grace_minutes'] ?? 0);
                $cutoffAt = $resolved['start']->copy()->addMinutes(max(0, $grace + $cutoffMinutes));
            } else {
                $cutoffAt = $date->copy()->startOfDay()->setTimeFromTimeString($fallbackCutoff);
            }

            if (now()->lessThan($cutoffAt)) {
                $skipped++;
                continue;
            }

            // Avoid duplicates: check existing database notifications for same date/type
            $user = User::where('email', $employee->email)->first();
            if ($user) {
                $already = $user->notifications()
                    ->where('type', AbsenceAlertNotification::class)
                    ->where('data->date', $ymd)
                    ->exists();
                if ($already) {
                    $skipped++;
                    continue;
                }
            }

            $expectedStart = !empty($resolved['start']) ? $resolved['start']->format('H:i') : null;
            $message = 'Please file a correction or contact your administrator if you were present.';

            if ($user) {
                $user->notify(new AbsenceAlertNotification($ymd, $expectedStart, $message));
            } else {
                // Fallback: notify employee model (email only; no history for non-user accounts)
                $employee->notify(new AbsenceAlertNotification($ymd, $expectedStart, $message));
            }
            $sent++;
        }

        $this->info("Absence check done for {$ymd}. Sent={$sent}, Skipped={$skipped}");
        return 0;
    }
}
