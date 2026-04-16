<?php

namespace App\Http\Controllers;

use DateTime;
use App\Models\Employee;
use App\Models\Latetime;
use App\Models\Attendance;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\AttendanceEmp;

class AttendanceController extends Controller
{   
    //show attendance 
    public function index()
    {  
        // Get all attendances grouped by employee and date
        $attendances = Attendance::select('emp_id', 'attendance_date')
            ->selectRaw('GROUP_CONCAT(CASE WHEN type = 0 THEN attendance_time END) as time_in')
            ->selectRaw('GROUP_CONCAT(CASE WHEN type = 1 THEN attendance_time END) as time_out')
            ->groupBy('emp_id', 'attendance_date')
            ->orderBy('attendance_date', 'desc')
            ->orderBy('emp_id')
            ->get();
        
        // Load employee relationship for each record
        foreach ($attendances as $attendance) {
            $attendance->employee = Employee::find($attendance->emp_id);
        }
        
        return view('admin.attendance')->with(['attendances' => $attendances]);
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
        $attendance_time = new DateTime($att_dateTime);
        $checkin = new DateTime($employee->schedules->first()->time_in);
        $difference = $checkin->diff($attendance_time)->format('%H:%I:%S');

        $latetime = new Latetime();
        $latetime->emp_id = $employee->id;
        $latetime->duration = $difference;
        $latetime->latetime_date = date('Y-m-d', strtotime($att_dateTime));
        $latetime->save();
    }
  
}
