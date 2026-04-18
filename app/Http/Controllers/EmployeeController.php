<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Employee;
use App\Models\Role;
use App\Models\Schedule;
use App\Models\Department;
use App\Http\Requests\EmployeeRec;
use RealRashid\SweetAlert\Facades\Alert;
use App\Models\EmployeeShiftRotation;

class EmployeeController extends Controller
{
   
    public function index()
    {
        
        return view('admin.employee')->with([
            'employees' => Employee::with('department')->get(),
            'schedules' => Schedule::all(),
            'departments' => Department::query()->where('is_active', 1)->orderBy('name')->get(),
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
        
        // Face recognition data
        if ($request->face_descriptor) {
            $employee->face_descriptor = $request->face_descriptor;
            $employee->face_registered = true;
        }
        if ($request->face_image) {
            $employee->face_image = $request->face_image;
        }
        
        $employee->save();

        if($request->schedule){

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
                }
            }
        }

        // Create user account with login credentials if provided
        if ($request->password) {
            $user = new User();
            $user->name = $request->name;
            $user->email = $request->email;
            $user->password = bcrypt($request->password);
            $user->save();
            
            // Assign employee role to user
            $employeeRole = Role::where('slug', 'employee')->first();
            if ($employeeRole) {
                $user->roles()->attach($employeeRole);
            }
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

        // Sync linked user account by email (if one exists)
        $user = User::where('email', $oldEmail)->first();
        if (!$user && $request->email) {
            $user = User::where('email', $request->email)->first();
        }
        if ($user) {
            $user->name = $request->name;
            $user->email = $request->email;
            if ($request->filled('password')) {
                $user->password = bcrypt($request->password);
            }
            $user->save();
        }

        flash()->success('Success','Employee Record has been Updated successfully !');

        return redirect()->route('employees.index')->with('success');
    }


    public function destroy(Employee $employee)
    {
        $employee->delete();
        flash()->success('Success','Employee Record has been Deleted successfully !');
        return redirect()->route('employees.index')->with('success');
    }
}
