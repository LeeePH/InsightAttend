<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\Attendance;
use App\Models\Leave;
use Carbon\Carbon;

class CheckController extends Controller
{
    public function index()
    {
        $today = today();
        $start = $today->copy()->startOfMonth()->toDateString();
        $end = $today->copy()->endOfMonth()->toDateString();

        $deptLabelMap = [
            // Backward-compat for older stored codes
            'SIT' => 'Bachelor of Science in Information Technology',
            'SHTM' => 'Bachelor of Science in Hospitality Management',
            'SED' => 'Bachelor of Secondary Education',
        ];

        $deptToLabel = function ($raw) use ($deptLabelMap) {
            $raw = trim((string) ($raw ?? ''));
            if ($raw === '') return '';
            return $deptLabelMap[$raw] ?? $raw;
        };

        $employees = Employee::query()
            ->orderBy('department')
            ->orderBy('name')
            ->get();

        $attRows = Attendance::query()
            ->select('emp_id', 'attendance_date')
            ->whereBetween('attendance_date', [$start, $end])
            ->groupBy('emp_id', 'attendance_date')
            ->get();

        $attendanceByEmp = [];
        foreach ($attRows as $row) {
            $attendanceByEmp[(int) $row->emp_id][$row->attendance_date] = true;
        }

        $leaveRows = Leave::query()
            ->select('emp_id', 'leave_date', 'leave_date_end', 'status')
            ->where('status', Leave::STATUS_APPROVED)
            ->where(function ($q) use ($start, $end) {
                $q->whereBetween('leave_date', [$start, $end])
                    ->orWhereBetween('leave_date_end', [$start, $end])
                    ->orWhere(function ($q2) use ($start, $end) {
                        $q2->where('leave_date', '<=', $start)
                            ->where('leave_date_end', '>=', $end);
                    });
            })
            ->get();

        $leaveByEmp = [];
        foreach ($leaveRows as $leave) {
            $from = Carbon::parse($leave->leave_date);
            $to = $leave->leave_date_end ? Carbon::parse($leave->leave_date_end) : Carbon::parse($leave->leave_date);
            if ($to->lessThan($from)) {
                [$from, $to] = [$to, $from];
            }

            for ($d = $from->copy(); $d->lte($to); $d->addDay()) {
                $ymd = $d->toDateString();
                if ($ymd < $start || $ymd > $end) continue;
                $leaveByEmp[(int) $leave->emp_id][$ymd] = true;
            }
        }

        // Group employees by department (fallback to 'Unassigned')
        $employeesByDept = $employees->groupBy(function ($emp) use ($deptToLabel) {
            $dept = $deptToLabel($emp->department ?? '');
            return $dept !== '' ? $dept : 'Unassigned';
        });

        return view('admin.check')->with([
            'employeesByDept' => $employeesByDept,
            'attendanceByEmp' => $attendanceByEmp,
            'leaveByEmp' => $leaveByEmp,
        ]);
    }

    public function CheckStore(Request $request)
    {
        if (isset($request->attd)) {
            foreach ($request->attd as $keys => $values) {
                foreach ($values as $key => $value) {
                    if ($employee = Employee::whereId(request('emp_id'))->first()) {
                        if (
                            !Attendance::whereAttendance_date($keys)
                                ->whereEmp_id($key)
                                ->whereType(0)
                                ->first()
                        ) {
                            $data = new Attendance();
                            
                            $data->emp_id = $key;
                            $emp_req = Employee::whereId($data->emp_id)->first();
                            $data->attendance_time = date('H:i:s', strtotime($emp_req->schedules->first()->time_in));
                            $data->attendance_date = $keys;
                            
                            // $emps = date('H:i:s', strtotime($employee->schedules->first()->time_in));
                            // if (!($emps >= $data->attendance_time)) {
                            //     $data->status = 0;
                           
                            // }
                            $data->save();
                        }
                    }
                }
            }
        }
        if (isset($request->leave)) {
            foreach ($request->leave as $keys => $values) {
                foreach ($values as $key => $value) {
                    if ($employee = Employee::whereId(request('emp_id'))->first()) {
                        if (
                            !Leave::whereLeave_date($keys)
                                ->whereEmp_id($key)
                                ->whereType(1)
                                ->first()
                        ) {
                            $data = new Leave();
                            $data->emp_id = $key;
                            $emp_req = Employee::whereId($data->emp_id)->first();
                            $data->leave_time = $emp_req->schedules->first()->time_out;
                            $data->leave_date = $keys;
                            // if ($employee->schedules->first()->time_out <= $data->leave_time) {
                            //     $data->status = 1;
                                
                            // }
                            // 
                            $data->save();
                        }
                    }
                }
            }
        }
        flash()->success('Success', 'You have successfully submited the attendance !');
        return back();
    }
    public function sheetReport()
    {

    return view('admin.sheet-report')->with(['employees' => Employee::all()]);
    }
}
