<?php

namespace App\Services;

use App\Models\EmployeeTimetableEntry;
use Carbon\Carbon;

class TimetableConflictService
{
    public function intervalsOverlap(string $startA, string $endA, string $startB, string $endB): bool
    {
        $a1 = $this->toMinutes($startA);
        $a2 = $this->toMinutes($endA);
        $b1 = $this->toMinutes($startB);
        $b2 = $this->toMinutes($endB);

        if ($a2 <= $a1 || $b2 <= $b1) {
            return true;
        }

        return $a1 < $b2 && $b1 < $a2;
    }

    public function toMinutes(string $time): int
    {
        $c = Carbon::parse($time);

        return $c->hour * 60 + $c->minute;
    }

    /**
     * @return array{0: bool, 1: string|null} [hasConflict, message]
     */
    public function validateEntry(
        int $employeeId,
        int $dayOfWeek,
        string $timeStart,
        string $timeEnd,
        string $room,
        ?int $ignoreEntryId = null
    ): array {
        $roomNorm = $this->normalizeRoom($room);

        if ($this->toMinutes($timeEnd) <= $this->toMinutes($timeStart)) {
            return [true, 'End time must be after start time.'];
        }

        $empQuery = EmployeeTimetableEntry::query()
            ->where('employee_id', $employeeId)
            ->where('day_of_week', $dayOfWeek);

        if ($ignoreEntryId) {
            $empQuery->where('id', '!=', $ignoreEntryId);
        }

        foreach ($empQuery->get(['id', 'time_start', 'time_end', 'time_blocks']) as $row) {
            $blocks = method_exists($row, 'resolvedTimeBlocks')
                ? $row->resolvedTimeBlocks()
                : [[
                    'time_start' => substr((string) $row->time_start, 0, 5),
                    'time_end' => substr((string) $row->time_end, 0, 5),
                ]];

            foreach ($blocks as $block) {
                if ($this->intervalsOverlap($timeStart, $timeEnd, $block['time_start'], $block['time_end'])) {
                    return [true, 'This employee already has another slot that overlaps this time on the same day.'];
                }
            }
        }

        $roomQuery = EmployeeTimetableEntry::query()
            ->where('day_of_week', $dayOfWeek)
            ->whereRaw('LOWER(TRIM(room)) = ?', [$roomNorm]);

        if ($ignoreEntryId) {
            $roomQuery->where('id', '!=', $ignoreEntryId);
        }

        foreach ($roomQuery->get(['id', 'time_start', 'time_end', 'room', 'time_blocks']) as $row) {
            $blocks = method_exists($row, 'resolvedTimeBlocks')
                ? $row->resolvedTimeBlocks()
                : collect(json_decode((string) ($row->time_blocks ?? '[]'), true) ?: [])
                    ->map(fn ($block) => [
                        'time_start' => substr((string) ($block['time_start'] ?? $row->time_start), 0, 5),
                        'time_end' => substr((string) ($block['time_end'] ?? $row->time_end), 0, 5),
                    ])->whenEmpty(fn ($collection) => $collection->push([
                        'time_start' => substr((string) $row->time_start, 0, 5),
                        'time_end' => substr((string) $row->time_end, 0, 5),
                    ]))->all();

            foreach ($blocks as $block) {
                if ($this->intervalsOverlap($timeStart, $timeEnd, $block['time_start'], $block['time_end'])) {
                    return [true, 'Room "'.$row->room.'" is already booked for this time slot.'];
                }
            }
        }

        return [false, null];
    }

    public function normalizeRoom(string $room): string
    {
        return strtolower(trim($room));
    }

    /**
     * @param array<int, array{time_start:string,time_end:string}> $blocks
     * @return array{0: bool, 1: string|null}
     */
    public function validateBatchIntervals(array $blocks): array
    {
        foreach ($blocks as $index => $block) {
            if ($this->toMinutes($block['time_end']) <= $this->toMinutes($block['time_start'])) {
                return [true, 'Each time block must end after it starts.'];
            }

            foreach ($blocks as $compareIndex => $compareBlock) {
                if ($index === $compareIndex) {
                    continue;
                }

                if ($this->intervalsOverlap($block['time_start'], $block['time_end'], $compareBlock['time_start'], $compareBlock['time_end'])) {
                    return [true, 'Time blocks in the same submission cannot overlap. Use separate lines with a gap for breaktime.'];
                }
            }
        }

        return [false, null];
    }
}
