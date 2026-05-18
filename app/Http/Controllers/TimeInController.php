<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Attendance;
use Illuminate\Http\Request;
use RealRashid\SweetAlert\Facades\Alert;
use App\Services\ShiftResolver;
use Carbon\Carbon;
use App\Models\User;
use App\Notifications\LateWarningNotification;

class TimeInController extends Controller
{
    /**
     * Show the Time In page
     */
    public function index()
    {
        return view('timein.index');
    }

    /**
     * Get all registered employees for facial recognition
     */
    public function getEmployees()
    {
        // Get employees with face data - include those with face_descriptor even if flag not set
        $employees = Employee::whereNotNull('face_descriptor')
            ->where('face_descriptor', '!=', '')
            ->select('id', 'name', 'position', 'face_descriptor')
            ->get();

        return response()->json([
            'employees' => $employees,
            'debug' => [
                'count' => $employees->count()
            ]
        ]);
    }

    /**
     * Get employee by name
     */
    public function getEmployee($name)
    {
        $employee = Employee::where('name', $name)->first();
        
        if (!$employee) {
            // Try with case-insensitive search
            $employee = Employee::whereRaw('LOWER(name) = ?', [strtolower($name)])->first();
        }

        return response()->json([
            'employee' => $employee,
            'debug' => [
                'requested_name' => $name,
                'found' => $employee ? true : false
            ]
        ]);
    }

    /**
     * Store time in record
     */
    public function store(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id'
        ]);

        $employee = Employee::find($request->employee_id);

        // Check if already timed in today
        $todayAttendance = Attendance::where('emp_id', $employee->id)
            ->where('attendance_date', date('Y-m-d'))
            ->where('type', 0) // Time in type
            ->first();

        if ($todayAttendance) {
            return redirect()->route('timein.index')->with('error', 'You have already timed in today!');
        }

        // Create attendance record
        $attendance = new Attendance();
        $attendance->emp_id = $employee->id;
        $attendance->attendance_time = date('H:i:s');
        $attendance->attendance_date = date('Y-m-d');
        
        // Check if late (fixed or shifting) with grace + overnight support
        $today = Carbon::parse(date('Y-m-d'));
        $resolved = ShiftResolver::resolve($employee, $today);
        if (($resolved['is_off'] ?? false) === true) {
            // Rest day: do not mark late
            $attendance->status = 1;
        } elseif (!empty($resolved['start'])) {
            $start = $resolved['start']->copy();
            $grace = (int) ($resolved['grace_minutes'] ?? 0);
            $deadline = $start->addMinutes(max(0, $grace));
            $now = Carbon::parse($attendance->attendance_date . ' ' . $attendance->attendance_time);
            $isLate = $now->gt($deadline);
            $attendance->status = $isLate ? 0 : 1;

            if ($isLate) {
                $user = User::where('email', $employee->email)->first();
                $timeInPretty = Carbon::parse($attendance->attendance_time)->format('g:i A');
                $msg = 'Grace period: ' . $grace . ' minute(s).';
                if ($user) {
                    $user->notify(new LateWarningNotification($attendance->attendance_date, $timeInPretty, $msg));
                } else {
                    $employee->notify(new LateWarningNotification($attendance->attendance_date, $timeInPretty, $msg));
                }
            }
        } else {
            $attendance->status = 1;
        }
        
        $attendance->type = 0; // Time in
        $attendance->save();

        return redirect()->route('timein.index')->with('success', 'Time In recorded successfully for ' . $employee->name . '!');
    }

    /**
     * Authenticated employee Time In from dashboard
     */
    public function employeeTimeIn(Request $request)
    {
        $employee = $request->user()?->employee;

        if (!$employee) {
            return redirect()->route('employee.dashboard')->with('error', 'No employee profile linked to your account.');
        }

        // Already timed in today?
        $existing = Attendance::where('emp_id', $employee->id)
            ->where('attendance_date', date('Y-m-d'))
            ->where('type', 0)
            ->first();

        if ($existing) {
            return redirect()->route('employee.dashboard')->with('error', 'You have already timed in today.');
        }

        $attendance = new Attendance();
        $attendance->emp_id = $employee->id;
        $attendance->attendance_time = date('H:i:s');
        $attendance->attendance_date = date('Y-m-d');

        $today   = Carbon::parse(date('Y-m-d'));
        $resolved = ShiftResolver::resolve($employee, $today);

        if (($resolved['is_off'] ?? false) === true) {
            $attendance->status = 1;
        } elseif (!empty($resolved['start'])) {
            $start    = $resolved['start']->copy();
            $grace    = (int) ($resolved['grace_minutes'] ?? 0);
            $deadline = $start->addMinutes(max(0, $grace));
            $now      = Carbon::parse($attendance->attendance_date . ' ' . $attendance->attendance_time);
            $isLate   = $now->gt($deadline);
            $attendance->status = $isLate ? 0 : 1;

            if ($isLate) {
                $user = User::where('email', $employee->email)->first();
                $timeInPretty = Carbon::parse($attendance->attendance_time)->format('g:i A');
                $msg = 'Grace period: ' . $grace . ' minute(s).';
                if ($user) {
                    $user->notify(new LateWarningNotification($attendance->attendance_date, $timeInPretty, $msg));
                } else {
                    $employee->notify(new LateWarningNotification($attendance->attendance_date, $timeInPretty, $msg));
                }
            }
        } else {
            $attendance->status = 1;
        }

        $attendance->type = 0;
        $attendance->save();

        return redirect()->route('employee.dashboard')->with('success', 'Time In recorded at ' . Carbon::parse($attendance->attendance_time)->format('g:i A') . '.');
    }

    /**
     * Authenticated employee Time Out from dashboard
     */
    public function employeeTimeOut(Request $request)
    {
        $employee = $request->user()?->employee;

        if (!$employee) {
            return redirect()->route('employee.dashboard')->with('error', 'No employee profile linked to your account.');
        }

        // Must have timed in first
        $timeInRecord = Attendance::where('emp_id', $employee->id)
            ->where('attendance_date', date('Y-m-d'))
            ->where('type', 0)
            ->first();

        if (!$timeInRecord) {
            return redirect()->route('employee.dashboard')->with('error', 'You must time in first before timing out.');
        }

        // Already timed out?
        $existing = Attendance::where('emp_id', $employee->id)
            ->where('attendance_date', date('Y-m-d'))
            ->where('type', 1)
            ->first();

        if ($existing) {
            return redirect()->route('employee.dashboard')->with('error', 'You have already timed out today.');
        }

        $attendance = new Attendance();
        $attendance->emp_id = $employee->id;
        $attendance->attendance_time = date('H:i:s');
        $attendance->attendance_date = date('Y-m-d');
        $attendance->status = 1;
        $attendance->type = 1;
        $attendance->save();

        return redirect()->route('employee.dashboard')->with('success', 'Time Out recorded at ' . Carbon::parse($attendance->attendance_time)->format('g:i A') . '.');
    }

    /**
     * Show the Time Out page
     */
    public function timeoutIndex()
    {
        return view('timein.timeout');
    }

    /**
     * Store time out record
     */
    public function storeTimeout(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id'
        ]);

        $employee = Employee::find($request->employee_id);

        // Check if already timed in today
        $todayAttendance = Attendance::where('emp_id', $employee->id)
            ->where('attendance_date', date('Y-m-d'))
            ->where('type', 0) // Time in type
            ->first();

        if (!$todayAttendance) {
            return redirect()->route('timeout.index')->with('error', 'You must time in first before timing out!');
        }

        // Check if already timed out today
        $alreadyTimedOut = Attendance::where('emp_id', $employee->id)
            ->where('attendance_date', date('Y-m-d'))
            ->where('type', 1) // Time out type
            ->first();

        if ($alreadyTimedOut) {
            return redirect()->route('timeout.index')->with('error', 'You have already timed out today!');
        }

        // Create attendance record for time out
        $attendance = new Attendance();
        $attendance->emp_id = $employee->id;
        $attendance->attendance_time = date('H:i:s');
        $attendance->attendance_date = date('Y-m-d');
        $attendance->status = 1; // Completed
        $attendance->type = 1; // Time out
        $attendance->save();

        return redirect()->route('timeout.index')->with('success', 'Time Out recorded successfully for ' . $employee->name . '!');
    }
}
