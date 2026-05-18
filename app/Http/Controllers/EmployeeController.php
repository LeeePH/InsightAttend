<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Employee;
use App\Models\Role;
use App\Models\Schedule;
use App\Models\Department;
use App\Http\Controllers\DepartmentController;
use App\Http\Requests\EmployeeRec;
use RealRashid\SweetAlert\Facades\Alert;
use App\Models\EmployeeShiftRotation;

class EmployeeController extends Controller
{
    protected function fillProfileDetails(Employee $employee, EmployeeRec $request): void
    {
        $employee->date_hired = $request->input('date_hired');
        $employee->employment_type = $request->input('employment_type');
        $employee->employee_number = $request->input('employee_number') ?: null;
        $employee->educational_background = $request->input('educational_background');
        $employee->work_experience = $request->input('work_experience');
        $employee->emergency_contact_name = $request->input('emergency_contact_name');
        $employee->emergency_contact_relationship = $request->input('emergency_contact_relationship');
        $employee->emergency_contact_phone = $request->input('emergency_contact_phone');
    }
   
    public function index()
    {
        DepartmentController::ensureFixedDepartments();
        return view('admin.employee')->with([
            'employees' => Employee::with(['department', 'user.roles'])->get(),
            'schedules' => Schedule::all(),
            'departments' => Department::query()->orderBy('name')->get(),
        ]);
    }

    public function store(EmployeeRec $request)
    {
        $request->validated();

        $deptName = $request->department_id ? Department::find($request->department_id)?->name : null;

        $employee = new Employee;
        $employee->name = $request->name;
        $employee->position = $request->position;
        $employee->email = $request->email;
        $employee->phone = $request->phone;
        $employee->pin_code = bcrypt($request->pin_code);
        $employee->department_id = $request->department_id;
        // Keep legacy string column aligned for older screens/exports
        $employee->department = $deptName;
        $employee->schedule_department_key = $request->input('portal_role') === 'secretary'
            ? ($request->input('schedule_department_key') ?: null)
            : null;
        $this->fillProfileDetails($employee, $request);
        if (!$employee->date_hired) {
            $employee->date_hired = now()->toDateString();
        }

        // Face recognition data
        if ($request->face_descriptor) {
            $employee->face_descriptor = $request->face_descriptor;
            $employee->face_registered = true;
        }
        if ($request->face_image) {
            $employee->face_image = $request->face_image;
        }

        $employee->save();

        $schedule = $request->filled('schedule')
            ? Schedule::whereSlug($request->schedule)->first()
            : Schedule::query()->orderBy('id')->first();
        if ($schedule) {
            $employee->schedules()->attach($schedule);

            if (($schedule->schedule_type ?? 'fixed') === 'shifting') {
                $patternRaw = trim((string) $request->input('rotation_pattern', ''));
                $startDate = $request->input('rotation_start_date');
                if ($patternRaw !== '' && $startDate) {
                    $codes = array_values(array_filter(array_map(function ($c) {
                        return strtoupper(trim((string) $c));
                    }, preg_split('/[,\s]+/', $patternRaw))));
                    if (count($codes)) {
                        EmployeeShiftRotation::updateOrCreate(
                            ['emp_id' => $employee->id, 'schedule_id' => $schedule->id],
                            ['start_date' => $startDate, 'pattern_json' => json_encode($codes)]
                        );
                    }
                }
            }
        }

        if ($request->password) {
            $user = new User();
            $user->name = $request->name;
            $user->email = $request->email;
            $user->password = bcrypt($request->password);
            $user->save();
            $this->applyPortalRoleToUser(
                $user,
                (string) $request->input('portal_role', 'employee'),
                $request->input('schedule_department_key') ?: null
            );
        }

        // $role = Role::whereSlug('emp')->first();

        // $employee->roles()->attach($role);

        flash()->success('Success','Employee Record has been created successfully !');

        return redirect()->route('employees.index')->with('success');
    }

 
    public function update(EmployeeRec $request, Employee $employee)
    {
        $request->validated();
        $oldEmail = $employee->email;

        $employee->name = $request->name;
        $employee->position = $request->position;
        $employee->email = $request->email;
        $deptName = $request->department_id ? Department::find($request->department_id)?->name : null;

        $employee->phone = $request->phone;
        $employee->pin_code = bcrypt($request->pin_code);
        $employee->department_id = $request->department_id;
        $employee->department = $deptName;
        $employee->schedule_department_key = $request->input('schedule_department_key') ?: null;
        $this->fillProfileDetails($employee, $request);
        
        // Face recognition data
        if ($request->face_descriptor) {
            $employee->face_descriptor = $request->face_descriptor;
            $employee->face_registered = true;
        }
        if ($request->face_image) {
            $employee->face_image = $request->face_image;
        }
        
        $employee->save();

        if ($request->schedule) {

            $employee->schedules()->detach();

            $schedule = Schedule::whereSlug($request->schedule)->first();

            $employee->schedules()->attach($schedule);

            if (($schedule->schedule_type ?? 'fixed') === 'shifting') {
                $patternRaw = trim((string) $request->input('rotation_pattern', ''));
                $startDate = $request->input('rotation_start_date');
                if ($patternRaw !== '' && $startDate) {
                    $codes = array_values(array_filter(array_map(function ($c) {
                        return strtoupper(trim((string) $c));
                    }, preg_split('/[,\s]+/', $patternRaw))));
                    if (count($codes)) {
                        EmployeeShiftRotation::updateOrCreate(
                            ['emp_id' => $employee->id, 'schedule_id' => $schedule->id],
                            ['start_date' => $startDate, 'pattern_json' => json_encode($codes)]
                        );
                    }
                } else {
                    EmployeeShiftRotation::where('emp_id', $employee->id)
                        ->where('schedule_id', $schedule->id)
                        ->delete();
                }
            } else {
                EmployeeShiftRotation::where('emp_id', $employee->id)->delete();
            }
        }

        $user = User::where('email', $oldEmail)->first();
        if (!$user && $request->email) {
            $user = User::where('email', $request->email)->first();
        }
        if (!$user && $request->filled('email') && $request->filled('password')) {
            $user = new User();
            $user->name = $request->name;
            $user->email = $request->email;
            $user->password = bcrypt($request->password);
            $user->save();
        } elseif ($user) {
            $user->name = $request->name;
            $user->email = $request->email;
            if ($request->filled('password')) {
                $user->password = bcrypt($request->password);
            }
            $user->save();
        }

        if ($user) {
            $this->applyPortalRoleToUser(
                $user,
                (string) $request->input('portal_role', 'employee'),
                $request->input('schedule_department_key') ?: null
            );
        }

        flash()->success('Success','Employee Record has been Updated successfully !');

        return redirect()->route('employees.index')->with('success');
    }

    public function profile($id)
    {
        $employee = Employee::with(['department', 'schedules', 'shiftRotation'])->findOrFail($id);

        return view('admin.employee-profile', [
            'employee' => $employee,
            'schedule' => $employee->schedules->first(),
        ]);
    }


    public function destroy(Employee $employee)
    {
        $employee->delete();
        flash()->success('Success','Employee Record has been Deleted successfully !');
        return redirect()->route('employees.index')->with('success');
    }

    private function ensurePortalRolesExist(): void
    {
        Role::firstOrCreate(['slug' => 'employee'], ['name' => 'Employee']);
        Role::firstOrCreate(['slug' => 'secretary'], ['name' => 'Department Secretary']);
    }

    private function applyPortalRoleToUser(User $user, string $portalRole, ?string $scheduleDeptKey): void
    {
        $this->ensurePortalRolesExist();
        $user->loadMissing('roles');
        $slugs = $user->roles->pluck('slug')->all();
        $onlyEmployeeOrSecretary = empty($slugs)
            || collect($slugs)->every(fn ($slug) => in_array($slug, ['employee', 'secretary'], true));
        if (!$onlyEmployeeOrSecretary) {
            return;
        }

        $targetSlug = $portalRole === 'secretary' ? 'secretary' : 'employee';
        $role = Role::where('slug', $targetSlug)->first();
        if (!$role) {
            return;
        }

        if ($targetSlug === 'secretary' && $scheduleDeptKey) {
            $user->managed_schedule_department = strtoupper($scheduleDeptKey);
        } else {
            $user->managed_schedule_department = null;
        }

        $user->save();
        $user->roles()->sync([$role->id]);
    }
}
