<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    public function index(Request $request): View
    {
        $query = AuditLog::query()->orderByDesc('created_at');

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->input('user_id'));
        }

        if ($request->filled('role')) {
            $query->where('role_slug', $request->input('role'));
        }

        if ($request->filled('q')) {
            $search = $request->input('q');
            $query->where(function ($q) use ($search) {
                $q->where('user_name', 'like', '%' . $search . '%')
                    ->orWhere('route_name', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%');
            });
        }

        $logs = $query->paginate(25)->appends($request->query());
        $users = User::orderBy('name')->get(['id', 'name']);
        $roles = AuditLog::query()->select('role_slug')->whereNotNull('role_slug')->distinct()->pluck('role_slug');

        return view('admin.audit-logs', compact('logs', 'users', 'roles'));
    }
}
