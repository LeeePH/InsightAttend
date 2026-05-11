<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddTimeBlocksToEmployeeTimetableEntries extends Migration
{
    public function up(): void
    {
        Schema::table('employee_timetable_entries', function (Blueprint $table) {
            $table->json('time_blocks')->nullable()->after('time_end');
        });

        $rows = DB::table('employee_timetable_entries')
            ->orderBy('employee_id')
            ->orderBy('course_id')
            ->orderBy('day_of_week')
            ->orderBy('room')
            ->orderBy('time_start')
            ->get();

        $groups = [];
        foreach ($rows as $row) {
            $key = implode('|', [
                $row->employee_id,
                $row->course_id ?? 0,
                $row->day_of_week,
                trim((string) $row->room),
                trim((string) $row->department_key),
            ]);

            $groups[$key][] = $row;
        }

        foreach ($groups as $items) {
            usort($items, fn ($a, $b) => strcmp((string) $a->time_start, (string) $b->time_start));
            $primary = $items[0];
            $blocks = array_map(function ($item) {
                return [
                    'time_start' => substr((string) $item->time_start, 0, 5),
                    'time_end' => substr((string) $item->time_end, 0, 5),
                ];
            }, $items);

            DB::table('employee_timetable_entries')
                ->where('id', $primary->id)
                ->update([
                    'time_start' => $blocks[0]['time_start'],
                    'time_end' => $blocks[count($blocks) - 1]['time_end'],
                    'time_blocks' => json_encode($blocks),
                ]);

            $deleteIds = array_slice(array_map(fn ($item) => $item->id, $items), 1);
            if (!empty($deleteIds)) {
                DB::table('employee_timetable_entries')->whereIn('id', $deleteIds)->delete();
            }
        }
    }

    public function down(): void
    {
        Schema::table('employee_timetable_entries', function (Blueprint $table) {
            $table->dropColumn('time_blocks');
        });
    }
}
