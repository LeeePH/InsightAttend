<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Employee;
use App\Models\Latetime;
use App\Models\Attendance;
use Carbon\Carbon;


class AdminController extends Controller
{

 
    public function index()
    {
        //Dashboard statistics 
        $totalEmp =  count(Employee::all());
        $AllAttendance = count(Attendance::whereAttendance_date(date("Y-m-d"))->get());
        $ontimeEmp = count(Attendance::whereAttendance_date(date("Y-m-d"))->whereStatus('1')->get());
        $latetimeEmp = count(Attendance::whereAttendance_date(date("Y-m-d"))->whereStatus('0')->get());
            
        if($AllAttendance > 0){
                $percentageOntime = str_split(($ontimeEmp/ $AllAttendance)*100, 4)[0];
            }else {
                $percentageOntime = 0 ;
            }
        
        // Monthly reports - this month
        $thisMonthStart = Carbon::now()->startOfMonth();
        $thisMonthEnd = Carbon::now()->endOfMonth();
        $thisMonthAttendance = count(Attendance::whereBetween('attendance_date', [$thisMonthStart, $thisMonthEnd])->get());
        
        // Monthly reports - last month
        $lastMonthStart = Carbon::now()->subMonth()->startOfMonth();
        $lastMonthEnd = Carbon::now()->subMonth()->endOfMonth();
        $lastMonthAttendance = count(Attendance::whereBetween('attendance_date', [$lastMonthStart, $lastMonthEnd])->get());
        
        // Get daily attendance for current month (for chart)
        $dailyAttendance = [];
        $daysInMonth = Carbon::now()->daysInMonth;
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;
        
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = Carbon::create($currentYear, $currentMonth, $day)->toDateString();
            $dailyAttendance[] = count(Attendance::where('attendance_date', $date)->get());
        }
        
        $data = [
            $totalEmp, 
            $ontimeEmp, 
            $latetimeEmp, 
            $percentageOntime,
            $thisMonthAttendance,
            $lastMonthAttendance,
            $dailyAttendance
        ];
        
        return view('admin.index')->with(['data' => $data]);
    }

}
