<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use App\Models\Schedule;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        return view('home');
    }
    
    /**
     * Show the employee dashboard
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function employeeDashboard()
    {
        $user = auth()->user();
        $employee = $user->employee;
        if (!$employee) {
            abort(403);
        }

        $today = today()->toDateString();
        $sched = $employee->schedules()->first();

        $timeInRow = Attendance::query()
            ->where('emp_id', $employee->id)
            ->where('attendance_date', $today)
            ->where('type', 0)
            ->orderBy('attendance_time')
            ->first();

        $timeOutRow = Attendance::query()
            ->where('emp_id', $employee->id)
            ->where('attendance_date', $today)
            ->where('type', 1)
            ->orderBy('attendance_time', 'desc')
            ->first();

        $timeIn = $timeInRow?->attendance_time ? Carbon::parse($timeInRow->attendance_time) : null;
        $timeOut = $timeOutRow?->attendance_time ? Carbon::parse($timeOutRow->attendance_time) : null;

        $statusLabel = 'Absent';
        if ($timeInRow) {
            $statusLabel = ((int) $timeInRow->status === 0) ? 'Late' : 'Present';
        } else {
            // If schedule exists and it's still before time-in, show Absent (not yet)
            $statusLabel = 'Absent';
        }

        $workedSeconds = null;
        if ($timeIn && $timeOut && $timeOut->greaterThan($timeIn)) {
            $workedSeconds = $timeOut->diffInSeconds($timeIn);
        }
        
        // Get employee's attendance records
        $attendances = Attendance::where('emp_id', $employee->id)
            ->orderBy('attendance_date', 'desc')
            ->orderBy('attendance_time', 'desc')
            ->limit(10)
            ->get();
        
        // Get employee's leave requests
        $leaves = \App\Models\Leave::where('emp_id', $employee->id)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
        
        return view('employee.dashboard', compact(
            'employee',
            'attendances',
            'leaves',
            'sched',
            'statusLabel',
            'timeIn',
            'timeOut',
            'workedSeconds'
        ));
    }

    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if (!Hash::check($validated['current_password'], $request->user()->password)) {
            return back()
                ->withErrors(['current_password' => 'The current password is incorrect.'])
                ->withInput($request->except(['current_password', 'password', 'password_confirmation']));
        }

        $request->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        // If the user is coming from settings, keep them there
        $fallback = url()->previous() && str_contains(url()->previous(), '/employee/settings')
            ? 'employee.settings'
            : 'employee.dashboard';
        return redirect()->route($fallback)->with('success', 'Your password has been updated.');
    }

    public function employeeSettings()
    {
        $employee = auth()->user()->employee;
        if (!$employee) {
            abort(403);
        }

        $departmentOptions = [
            'Bachelor of Science in Information Technology',
            'Bachelor of Science in Hospitality Management',
            'Bachelor of Science in Tourism Management',
            'Bachelor of Secondary Education - English',
            'Bachelor of Secondary Education - Filipino',
            'Bachelor of Secondary Education - Mathematics',
            'Bachelor of Secondary Education - Social Science',
            'Bachelor of Elementary Education',
        ];

        return view('employee.settings', [
            'employee' => $employee,
            'departmentOptions' => $departmentOptions,
        ]);
    }

    public function updateEmployeeProfile(Request $request)
    {
        $user = auth()->user();
        $employee = $user->employee;
        if (!$employee) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:128'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
                Rule::unique('employees', 'email')->ignore($employee->id),
            ],
            'department' => ['required', 'string', 'max:255'],
            'position' => ['required', 'string', 'max:128'],
        ]);

        DB::transaction(function () use ($validated, $user, $employee) {
            $oldEmail = $employee->email;

            $employee->name = $validated['name'];
            $employee->email = $validated['email'];
            $employee->department = $validated['department'];
            $employee->position = $validated['position'];
            $employee->save();

            // Keep linked user account aligned (relation is based on email)
            $user->name = $validated['name'];
            $user->email = $validated['email'];
            $user->save();

            // If there are other user rows linked to old email, we intentionally do not update them.
            // The system expects a 1:1 user<->employee by email.
        });

        return redirect()->route('employee.settings')->with('success', 'Profile updated successfully.');
    }

    /**
     * Employee attendance logs (daily + recent + monthly)
     */
    public function employeeAttendanceLogs(Request $request)
    {
        $user = auth()->user();
        $employee = $user->employee;
        if (!$employee) {
            abort(403);
        }

        $selectedDateParam = $request->query('date');
        try {
            $selected = $selectedDateParam ? Carbon::parse($selectedDateParam) : today();
        } catch (\Throwable $e) {
            $selected = today();
        }

        $selectedDate = $selected->toDateString();
        $monthStart = $selected->copy()->startOfMonth()->toDateString();
        $monthEnd = $selected->copy()->endOfMonth()->toDateString();

        // Daily record for selected date (time in/out)
        $daily = Attendance::query()
            ->select('emp_id', 'attendance_date')
            ->selectRaw('GROUP_CONCAT(CASE WHEN type = 0 THEN attendance_time END) as time_in')
            ->selectRaw('GROUP_CONCAT(CASE WHEN type = 1 THEN attendance_time END) as time_out')
            ->where('emp_id', $employee->id)
            ->where('attendance_date', $selectedDate)
            ->groupBy('emp_id', 'attendance_date')
            ->first();

        // Recent records (grouped by date)
        $recent = Attendance::query()
            ->select('emp_id', 'attendance_date')
            ->selectRaw('GROUP_CONCAT(CASE WHEN type = 0 THEN attendance_time END) as time_in')
            ->selectRaw('GROUP_CONCAT(CASE WHEN type = 1 THEN attendance_time END) as time_out')
            ->where('emp_id', $employee->id)
            ->groupBy('emp_id', 'attendance_date')
            ->orderBy('attendance_date', 'desc')
            ->limit(14)
            ->get();

        // Monthly presence map (any attendance record counts as present)
        $monthRows = Attendance::query()
            ->select('attendance_date')
            ->where('emp_id', $employee->id)
            ->whereBetween('attendance_date', [$monthStart, $monthEnd])
            ->groupBy('attendance_date')
            ->get();

        $presentDays = [];
        foreach ($monthRows as $row) {
            $presentDays[$row->attendance_date] = true;
        }

        return view('employee.attendance-logs', [
            'employee' => $employee,
            'selected' => $selected,
            'daily' => $daily,
            'recent' => $recent,
            'presentDays' => $presentDays,
        ]);
    }
}
