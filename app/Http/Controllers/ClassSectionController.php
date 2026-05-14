<?php

namespace App\Http\Controllers;

use App\Models\ClassSection;
use App\Models\Course;
use App\Models\User;
use App\Services\SchedulingDepartmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ClassSectionController extends Controller
{
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

    public function index(Request $request): View
    {
        $user = $request->user();
        $this->assertScheduler($user);

        $query = ClassSection::query()->orderBy('department_key')->orderBy('year_level')->orderBy('section_label');

        if ($user->hasRole('secretary')) {
            $key = strtoupper((string) $user->managed_schedule_department);
            abort_unless(
                SchedulingDepartmentService::isValidKey($key),
                403,
                'Your account must have a managed scheduling department (IT, EDUC, or SHTM).'
            );
            $query->where('department_key', $key);
        } elseif ($request->filled('department_key')) {
            $dk = strtoupper((string) $request->department_key);
            if (SchedulingDepartmentService::isValidKey($dk)) {
                $query->where('department_key', $dk);
            }
        }

        $sections = $query->get();
        $departmentLabels = SchedulingDepartmentService::labels();
        $courses = Course::query()->orderBy('code')->orderBy('name')->get();

        return view('admin.class_sections.index', [
            'sections'         => $sections,
            'departmentLabels' => $departmentLabels,
            'courses'          => $courses,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();
        $this->assertScheduler($user);

        $validated = $request->validate([
            'department_key' => ['required', Rule::in(SchedulingDepartmentService::KEYS)],
            'year_level' => ['nullable', 'integer', 'min:1', 'max:6'],
            'section_label' => [
                'required',
                'string',
                'max:64',
                Rule::unique('class_sections', 'section_label')->where(
                    fn ($q) => $q->where('department_key', strtoupper((string) $request->input('department_key')))
                ),
            ],
        ]);

        $deptKey = strtoupper($validated['department_key']);
        abort_unless($this->canEditDepartment($user, $deptKey), 403);

        ClassSection::create([
            'department_key' => $deptKey,
            'year_level' => $validated['year_level'] ?? null,
            'section_label' => trim($validated['section_label']),
        ]);

        flash()->success('Success', 'Class section created.');

        return redirect()->route('class_sections.index', array_filter([
            'department_key' => $request->input('return_department_key'),
        ]));
    }

    public function update(Request $request, ClassSection $classSection): RedirectResponse
    {
        $user = $request->user();
        $this->assertScheduler($user);
        abort_unless($this->canEditDepartment($user, $classSection->department_key), 403);

        $validated = $request->validate([
            'department_key' => ['required', Rule::in(SchedulingDepartmentService::KEYS)],
            'year_level' => ['nullable', 'integer', 'min:1', 'max:6'],
            'section_label' => [
                'required',
                'string',
                'max:64',
                Rule::unique('class_sections', 'section_label')
                    ->where(fn ($q) => $q->where('department_key', strtoupper((string) $request->input('department_key'))))
                    ->ignore($classSection->id),
            ],
        ]);

        $deptKey = strtoupper($validated['department_key']);
        abort_unless($this->canEditDepartment($user, $deptKey), 403);

        $classSection->update([
            'department_key' => $deptKey,
            'year_level' => $validated['year_level'] ?? null,
            'section_label' => trim($validated['section_label']),
        ]);

        flash()->success('Success', 'Class section updated.');

        return redirect()->route('class_sections.index', array_filter([
            'department_key' => $request->input('return_department_key'),
        ]));
    }

    public function destroy(Request $request, ClassSection $classSection): RedirectResponse
    {
        $user = $request->user();
        $this->assertScheduler($user);
        abort_unless($this->canEditDepartment($user, $classSection->department_key), 403);

        if ($classSection->timetableEntries()->exists()) {
            flash()->error('Error', 'Cannot delete a section that still has schedule entries. Reassign or remove those entries first.');

            return back();
        }

        $classSection->delete();
        flash()->success('Success', 'Class section removed.');

        return redirect()->route('class_sections.index', array_filter([
            'department_key' => $request->input('return_department_key'),
        ]));
    }
}
