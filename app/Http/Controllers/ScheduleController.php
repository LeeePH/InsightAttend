<?php

namespace App\Http\Controllers;

use App\Models\Schedule;
use App\Models\ScheduleShift;
use App\Http\Requests\ScheduleEmp;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ScheduleController extends Controller
{
   
    public function index()
    {
     
        return view('admin.schedule')->with('schedules', Schedule::query()->orderBy('id', 'desc')->get());

    }


    public function store(ScheduleEmp $request)
    {
        $validated = $request->validated();

        $schedule = new Schedule();
        $schedule->slug = $validated['slug'];
        $schedule->schedule_type = $validated['schedule_type'];
        $schedule->break_minutes = $validated['break_minutes'];
        $schedule->grace_minutes = $validated['grace_minutes'] ?? 0;
        $schedule->break_start = $validated['break_start'] ?? null;
        $schedule->break_end = $validated['break_end'] ?? null;

        $schedule->time_in = $validated['schedule_type'] === 'fixed' ? ($validated['time_in'] ?? null) : null;
        $schedule->time_out = $validated['schedule_type'] === 'fixed' ? ($validated['time_out'] ?? null) : null;
        $schedule->save();

        flash()->success('Success','Schedule has been created successfully !');
        return redirect()->route('schedule.index');

    }

    public function update(ScheduleEmp $request, Schedule $schedule)
    {
        if ($request->filled('time_in')) {
            $request['time_in'] = str_split($request->time_in, 5)[0];
        }
        if ($request->filled('time_out')) {
            $request['time_out'] = str_split($request->time_out, 5)[0];
        }

        $validated = $request->validated();

        $schedule->slug = $validated['slug'];
        $schedule->schedule_type = $validated['schedule_type'];
        $schedule->break_minutes = $validated['break_minutes'];
        $schedule->grace_minutes = $validated['grace_minutes'] ?? 0;
        $schedule->break_start = $validated['break_start'] ?? null;
        $schedule->break_end = $validated['break_end'] ?? null;
        $schedule->time_in = $validated['schedule_type'] === 'fixed' ? ($validated['time_in'] ?? null) : null;
        $schedule->time_out = $validated['schedule_type'] === 'fixed' ? ($validated['time_out'] ?? null) : null;
        $schedule->save();
        flash()->success('Success','Schedule has been Updated successfully !');
        return redirect()->route('schedule.index');


    }

    public function shifts(Schedule $schedule)
    {
        if ($schedule->schedule_type !== 'shifting') {
            abort(404);
        }

        $schedule->load([
            'shifts' => function ($q) {
                $q->orderBy('is_off')->orderBy('name');
            },
            'shifts.breaks',
        ]);

        return view('admin.schedule_shifts', [
            'schedule' => $schedule,
        ]);
    }

    public function storeShift(Request $request, Schedule $schedule)
    {
        if ($schedule->schedule_type !== 'shifting') {
            abort(404);
        }

        $validated = $request->validate([
            'shift_code' => ['required', 'string', 'min:1', 'max:16', Rule::unique('schedule_shifts', 'shift_code')->where('schedule_id', $schedule->id)],
            'name' => ['required', 'string', 'min:2', 'max:64'],
            // Allow overnight shifts: time_out may be "before" time_in.
            'time_in' => ['required', 'date_format:H:i'],
            'time_out' => ['required', 'date_format:H:i'],
            'grace_minutes' => ['nullable', 'integer', 'min:0', 'max:120'],
            'is_off' => ['nullable', 'boolean'],
            'break_minutes' => ['nullable', 'integer', 'min:0', 'max:600'], // fallback if no break windows
            'break_start' => ['nullable', 'date_format:H:i'],
            'break_end' => ['nullable', 'date_format:H:i'],
        ]);

        $breakStart = $validated['break_start'] ?? null;
        $breakEnd = $validated['break_end'] ?? null;
        unset($validated['break_start'], $validated['break_end']);

        $validated['shift_code'] = strtoupper(trim($validated['shift_code']));
        $validated['is_off'] = (bool) ($validated['is_off'] ?? false);
        $validated['grace_minutes'] = (int) ($validated['grace_minutes'] ?? 0);

        $shift = $schedule->shifts()->create($validated);

        if ($breakStart && $breakEnd) {
            $shift->breaks()->create([
                'break_start' => $breakStart,
                'break_end' => $breakEnd,
            ]);
        }

        flash()->success('Success', 'Shift has been added successfully!');
        return redirect()->route('schedule.shifts', $schedule);
    }

    public function updateShift(Request $request, Schedule $schedule, ScheduleShift $shift)
    {
        if ($schedule->schedule_type !== 'shifting' || (int) $shift->schedule_id !== (int) $schedule->id) {
            abort(404);
        }

        $validated = $request->validate([
            'shift_code' => ['required', 'string', 'min:1', 'max:16', Rule::unique('schedule_shifts', 'shift_code')->where('schedule_id', $schedule->id)->ignore($shift->id)],
            'name' => ['required', 'string', 'min:2', 'max:64'],
            'time_in' => ['required', 'date_format:H:i'],
            'time_out' => ['required', 'date_format:H:i'],
            'grace_minutes' => ['nullable', 'integer', 'min:0', 'max:120'],
            'is_off' => ['nullable', 'boolean'],
            'break_minutes' => ['nullable', 'integer', 'min:0', 'max:600'],
            'break_start' => ['nullable', 'date_format:H:i'],
            'break_end' => ['nullable', 'date_format:H:i'],
        ]);

        $breakStart = $validated['break_start'] ?? null;
        $breakEnd = $validated['break_end'] ?? null;
        unset($validated['break_start'], $validated['break_end']);

        $validated['shift_code'] = strtoupper(trim($validated['shift_code']));
        $validated['is_off'] = (bool) ($validated['is_off'] ?? false);
        $validated['grace_minutes'] = (int) ($validated['grace_minutes'] ?? 0);

        $shift->update($validated);

        // For now: maintain a single break window (first row)
        $shift->load('breaks');
        if ($breakStart && $breakEnd) {
            $existing = $shift->breaks->first();
            if ($existing) {
                $existing->update(['break_start' => $breakStart, 'break_end' => $breakEnd]);
            } else {
                $shift->breaks()->create(['break_start' => $breakStart, 'break_end' => $breakEnd]);
            }
        } else {
            $shift->breaks()->delete();
        }

        flash()->success('Success', 'Shift has been updated successfully!');
        return redirect()->route('schedule.shifts', $schedule);
    }

    public function destroyShift(Schedule $schedule, ScheduleShift $shift)
    {
        if ($schedule->schedule_type !== 'shifting' || (int) $shift->schedule_id !== (int) $schedule->id) {
            abort(404);
        }

        $shift->delete();

        flash()->success('Success', 'Shift has been deleted successfully!');
        return redirect()->route('schedule.shifts', $schedule);
    }

  
    public function destroy(Schedule $schedule)
    {
        $schedule->delete();
        flash()->success('Success','Schedule has been deleted successfully !');
        return redirect()->route('schedule.index');
    }
}
