<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserManagementController extends Controller
{
    public function index(): View
    {
        $this->ensureBaseRoles();

        $users = User::with('roles')->orderBy('name')->get();
        $roles = Role::orderBy('name')->get();
        $roleSummary = [
            'total' => $users->count(),
            'admin' => $users->filter(function ($user) {
                return optional($user->roles->first())->slug === 'admin';
            })->count(),
            'staff' => $users->filter(function ($user) {
                return optional($user->roles->first())->slug === 'staff';
            })->count(),
            'employee' => $users->filter(function ($user) {
                return optional($user->roles->first())->slug === 'employee';
            })->count(),
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
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];

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
    }
}
