<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserManagementController extends Controller
{
    public function index(): View
    {
        $this->ensureBaseRoles();

        $users = User::with('roles')->orderBy('name')->get();
        $roles = Role::orderBy('name')->get();
        $roles->each(function ($role) {
            $role->display_label = $role->name;
        });
        $roleSummary = [
            'total' => $users->count(),
            'admin' => $users->filter(fn ($user) => $user->hasRole('admin'))->count(),
            'hr' => $users->filter(fn ($user) => $user->hasRole('hr'))->count(),
            'secretary' => $users->filter(fn ($user) => $user->hasRole('secretary'))->count(),
            'staff' => $users->filter(fn ($user) => $user->hasRole('staff'))->count(),
            'employee' => $users->filter(fn ($user) => $user->hasRole('employee'))->count(),
        ];

        return view('admin.user-management', compact('users', 'roles', 'roleSummary'));
    }

    public function update(Request $request, $id): RedirectResponse
    {
        $this->ensureBaseRoles();
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:6'],
            'role_id' => ['required', 'exists:roles,id'],
            'managed_schedule_department' => ['nullable', 'string', Rule::in(['IT', 'EDUC', 'SHTM'])],
        ]);

        $role = Role::findOrFail((int) $validated['role_id']);

        if ($role->slug === 'secretary' && empty($validated['managed_schedule_department'])) {
            return back()->withErrors([
                'managed_schedule_department' => 'Secretary accounts must have a managed department (IT, EDUC, or SHTM).',
            ])->withInput();
        }

        $user->name = $validated['name'];
        $user->email = $validated['email'];

        if ($role->slug === 'secretary') {
            $user->managed_schedule_department = strtoupper((string) $validated['managed_schedule_department']);
        } else {
            $user->managed_schedule_department = null;
        }

        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();
        $user->roles()->sync([$validated['role_id']]);

        flash()->success('Success', 'User account updated successfully.');

        return back();
    }

    private function ensureBaseRoles(): void
    {
        Role::firstOrCreate(['slug' => 'admin'], ['name' => 'Administrator']);
        Role::firstOrCreate(['slug' => 'staff'], ['name' => 'Staff']);
        Role::firstOrCreate(['slug' => 'employee'], ['name' => 'Employee']);
        Role::firstOrCreate(['slug' => 'hr'], ['name' => 'Human Resources']);
        Role::firstOrCreate(['slug' => 'secretary'], ['name' => 'Department Secretary']);
    }
}
