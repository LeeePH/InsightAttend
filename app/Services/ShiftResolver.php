<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\EmployeeShiftRotation;
use App\Models\Schedule;
use App\Models\ScheduleShift;
use Carbon\Carbon;

class ShiftResolver
{
    /**
     * Resolve an employee's expected shift for a given date.
     *
     * Returns an array:
     * - schedule: Schedule|null
     * - shift: ScheduleShift|null (for shifting schedules)
     * - is_off: bool
     * - start: Carbon|null (expected shift start datetime)
     * - end: Carbon|null (expected shift end datetime; may be next day)
     * - grace_minutes: int
     */
    public static function resolve(Employee $employee, Carbon $date): array
    {
        $schedule = $employee->schedules()->first();
        if (!$schedule instanceof Schedule) {
            return [
                'schedule' => null,
                'shift' => null,
                'is_off' => false,
                'start' => null,
                'end' => null,
                'grace_minutes' => 0,
            ];
        }

        $stype = $schedule->schedule_type ?? 'fixed';
        if ($stype === 'fixed') {
            if (!$schedule->time_in || !$schedule->time_out) {
                return [
                    'schedule' => $schedule,
                    'shift' => null,
                    'is_off' => false,
                    'start' => null,
                    'end' => null,
                    'grace_minutes' => (int) ($schedule->grace_minutes ?? 0),
                ];
            }

            $start = $date->copy()->startOfDay()->setTimeFromTimeString($schedule->time_in);
            $end = $date->copy()->startOfDay()->setTimeFromTimeString($schedule->time_out);
            if ($end->lte($start)) {
                $end->addDay(); // overnight fixed schedule
            }

            return [
                'schedule' => $schedule,
                'shift' => null,
                'is_off' => false,
                'start' => $start,
                'end' => $end,
                'grace_minutes' => (int) ($schedule->grace_minutes ?? 0),
            ];
        }

        // Shifting schedule
        $rotation = EmployeeShiftRotation::where('emp_id', $employee->id)
            ->where('schedule_id', $schedule->id)
            ->first();

        $shift = null;
        if ($rotation) {
            $pattern = json_decode((string) $rotation->pattern_json, true);
            if (is_array($pattern) && count($pattern) > 0) {
                $startDate = Carbon::parse($rotation->start_date)->startOfDay();
                $d = $date->copy()->startOfDay();
                $diff = $d->diffInDays($startDate, false);
                if ($diff < 0) {
                    $idx = 0;
                } else {
                    $idx = $diff % count($pattern);
                }
                $code = strtoupper(trim((string) ($pattern[$idx] ?? '')));
                if ($code !== '') {
                    $shift = ScheduleShift::where('schedule_id', $schedule->id)
                        ->where('shift_code', $code)
                        ->with('breaks')
                        ->first();
                }
            }
        }

        if (!$shift) {
            // Fallback: first non-off shift
            $shift = ScheduleShift::where('schedule_id', $schedule->id)
                ->with('breaks')
                ->orderBy('is_off')
                ->orderBy('name')
                ->first();
        }

        if (!$shift) {
            return [
                'schedule' => $schedule,
                'shift' => null,
                'is_off' => false,
                'start' => null,
                'end' => null,
                'grace_minutes' => 0,
            ];
        }

        if ((bool) $shift->is_off) {
            return [
                'schedule' => $schedule,
                'shift' => $shift,
                'is_off' => true,
                'start' => null,
                'end' => null,
                'grace_minutes' => (int) ($shift->grace_minutes ?? 0),
            ];
        }

        $shift->loadMissing('breaks');
        $start = $date->copy()->startOfDay()->setTimeFromTimeString($shift->time_in);
        $end = $date->copy()->startOfDay()->setTimeFromTimeString($shift->time_out);
        if ($end->lte($start)) {
            $end->addDay(); // overnight shift
        }

        return [
            'schedule' => $schedule,
            'shift' => $shift,
            'is_off' => false,
            'start' => $start,
            'end' => $end,
            'grace_minutes' => (int) ($shift->grace_minutes ?? 0),
        ];
    }
}

