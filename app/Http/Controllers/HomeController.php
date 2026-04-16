<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

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
        
        return view('employee.dashboard', compact('employee', 'attendances', 'leaves'));
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

        return redirect()->route('employee.dashboard')->with('success', 'Your password has been updated.');
    }
}
