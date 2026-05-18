<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Employee;
use Carbon\Carbon;

class AttendanceStatusService
{
    /**
     * Compute daily attendance status against expected shift.
     *
     * Returns:
     * - status_label: Off|Absent|Present|Late
     * - undertime_minutes: int|null
     * - worked_seconds: int|null
     * - expected_start: Carbon|null
     * - expected_end: Carbon|null
     * - actual_in: Carbon|null
     * - actual_out: Carbon|null
     */
    public static function computeForDate(Employee $employee, Carbon $date): array
    {
        $resolved = ShiftResolver::resolve($employee, $date->copy()->startOfDay());

        if (($resolved['is_off'] ?? false) === true) {
            return [
                'status_label' => 'Off',
                'undertime_minutes' => null,
                'worked_seconds' => null,
                'expected_start' => $resolved['start'] ?? null,
                'expected_end' => $resolved['end'] ?? null,
                'actual_in' => null,
                'actual_out' => null,
            ];
        }

        $expectedStart = $resolved['start'] ?? null;
        $expectedEnd = $resolved['end'] ?? null;
        $grace = (int) ($resolved['grace_minutes'] ?? 0);

        $day = $date->toDateString();
        $timeInRow = Attendance::query()
            ->where('emp_id', $employee->id)
            ->where('attendance_date', $day)
            ->where('type', 0)
            ->orderBy('attendance_time')
            ->first();

        // Time out might be on next day for overnight shifts
        $outDate = $expectedEnd ? $expectedEnd->toDateString() : $day;
        $timeOutRow = Attendance::query()
            ->where('emp_id', $employee->id)
            ->where('attendance_date', $outDate)
            ->where('type', 1)
            ->orderBy('attendance_time', 'desc')
            ->first();

        $actualIn = $timeInRow?->attendance_time ? Carbon::parse($day . ' ' . $timeInRow->attendance_time) : null;
        $actualOut = $timeOutRow?->attendance_time ? Carbon::parse($outDate . ' ' . $timeOutRow->attendance_time) : null;

        $statusLabel = 'Absent';
        if ($actualIn) {
            if ($expectedStart) {
                $deadline = $expectedStart->copy()->addMinutes(max(0, $grace));
                $statusLabel = $actualIn->gt($deadline) ? 'Late' : 'Present';
            } else {
                $statusLabel = ((int) ($timeInRow->status ?? 1) === 0) ? 'Late' : 'Present';
            }
        } elseif ($expectedStart) {
            // Only mark Absent once the grace window has fully elapsed.
            // Before that point the employee still has time to clock in.
            $deadline = $expectedStart->copy()->addMinutes(max(0, $grace));
            $now = Carbon::now();
            if ($now->lt($deadline)) {
                // Shift hasn't started yet (or still within grace) — not absent yet
                $statusLabel = 'Pending';
            }
            // else: past the deadline with no time-in → Absent (default)
        }

        $workedSeconds = null;
        if ($actualIn && $actualOut && $actualOut->greaterThan($actualIn)) {
            $workedSeconds = $actualOut->diffInSeconds($actualIn);

            // Subtract break windows if defined (first break row for shifting; schedule window for fixed)
            $shift = $resolved['shift'] ?? null;
            if ($shift && $shift->relationLoaded('breaks')) {
                $b = $shift->breaks->first();
                if ($b) {
                    $bStart = $date->copy()->startOfDay()->setTimeFromTimeString($b->break_start);
                    $bEnd = $date->copy()->startOfDay()->setTimeFromTimeString($b->break_end);
                    if ($bEnd->lte($bStart)) {
                        $bEnd->addDay();
                    }

                    $overlapStart = $bStart->greaterThan($actualIn) ? $bStart : $actualIn;
                    $overlapEnd = $bEnd->lessThan($actualOut) ? $bEnd : $actualOut;
                    if ($overlapEnd->greaterThan($overlapStart)) {
                        $workedSeconds -= $overlapEnd->diffInSeconds($overlapStart);
                    }
                }
            }
            $schedule = $resolved['schedule'] ?? null;
            if (!$shift && $schedule && $schedule->break_start && $schedule->break_end) {
                $bStart = $date->copy()->startOfDay()->setTimeFromTimeString($schedule->break_start);
                $bEnd = $date->copy()->startOfDay()->setTimeFromTimeString($schedule->break_end);
                if ($bEnd->lte($bStart)) {
                    $bEnd->addDay();
                }
                $overlapStart = $bStart->greaterThan($actualIn) ? $bStart : $actualIn;
                $overlapEnd = $bEnd->lessThan($actualOut) ? $bEnd : $actualOut;
                if ($overlapEnd->greaterThan($overlapStart)) {
                    $workedSeconds -= $overlapEnd->diffInSeconds($overlapStart);
                }
            }
        }

        $undertimeMinutes = null;
        if ($expectedEnd && $actualOut && $actualOut->lessThan($expectedEnd)) {
            $undertimeMinutes = $actualOut->diffInMinutes($expectedEnd);
        }

        return [
            'status_label' => $statusLabel,
            'undertime_minutes' => $undertimeMinutes,
            'worked_seconds' => $workedSeconds,
            'expected_start' => $expectedStart,
            'expected_end' => $expectedEnd,
            'actual_in' => $actualIn,
            'actual_out' => $actualOut,
        ];
    }
}

