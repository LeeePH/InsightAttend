<?php

namespace App\Http\Controllers;

use App\Models\ClassSection;
use App\Models\Course;
use App\Models\Employee;
use App\Models\EmployeeTimetableEntry;
use App\Models\TimetableSetting;
use App\Models\User;
use App\Services\SchedulingDepartmentService;
use App\Services\TimetableConflictService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class EmployeeTimetableController extends Controller
{
    public function __construct(
        private TimetableConflictService $conflicts
    ) {
    }

    private function assertScheduler(User $user): void
    {
        abort_unless($user->hasAnyRole(['admin', 'hr', 'secretary']), 403);
    }

    private function canEditDepartment(User $user, string $departmentKey): bool
    {
        if ($user->hasRole('admin') || $user->hasRole('hr')) {
            return true;
        }

        return $user->hasRole('secretary')
            && strtoupper((string) $user->managed_schedule_department) === strtoupper($departmentKey);
    }

    private function dayShort(int $d): string
    {
        $map = [1 => 'Mon', 2 => 'Tue', 3 => 'Wed', 4 => 'Thu', 5 => 'Fri', 6 => 'Sat', 7 => 'Sun'];

        return $map[$d] ?? (string) $d;
    }

    private function extractTimeBlocks(array $starts, array $ends): array
    {
        $blocks = [];

        foreach ($starts as $index => $start) {
            $start = trim((string) $start);
            $end = trim((string) ($ends[$index] ?? ''));

            if ($start === '' && $end === '') {
                continue;
            }

            if ($start === '' || $end === '') {
                throw new \InvalidArgumentException('Each time block needs both a start and end time.');
            }

            $blocks[] = [
                'time_start' => Carbon::parse($start)->format('H:i'),
                'time_end' => Carbon::parse($end)->format('H:i'),
            ];
        }

        if (empty($blocks)) {
            throw new \InvalidArgumentException('Add at least one time block.');
        }

        usort($blocks, fn ($a, $b) => strcmp($a['time_start'], $b['time_start']));

        return $blocks;
    }

    private function buildEmployeeScheduleRows($entries)
    {
        return $entries
            ->map(function (EmployeeTimetableEntry $entry) {
                $times = collect($entry->resolvedTimeBlocks())
                    ->map(function ($block) {
                        return [
                            'display' => Carbon::parse($block['time_start'])->format('g:i A').' - '.Carbon::parse($block['time_end'])->format('g:i A'),
                            'start' => $block['time_start'],
                            'end' => $block['time_end'],
                        ];
                    })
                    ->values();

                return [
                    'id' => $entry->id,
                    'day_of_week' => (int) $entry->day_of_week,
                    'room' => $entry->room,
                    'department_key' => $entry->department_key,
                    'course_code' => $entry->course?->code,
                    'course_name' => $entry->course?->name,
                    'section_label' => $entry->classSection?->section_label,
                    'faculty_name' => $entry->employee?->name,
                    'times' => $times,
                    'time_start' => $times->first()['start'] ?? '00:00',
                ];
            })
            ->sortBy(fn ($row) => sprintf('%02d|%s|%s|%s|%s', $row['day_of_week'], $row['time_start'], $row['room'], $row['course_code'], $row['section_label'] ?? ''))
            ->values();
    }

    private function timetableExportSettings(): array
    {
        return [
            'school_name' => TimetableSetting::getValue('school_name', 'Colegio de Sta. Teresa De Avila'),
            'school_address' => TimetableSetting::getValue('school_address', ''),
            'semester_label' => TimetableSetting::getValue('semester_label', '1st Semester'),
            'school_year' => TimetableSetting::getValue('school_year', date('Y').'-'.(date('Y') + 1)),
        ];
    }

    public function index(Request $request): View
    {
        $user = $request->user();
        $this->assertScheduler($user);

        $query = EmployeeTimetableEntry::with(['employee.department', 'course', 'classSection'])
            ->orderBy('department_key')
            ->orderBy('employee_id')
            ->orderBy('day_of_week')
            ->orderBy('time_start');

        if ($user->hasRole('secretary')) {
            $key = strtoupper((string) $user->managed_schedule_department);
            abort_unless(
                SchedulingDepartmentService::isValidKey($key),
                403,
                'Your account must have a managed scheduling department (IT, EDUC, or SHTM). Ask an administrator to set this in User Management.'
            );
            $query->where('department_key', $key);
        } elseif ($request->filled('department_key')) {
            $dk = strtoupper((string) $request->department_key);
            if (SchedulingDepartmentService::isValidKey($dk)) {
                $query->where('department_key', $dk);
            }
        }

        $entries = $query->get();
        $employees = Employee::with('department')->orderBy('name')->get();
        if ($user->hasRole('secretary')) {
            $key = strtoupper((string) $user->managed_schedule_department);
            $employees = $employees->filter(function (Employee $employee) use ($key) {
                return SchedulingDepartmentService::resolveEmployeeDepartmentKey($employee) === $key;
            })->values();
        }

        $todayDow = (int) now()->format('N');
        $departmentLabels = SchedulingDepartmentService::labels();
        $courses = Course::query()->orderBy('code')->orderBy('name')->get();

        $classSectionsQuery = ClassSection::query()->orderBy('department_key')->orderBy('year_level')->orderBy('section_label');
        if ($user->hasRole('secretary')) {
            $classSectionsQuery->where('department_key', strtoupper((string) $user->managed_schedule_department));
        }
        $classSections = $classSectionsQuery->get();

        $todayEntries = $entries->filter(function (EmployeeTimetableEntry $row) use ($todayDow) {
            return (int) $row->day_of_week === $todayDow;
        });

        return view('admin.employee_timetable.index', [
            'entries' => $entries,
            'employees' => $employees,
            'courses' => $courses,
            'classSections' => $classSections,
            'todayDow' => $todayDow,
            'todayEntries' => $todayEntries,
            'departmentLabels' => $departmentLabels,
            'dayShort' => fn (int $d) => $this->dayShort($d),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();
        $this->assertScheduler($user);

        $validated = $request->validate([
            'employee_id' => ['required', 'exists:employees,id'],
            'course_id' => ['required', 'exists:courses,id'],
            'class_section_id' => ['nullable', 'exists:class_sections,id'],
            'day_of_week' => ['required', 'integer', 'min:1', 'max:7'],
            'time_start_blocks' => ['required', 'array', 'min:1'],
            'time_start_blocks.*' => ['nullable', 'date_format:H:i'],
            'time_end_blocks' => ['required', 'array', 'min:1'],
            'time_end_blocks.*' => ['nullable', 'date_format:H:i'],
            'room' => ['required', 'string', 'max:64'],
            'department_key' => ['required', Rule::in(SchedulingDepartmentService::KEYS)],
        ]);

        $employee = Employee::with('department')->findOrFail($validated['employee_id']);
        $deptKey = strtoupper($validated['department_key']);

        abort_unless($this->canEditDepartment($user, $deptKey), 403);

        $resolved = SchedulingDepartmentService::resolveEmployeeDepartmentKey($employee);
        if (!$resolved) {
            return back()->withInput()->withErrors([
                'employee_id' => 'Set this employee\'s scheduling department (IT, EDUC, or SHTM) on their profile before adding timetable entries.',
            ]);
        }
        if ($resolved !== $deptKey) {
            return back()->withInput()->withErrors([
                'department_key' => 'Department must match the employee\'s scheduling department ('.$resolved.').',
            ]);
        }

        $sectionsExist = ClassSection::query()->where('department_key', $deptKey)->exists();
        $classSectionId = $validated['class_section_id'] ?? null;
        if ($sectionsExist && !$classSectionId) {
            return back()->withInput()->withErrors([
                'class_section_id' => 'Select a class section (create one under Class sections if needed).',
            ]);
        }

        $classSection = null;
        if ($classSectionId) {
            $classSection = ClassSection::findOrFail((int) $classSectionId);
            if (strtoupper((string) $classSection->department_key) !== $deptKey) {
                return back()->withInput()->withErrors([
                    'class_section_id' => 'Class section must belong to scheduling department '.$deptKey.'.',
                ]);
            }
        }

        try {
            $blocks = $this->extractTimeBlocks(
                $request->input('time_start_blocks', []),
                $request->input('time_end_blocks', [])
            );
        } catch (\InvalidArgumentException $e) {
            return back()->withInput()->withErrors(['time_start_blocks' => $e->getMessage()]);
        }

        [$selfConflict, $selfMessage] = $this->conflicts->validateBatchIntervals($blocks);
        if ($selfConflict) {
            return back()->withInput()->withErrors(['time_start_blocks' => $selfMessage]);
        }

        foreach ($blocks as $block) {
            [$bad, $msg] = $this->conflicts->validateEntry(
                (int) $employee->id,
                (int) $validated['day_of_week'],
                $block['time_start'],
                $block['time_end'],
                $validated['room'],
                null
            );
            if ($bad) {
                return back()->withInput()->withErrors(['time_start_blocks' => $msg]);
            }
        }

        EmployeeTimetableEntry::create([
            'employee_id' => $employee->id,
            'course_id' => $validated['course_id'],
            'class_section_id' => $classSection?->id,
            'day_of_week' => (int) $validated['day_of_week'],
            'time_start' => $blocks[0]['time_start'],
            'time_end' => $blocks[count($blocks) - 1]['time_end'],
            'time_blocks' => $blocks,
            'room' => trim($validated['room']),
            'department_key' => $deptKey,
        ]);

        flash()->success('Success', count($blocks) > 1 ? 'Schedule with multiple time blocks added.' : 'Schedule entry added.');

        return redirect()->route('employee_timetable.index');
    }

    public function update(Request $request, EmployeeTimetableEntry $entry): RedirectResponse
    {
        $user = $request->user();
        $this->assertScheduler($user);

        $validated = $request->validate([
            'employee_id' => ['required', 'exists:employees,id'],
            'course_id' => ['required', 'exists:courses,id'],
            'class_section_id' => ['nullable', 'exists:class_sections,id'],
            'day_of_week' => ['required', 'integer', 'min:1', 'max:7'],
            'time_start_blocks' => ['required', 'array', 'min:1'],
            'time_start_blocks.*' => ['nullable', 'date_format:H:i'],
            'time_end_blocks' => ['required', 'array', 'min:1'],
            'time_end_blocks.*' => ['nullable', 'date_format:H:i'],
            'room' => ['required', 'string', 'max:64'],
            'department_key' => ['required', Rule::in(SchedulingDepartmentService::KEYS)],
        ]);

        $employee = Employee::with('department')->findOrFail($validated['employee_id']);
        $deptKey = strtoupper($validated['department_key']);

        abort_unless($this->canEditDepartment($user, $deptKey), 403);
        abort_unless($this->canEditDepartment($user, $entry->department_key), 403);

        $resolved = SchedulingDepartmentService::resolveEmployeeDepartmentKey($employee);
        if (!$resolved || $resolved !== $deptKey) {
            return back()->withInput()->withErrors([
                'department_key' => 'Department must match the employee\'s scheduling department.',
            ]);
        }

        $sectionsExist = ClassSection::query()->where('department_key', $deptKey)->exists();
        $classSectionId = $validated['class_section_id'] ?? null;
        if ($sectionsExist && !$classSectionId) {
            return back()->withInput()->withErrors([
                'class_section_id' => 'Select a class section (create one under Class sections if needed).',
            ]);
        }

        $classSection = null;
        if ($classSectionId) {
            $classSection = ClassSection::findOrFail((int) $classSectionId);
            if (strtoupper((string) $classSection->department_key) !== $deptKey) {
                return back()->withInput()->withErrors([
                    'class_section_id' => 'Class section must belong to scheduling department '.$deptKey.'.',
                ]);
            }
        }

        try {
            $blocks = $this->extractTimeBlocks(
                $request->input('time_start_blocks', []),
                $request->input('time_end_blocks', [])
            );
        } catch (\InvalidArgumentException $e) {
            return back()->withInput()->withErrors(['time_start_blocks' => $e->getMessage()]);
        }

        [$selfConflict, $selfMessage] = $this->conflicts->validateBatchIntervals($blocks);
        if ($selfConflict) {
            return back()->withInput()->withErrors(['time_start_blocks' => $selfMessage]);
        }

        foreach ($blocks as $block) {
            [$bad, $msg] = $this->conflicts->validateEntry(
                (int) $employee->id,
                (int) $validated['day_of_week'],
                $block['time_start'],
                $block['time_end'],
                $validated['room'],
                (int) $entry->id
            );
            if ($bad) {
                return back()->withInput()->withErrors(['time_start_blocks' => $msg]);
            }
        }

        $entry->update([
            'employee_id' => $employee->id,
            'course_id' => $validated['course_id'],
            'class_section_id' => $classSection?->id,
            'day_of_week' => (int) $validated['day_of_week'],
            'time_start' => $blocks[0]['time_start'],
            'time_end' => $blocks[count($blocks) - 1]['time_end'],
            'time_blocks' => $blocks,
            'room' => trim($validated['room']),
            'department_key' => $deptKey,
        ]);

        flash()->success('Success', 'Schedule entry updated.');

        return redirect()->route('employee_timetable.index');
    }

    public function destroy(Request $request, EmployeeTimetableEntry $entry): RedirectResponse
    {
        $user = $request->user();
        $this->assertScheduler($user);
        abort_unless($this->canEditDepartment($user, $entry->department_key), 403);

        $entry->delete();
        flash()->success('Success', 'Schedule entry removed.');

        return redirect()->route('employee_timetable.index');
    }

    public function pdf(Request $request): Response
    {
        $user = $request->user();
        $this->assertScheduler($user);

        $query = EmployeeTimetableEntry::with(['employee.department', 'course', 'classSection'])
            ->orderBy('department_key')
            ->orderBy('course_id')
            ->orderBy('day_of_week')
            ->orderBy('time_start');

        if ($user->hasRole('secretary')) {
            $key = strtoupper((string) $user->managed_schedule_department);
            abort_unless(SchedulingDepartmentService::isValidKey($key), 403);
            $query->where('department_key', $key);
        } elseif ($request->filled('department_key')) {
            $dk = strtoupper((string) $request->department_key);
            if (SchedulingDepartmentService::isValidKey($dk)) {
                $query->where('department_key', $dk);
            }
        }

        $entries = $query->get();
        $selectedDepartmentKey = $user->hasRole('secretary')
            ? strtoupper((string) $user->managed_schedule_department)
            : strtoupper((string) $request->input('department_key', ''));

        $pdf = Pdf::loadView('pdf.employee_timetable', [
            'entries' => $entries,
            'selectedDepartmentKey' => $selectedDepartmentKey,
            'exportSettings' => $this->timetableExportSettings(),
            'departmentTitles' => SchedulingDepartmentService::schoolTitles(),
            'generatedAt' => now(),
            'dayShort' => fn (int $d) => $this->dayShort($d),
        ])->setPaper('a4', 'landscape');

        return $pdf->download('employee-schedule-'.now()->format('Y-m-d').'.pdf');
    }

    public function sectionPdf(Request $request, ClassSection $classSection): Response
    {
        $user = $request->user();
        $this->assertScheduler($user);
        abort_unless($this->canEditDepartment($user, $classSection->department_key), 403);

        $entries = EmployeeTimetableEntry::with(['employee.department', 'course', 'classSection'])
            ->where('class_section_id', $classSection->id)
            ->orderBy('day_of_week')
            ->orderBy('time_start')
            ->get();

        $pdf = Pdf::loadView('pdf.employee_timetable_section', [
            'classSection' => $classSection,
            'entries' => $entries,
            'exportSettings' => $this->timetableExportSettings(),
            'departmentTitles' => SchedulingDepartmentService::schoolTitles(),
            'generatedAt' => now(),
            'dayShort' => fn (int $d) => $this->dayShort($d),
        ])->setPaper('a4', 'landscape');

        $slug = Str::slug($classSection->section_label) ?: 'section';

        return $pdf->download('section-schedule-'.$slug.'-'.now()->format('Y-m-d').'.pdf');
    }

    public function mySchedule(Request $request): View
    {
        $user = $request->user();
        abort_unless($user->hasRole('employee'), 403);

        $employee = $user->employee;
        abort_unless($employee, 404, 'No employee profile is linked to this account.');

        $entries = EmployeeTimetableEntry::with(['employee', 'course', 'classSection'])
            ->where('employee_id', $employee->id)
            ->orderBy('day_of_week')
            ->orderBy('time_start')
            ->get();

        return view('employee.my_schedule', [
            'employee' => $employee,
            'scheduleRows' => $this->buildEmployeeScheduleRows($entries),
            'todayDow' => (int) now()->format('N'),
            'dayShort' => fn (int $d) => $this->dayShort($d),
        ]);
    }

    public function mySchedulePreview(Request $request): View
    {
        $user = $request->user();
        abort_unless($user->hasRole('employee'), 403);

        $employee = $user->employee;
        abort_unless($employee, 404, 'No employee profile is linked to this account.');

        $entries = EmployeeTimetableEntry::with(['employee', 'course', 'classSection'])
            ->where('employee_id', $employee->id)
            ->orderBy('day_of_week')
            ->orderBy('time_start')
            ->get();

        return view('employee.my_schedule_preview', [
            'employee' => $employee,
            'scheduleRows' => $this->buildEmployeeScheduleRows($entries),
            'generatedAt' => now(),
            'dayShort' => fn (int $d) => $this->dayShort($d),
        ]);
    }

    public function mySchedulePdf(Request $request): Response
    {
        $user = $request->user();
        abort_unless($user->hasRole('employee'), 403);

        $employee = $user->employee;
        abort_unless($employee, 404, 'No employee profile is linked to this account.');

        $entries = EmployeeTimetableEntry::with(['employee', 'course', 'classSection'])
            ->where('employee_id', $employee->id)
            ->orderBy('day_of_week')
            ->orderBy('time_start')
            ->get();

        $pdf = Pdf::loadView('pdf.employee_my_schedule', [
            'employee' => $employee,
            'scheduleRows' => $this->buildEmployeeScheduleRows($entries),
            'generatedAt' => now(),
            'dayShort' => fn (int $d) => $this->dayShort($d),
        ])->setPaper('a4', 'landscape');

        return $pdf->download('my-schedule-'.str($employee->name)->slug('-').'-'.now()->format('Y-m-d').'.pdf');
    }
}
