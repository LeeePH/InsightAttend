<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Department;
use App\Models\Employee;
use App\Services\AttendanceStatusService;
use Carbon\Carbon;

class DepartmentReportController extends Controller
{
    public function index(Request $request)
    {
        $range = $request->query('range', 'daily'); // daily|weekly|monthly|custom
        $departmentId = $request->query('department_id');

        $today = today();
        $start = $today->copy();
        $end = $today->copy();

        if ($range === 'weekly') {
            $start = $today->copy()->startOfWeek();
            $end = $today->copy()->endOfWeek();
        } elseif ($range === 'monthly') {
            $start = $today->copy()->startOfMonth();
            $end = $today->copy()->endOfMonth();
        } elseif ($range === 'custom') {
            $start = $request->query('start') ? Carbon::parse($request->query('start')) : $today->copy();
            $end = $request->query('end') ? Carbon::parse($request->query('end')) : $today->copy();
        } else {
            // daily
            $start = $request->query('date') ? Carbon::parse($request->query('date')) : $today->copy();
            $end = $start->copy();
        }

        if ($end->lessThan($start)) {
            [$start, $end] = [$end, $start];
        }

        $departments = Department::query()->orderBy('name')->get();
        $selectedDepartment = $departmentId ? Department::find($departmentId) : null;

        $employees = Employee::query()
            ->when($departmentId, fn ($q) => $q->where('department_id', $departmentId))
            ->with('department')
            ->get();

        $summary = [
            'present' => 0,
            'late' => 0,
            'absent' => 0,
            'off' => 0,
        ];

        $rows = [];
        for ($d = $start->copy(); $d->lte($end); $d->addDay()) {
            $date = $d->copy();
            $present = 0; $late = 0; $absent = 0; $off = 0;
            foreach ($employees as $emp) {
                $status = AttendanceStatusService::computeForDate($emp, $date);
                $label = $status['status_label'] ?? 'Absent';
                if ($label === 'Off') $off++;
                elseif ($label === 'Late') $late++;
                elseif ($label === 'Present') $present++;
                else $absent++;
            }
            $rows[] = [
                'date' => $date->toDateString(),
                'present' => $present,
                'late' => $late,
                'absent' => $absent,
                'off' => $off,
            ];
            $summary['present'] += $present;
            $summary['late'] += $late;
            $summary['absent'] += $absent;
            $summary['off'] += $off;
        }

        return view('admin.departments.report', [
            'departments' => $departments,
            'selectedDepartment' => $selectedDepartment,
            'departmentId' => $departmentId,
            'range' => $range,
            'start' => $start,
            'end' => $end,
            'rows' => $rows,
            'summary' => $summary,
        ]);
    }

    public function departmentEmployees(Request $request, Department $department)
    {
        $date = $request->query('date')
            ? Carbon::parse($request->query('date'))
            : today();

        $employees = Employee::query()
            ->where('department_id', $department->id)
            ->orderBy('name')
            ->get();

        $employeeRows = $employees->map(function (Employee $emp) use ($date) {
            $status = AttendanceStatusService::computeForDate($emp, $date->copy());
            return [
                'id'            => $emp->id,
                'name'          => $emp->name,
                'position'      => $emp->position,
                'status'        => $status['status_label'],
                'actual_in'     => $status['actual_in'],
                'actual_out'    => $status['actual_out'],
                'worked_seconds'=> $status['worked_seconds'],
                'expected_start'=> $status['expected_start'],
                'expected_end'  => $status['expected_end'],
            ];
        });

        $summary = [
            'present' => $employeeRows->where('status', 'Present')->count(),
            'late'    => $employeeRows->where('status', 'Late')->count(),
            'absent'  => $employeeRows->where('status', 'Absent')->count(),
            'off'     => $employeeRows->where('status', 'Off')->count(),
        ];

        return view('admin.departments.employees', [
            'department'    => $department,
            'date'          => $date,
            'employeeRows'  => $employeeRows,
            'summary'       => $summary,
        ]);
    }
}
