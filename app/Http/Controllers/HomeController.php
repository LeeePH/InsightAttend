<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\DiscountApplication;
use App\Models\Employee;
use App\Models\Leave;
use App\Models\LoanRequest;
use App\Models\OvertimeAuthorizationRequest;
use App\Models\PermitToTeachOutsideRequest;
use App\Models\ResignationRequest;
use App\Models\SubstitutionRequest;
use App\Models\UndertimeAuthorizationRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use App\Services\AttendanceStatusService;
use App\Services\ShiftResolver;

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

        $today = today();
        $sched = $employee->schedules()->first();

        // Compute daily status against expected shift (supports shifting + overnight)
        $status = AttendanceStatusService::computeForDate($employee, $today->copy());
        $statusLabel = $status['status_label'];
        $timeIn = $status['actual_in'];
        $timeOut = $status['actual_out'];
        $workedSeconds = $status['worked_seconds'];

        // For display: expected shift window (if available)
        $resolved = ShiftResolver::resolve($employee, $today->copy());
        $expectedStart = $resolved['start'] ?? null;
        $expectedEnd = $resolved['end'] ?? null;
        $expectedShift = $resolved['shift'] ?? null;

        [$presentDays, $lateCount, $absenceCount] = $this->buildMonthlyAttendanceSummary($employee, $today);
        $recentAttendanceActivity = $this->buildRecentAttendanceActivity($employee, $today);
        $recentRequests = $this->buildRecentRequests($employee);

        return view('employee.dashboard', compact(
            'employee',
            'sched',
            'statusLabel',
            'timeIn',
            'timeOut',
            'workedSeconds',
            'expectedStart',
            'expectedEnd',
            'expectedShift',
            'presentDays',
            'lateCount',
            'absenceCount',
            'recentAttendanceActivity',
            'recentRequests'
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

        return view('profile.index', [
            'user' => auth()->user(),
            'employee' => $employee->load(['department', 'schedules', 'shiftRotation']),
            'isOwnProfile' => true,
            'isEmployeeProfile' => true,
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
            'position' => ['required', 'string', 'max:128'],
            'phone' => ['nullable', 'string', 'max:30'],
            'date_hired' => ['nullable', 'date'],
            'employment_type' => ['nullable', 'in:full_time,part_time'],
            'skills' => ['nullable', 'string', 'max:5000'],
            'achievements' => ['nullable', 'string', 'max:5000'],
            'emergency_contact_name' => ['nullable', 'string', 'max:255'],
            'emergency_contact_relationship' => ['nullable', 'string', 'max:255'],
            'emergency_contact_phone' => ['nullable', 'string', 'max:30'],
            'profile_photo' => ['nullable', 'image', 'max:5120'],
        ]);

        DB::transaction(function () use ($request, $validated, $user, $employee) {
            $employee->name = $validated['name'];
            $employee->email = $validated['email'];
            $employee->position = $validated['position'];
            $employee->phone = $validated['phone'] ?? null;
            $employee->date_hired = $validated['date_hired'] ?? null;
            $employee->employment_type = $validated['employment_type'] ?? null;
            $employee->skills = $validated['skills'] ?? null;
            $employee->achievements = $validated['achievements'] ?? null;
            $employee->emergency_contact_name = $validated['emergency_contact_name'] ?? null;
            $employee->emergency_contact_relationship = $validated['emergency_contact_relationship'] ?? null;
            $employee->emergency_contact_phone = $validated['emergency_contact_phone'] ?? null;
            if ($request->hasFile('profile_photo')) {
                $path = $request->file('profile_photo')->store('employee-avatars', 'public');
                $employee->face_image = Storage::url($path);
            }
            $employee->save();

            $user->name = $validated['name'];
            $user->email = $validated['email'];
            $user->save();
        });

        return redirect()->route('profile')->with('success', 'Profile updated successfully.');
    }

    public function profile(Request $request)
    {
        $user = $request->user();
        $employee = $user->employee;

        if ($employee) {
            return view('profile.index', [
                'user' => $user,
                'employee' => $employee->load(['department', 'schedules', 'shiftRotation']),
                'isOwnProfile' => true,
                'isEmployeeProfile' => true,
            ]);
        }

        return view('profile.index', [
            'user' => $user->load('roles'),
            'employee' => null,
            'isOwnProfile' => true,
            'isEmployeeProfile' => false,
        ]);
    }

    public function showLockScreen(Request $request)
    {
        $lockData = $request->session()->get('lock_screen');
        if (!$lockData) {
            return redirect()->route('login');
        }

        return view('auth.lock-screen', [
            'lockData' => $lockData,
        ]);
    }

    public function lockScreen(Request $request)
    {
        $user = $request->user();
        $request->session()->put('lock_screen', [
            'user_id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'avatar' => $user->employee?->face_image,
        ]);

        Auth::logout();
        $request->session()->save();

        return redirect()->route('lock.screen.form');
    }

    public function unlockScreen(Request $request)
    {
        $lockData = $request->session()->get('lock_screen');
        if (!$lockData || empty($lockData['user_id'])) {
            return redirect()->route('login');
        }

        $request->validate([
            'password' => ['required', 'string'],
        ]);

        $user = User::find($lockData['user_id']);
        if (!$user) {
            $request->session()->forget('lock_screen');
            return redirect()->route('login');
        }

        if (!Auth::attempt(['email' => $user->email, 'password' => $request->password])) {
            return back()->withErrors([
                'password' => 'The password is incorrect.',
            ]);
        }

        $request->session()->forget('lock_screen');

        return redirect()->route($user->hasRole('admin') ? 'admin' : 'employee.dashboard');
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

    protected function buildMonthlyAttendanceSummary(Employee $employee, Carbon $today): array
    {
        $presentDays = 0;
        $lateCount = 0;
        $absenceCount = 0;

        $cursor = $today->copy()->startOfMonth();
        while ($cursor->lte($today)) {
            $resolved = ShiftResolver::resolve($employee, $cursor->copy()->startOfDay());
            if (($resolved['is_off'] ?? false) === true) {
                $cursor->addDay();
                continue;
            }

            $status = AttendanceStatusService::computeForDate($employee, $cursor->copy());
            if ($status['status_label'] === 'Present') {
                $presentDays++;
            } elseif ($status['status_label'] === 'Late') {
                $presentDays++;
                $lateCount++;
            } elseif ($status['status_label'] === 'Absent') {
                $absenceCount++;
            }

            $cursor->addDay();
        }

        return [$presentDays, $lateCount, $absenceCount];
    }

    protected function buildRecentAttendanceActivity(Employee $employee, Carbon $today)
    {
        $items = collect();
        $cursor = $today->copy();
        $checkedDays = 0;

        while ($items->count() < 7 && $checkedDays < 21) {
            $resolved = ShiftResolver::resolve($employee, $cursor->copy()->startOfDay());
            if (($resolved['is_off'] ?? false) !== true) {
                $status = AttendanceStatusService::computeForDate($employee, $cursor->copy());
                $items->push([
                    'date' => $cursor->copy(),
                    'status' => $status['status_label'],
                    'actual_in' => $status['actual_in'],
                    'actual_out' => $status['actual_out'],
                    'expected_start' => $status['expected_start'],
                    'expected_end' => $status['expected_end'],
                    'worked_seconds' => $status['worked_seconds'],
                ]);
            }

            $cursor->subDay();
            $checkedDays++;
        }

        return $items;
    }

    protected function buildRecentRequests(Employee $employee)
    {
        $requests = collect();

        $models = [
            ['model' => Leave::class, 'label' => 'Leave Request'],
            ['model' => LoanRequest::class, 'label' => 'Loan Request'],
            ['model' => DiscountApplication::class, 'label' => 'Discount Application'],
            ['model' => OvertimeAuthorizationRequest::class, 'label' => 'Overtime Authorization'],
            ['model' => UndertimeAuthorizationRequest::class, 'label' => 'Undertime Authorization'],
            ['model' => PermitToTeachOutsideRequest::class, 'label' => 'Permit To Teach Outside'],
            ['model' => SubstitutionRequest::class, 'label' => 'Substitution Request'],
            ['model' => ResignationRequest::class, 'label' => 'Resignation Request'],
        ];

        foreach ($models as $config) {
            $rows = $config['model']::query()
                ->where('emp_id', $employee->id)
                ->orderBy('created_at', 'desc')
                ->limit(4)
                ->get()
                ->map(function ($row) use ($config) {
                    return [
                        'label' => $config['label'],
                        'status' => (int) ($row->status ?? 0),
                        'submitted_at' => $row->created_at ?? now(),
                    ];
                });

            $requests = $requests->merge($rows);
        }

        return $requests
            ->sortByDesc('submitted_at')
            ->take(6)
            ->values();
    }
}
