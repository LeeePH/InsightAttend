<?php

namespace App\Http\Controllers;

use DateTime;
use App\Models\Employee;
use App\Models\Latetime;
use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\AttendanceEmp;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{   
    //show attendance 
    public function index(Request $request)
    {  
        $selectedDate = $request->query('date');
        try {
            $selected = $selectedDate ? Carbon::parse($selectedDate) : today();
        } catch (\Throwable $e) {
            $selected = today();
        }
        $selectedDate = $selected->toDateString();

        // Backward-compat for older stored codes
        $deptLabelMap = [
            'SIT' => 'Bachelor of Science in Information Technology',
            'SHTM' => 'Bachelor of Science in Hospitality Management',
            'SED' => 'Bachelor of Secondary Education',
        ];

        $deptToLabel = function ($raw) use ($deptLabelMap) {
            $raw = trim((string) ($raw ?? ''));
            if ($raw === '') return 'Unassigned';
            return $deptLabelMap[$raw] ?? $raw;
        };

        // Get attendances grouped by employee for selected date only
        $attendances = Attendance::select('emp_id', 'attendance_date')
            ->selectRaw('GROUP_CONCAT(CASE WHEN type = 0 THEN attendance_time END) as time_in')
            ->selectRaw('GROUP_CONCAT(CASE WHEN type = 1 THEN attendance_time END) as time_out')
            ->where('attendance_date', $selectedDate)
            ->groupBy('emp_id', 'attendance_date')
            ->orderBy('emp_id')
            ->get();
        
        // Load employee relationship for each record
        foreach ($attendances as $attendance) {
            $attendance->employee = Employee::find($attendance->emp_id);
            $attendance->dept_label = $deptToLabel($attendance->employee?->department);
        }

        $attendancesByDept = $attendances->groupBy(function ($a) {
            return $a->dept_label ?? 'Unassigned';
        })->sortKeys();
        
        return view('admin.attendance')->with([
            'selectedDate' => $selected,
            'attendancesByDept' => $attendancesByDept,
            'attendancesCount' => $attendances->count(),
        ]);
    }

    //show late times
    public function indexLatetime()
    {
        return view('admin.latetime')->with(['latetimes' => Latetime::all()]);
    }

    

    // public static function lateTime(Employee $employee)
    // {
    //     $current_t = new DateTime(date('H:i:s'));
    //     $start_t = new DateTime($employee->schedules->first()->time_in);
    //     $difference = $start_t->diff($current_t)->format('%H:%I:%S');

    //     $latetime = new Latetime();
    //     $latetime->emp_id = $employee->id;
    //     $latetime->duration = $difference;
    //     $latetime->latetime_date = date('Y-m-d');
    //     $latetime->save();
    // }

    public static function lateTimeDevice($att_dateTime, Employee $employee)
    {
        $schedule = $employee->schedules->first();
        // For shifting schedules, late duration is not tracked in Latetime table yet.
        if (!$schedule || $schedule->schedule_type !== 'fixed' || !$schedule->time_in) {
            return;
        }

        $attendance_time = new DateTime($att_dateTime);
        $checkin = new DateTime($schedule->time_in);
        $difference = $checkin->diff($attendance_time)->format('%H:%I:%S');

        $latetime = new Latetime();
        $latetime->emp_id = $employee->id;
        $latetime->duration = $difference;
        $latetime->latetime_date = date('Y-m-d', strtotime($att_dateTime));
        $latetime->save();
    }
  
}
