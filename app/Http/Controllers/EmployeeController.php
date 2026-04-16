<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Employee;
use App\Models\Role;
use App\Models\Schedule;
use App\Http\Requests\EmployeeRec;
use RealRashid\SweetAlert\Facades\Alert;

class EmployeeController extends Controller
{
   
    public function index()
    {
        
        return view('admin.employee')->with(['employees'=> Employee::all(), 'schedules'=>Schedule::all()]);
    }

    public function store(EmployeeRec $request)
    {
        $request->validated();

        $employee = new Employee;
        $employee->name = $request->name;
        $employee->position = $request->position;
        $employee->email = $request->email;
        $employee->pin_code = bcrypt($request->pin_code);
        $employee->department = $request->department;
        
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
        $employee->pin_code = bcrypt($request->pin_code);
        
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
