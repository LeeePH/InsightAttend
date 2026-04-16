<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Attendance;
use Illuminate\Http\Request;
use RealRashid\SweetAlert\Facades\Alert;

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
        
        // Check if late
        if ($employee->schedules->first()) {
            $scheduleTimeIn = strtotime($employee->schedules->first()->time_in);
            $currentTime = strtotime(date('H:i:s'));
            
            if ($currentTime > $scheduleTimeIn) {
                $attendance->status = 0; // Late
            } else {
                $attendance->status = 1; // On time
            }
        } else {
            $attendance->status = 1;
        }
        
        $attendance->type = 0; // Time in
        $attendance->save();

        return redirect()->route('timein.index')->with('success', 'Time In recorded successfully for ' . $employee->name . '!');
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
