<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Employee;
use App\Models\MaintenanceRecord;
use App\Models\TimetableSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MaintenanceController extends Controller
{
    private const FIXED_SCHOOL_ADDRESS = '06 Kingfisher Street, Zabarte Subd., Kaligayahan, Novaliches, Quezon City';

    private const DEFAULT_COURSES = [
        ['code' => 'BSIT', 'name' => 'Bachelor of Science in Information Technology'],
        ['code' => 'BSCS', 'name' => 'Bachelor of Science in Computer Science'],
        ['code' => 'BSIS', 'name' => 'Bachelor of Science in Information Systems'],
        ['code' => 'BEED', 'name' => 'Bachelor of Elementary Education'],
        ['code' => 'BSED-ENG', 'name' => 'Bachelor of Secondary Education major in English'],
        ['code' => 'BSED-MATH', 'name' => 'Bachelor of Secondary Education major in Mathematics'],
        ['code' => 'BSHM', 'name' => 'Bachelor of Science in Hospitality Management'],
        ['code' => 'BSTM', 'name' => 'Bachelor of Science in Tourism Management'],
    ];

    public function formBuilder(): View
    {
        $this->ensureDefaultCourses();

        $courses = Course::query()->orderBy('code')->orderBy('name')->get();
        TimetableSetting::setValue('school_address', self::FIXED_SCHOOL_ADDRESS);

        return view('admin.maintenance-form', [
            'courses' => $courses,
            'timetableSettings' => [
                'school_name' => TimetableSetting::getValue('school_name', 'Colegio de Sta. Teresa De Avila'),
                'school_address' => self::FIXED_SCHOOL_ADDRESS,
                'semester_label' => TimetableSetting::getValue('semester_label', '1st Semester'),
                'school_year' => TimetableSetting::getValue('school_year', date('Y').'-'.(date('Y') + 1)),
            ],
            'schoolYearOptions' => $this->schoolYearOptions(),
        ]);
    }

    public function storeCourse(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:30', 'unique:courses,code'],
            'name' => ['required', 'string', 'max:150'],
        ]);

        Course::create($validated);

        flash()->success('Success', 'Course added successfully.');

        return redirect()->route('admin.maintenance_form');
    }

    public function updateCourse(Request $request, Course $course): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:30', 'unique:courses,code,' . $course->id],
            'name' => ['required', 'string', 'max:150'],
        ]);

        $course->update($validated);

        flash()->success('Success', 'Course updated successfully.');

        return redirect()->route('admin.maintenance_form');
    }

    public function destroyCourse(Course $course): RedirectResponse
    {
        $course->delete();

        flash()->success('Success', 'Course deleted successfully.');

        return redirect()->route('admin.maintenance_form');
    }

    public function updateTimetableSettings(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'school_name' => ['required', 'string', 'max:255'],
            'semester_label' => ['required', Rule::in(['1st Semester', '2nd Semester'])],
            'school_year' => ['required', 'string', 'max:64'],
        ]);

        $validated['school_address'] = self::FIXED_SCHOOL_ADDRESS;

        foreach ($validated as $key => $value) {
            TimetableSetting::setValue($key, $value);
        }

        flash()->success('Success', 'Schedule export settings updated successfully.');

        return redirect()->route('admin.maintenance_form');
    }

    public function index(): View
    {
        $employees = Employee::orderBy('name')->get(['id', 'name', 'position', 'employment_status']);
        $records = MaintenanceRecord::with(['employee', 'processor'])
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('admin.maintenance', compact('employees', 'records'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'employee_id' => ['required', 'exists:employees,id'],
            'form_type' => ['required', 'in:departure,status_change'],
            'to_status' => ['required', 'in:active,inactive,on_leave,suspended,resigned,terminated,retired'],
            'effective_date' => ['nullable', 'date'],
            'reason' => ['nullable', 'string', 'max:2000'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $employee = Employee::findOrFail($validated['employee_id']);
        $oldStatus = $employee->employment_status ?: 'active';
        $newStatus = $validated['to_status'];

        if ($validated['form_type'] === 'departure' && !in_array($newStatus, ['resigned', 'terminated', 'retired'], true)) {
            $newStatus = 'resigned';
        }

        $employee->employment_status = $newStatus;
        $employee->status_updated_by = auth()->id();

        if ($validated['form_type'] === 'departure') {
            $employee->departure_date = $validated['effective_date'] ?? now()->toDateString();
            $employee->departure_reason = $validated['reason'] ?? $validated['notes'] ?? null;
        } elseif (!in_array($newStatus, ['resigned', 'terminated', 'retired'], true)) {
            $employee->departure_date = null;
            $employee->departure_reason = null;
        }

        $employee->save();

        MaintenanceRecord::create([
            'employee_id' => $employee->id,
            'processed_by' => auth()->id(),
            'form_type' => $validated['form_type'],
            'from_status' => $oldStatus,
            'to_status' => $newStatus,
            'effective_date' => $validated['effective_date'] ?? null,
            'reason' => $validated['reason'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ]);

        flash()->success('Success', 'Maintenance form submitted and employee status updated.');

        return back();
    }

    private function ensureDefaultCourses(): void
    {
        foreach (self::DEFAULT_COURSES as $course) {
            Course::firstOrCreate(
                ['code' => $course['code']],
                ['name' => $course['name'], 'description' => null]
            );
        }
    }

    private function schoolYearOptions(): array
    {
        $year = (int) date('Y');
        $options = [];
        for ($i = -1; $i <= 4; $i++) {
            $start = $year + $i;
            $options[] = $start.'-'.($start + 1);
        }

        return $options;
    }
}
