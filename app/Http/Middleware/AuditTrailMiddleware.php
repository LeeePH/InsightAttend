<?php

namespace App\Http\Middleware;

use App\Models\AuditLog;
use Closure;
use Illuminate\Support\Facades\Schema;

class AuditTrailMiddleware
{
    private static $auditTableExists = null;
    private const EXCLUDED_ROUTES = [
        'admin.audit_logs',
        'admin.backups.reset_database',
        'admin.backups.delete_database',
        'notifications.read',
        'notifications.read_all',
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $response = $next($request);

        $routeName = optional($request->route())->getName();
        $action = optional($request->route())->getActionName();

        if (!$this->shouldLog($request, $routeName, $action)) {
            return $response;
        }

        $user = auth()->user();
        $firstRole = $user ? $user->roles()->first() : null;
        $role = $firstRole ? $firstRole->slug : null;

        $safeInput = collect($request->except([
            '_token',
            '_method',
            'password',
            'password_confirmation',
            'pin_code',
            'face_descriptor',
            'face_image',
        ]))->toArray();

        AuditLog::create([
            'user_id' => $user ? $user->id : null,
            'user_name' => $user ? $user->name : null,
            'role_slug' => $role,
            'action' => $action,
            'route_name' => $routeName,
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'status_code' => method_exists($response, 'getStatusCode') ? $response->getStatusCode() : null,
            'description' => $this->makeDescription($request, $routeName, $action),
            'metadata' => $safeInput,
        ]);

        return $response;
    }

    private function shouldLog($request, ?string $routeName, ?string $action): bool
    {
        if (!auth()->check()) {
            return false;
        }

        if (self::$auditTableExists === null) {
            try {
                self::$auditTableExists = Schema::hasTable('audit_logs');
            } catch (\Throwable $e) {
                self::$auditTableExists = false;
            }
        }

        if (!self::$auditTableExists) {
            return false;
        }

        $path = $request->path();

        if (strpos($path, 'admin/audit-logs') === 0) {
            return false;
        }

        if (strpos($path, 'admin/backups/reset') === 0 || strpos($path, 'admin/backups/delete') === 0) {
            return false;
        }

        if ($routeName && strpos($routeName, 'ignition.') === 0) {
            return false;
        }

        if ($routeName && in_array($routeName, self::EXCLUDED_ROUTES, true)) {
            return false;
        }

        if (!in_array(strtoupper($request->method()), ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            return false;
        }

        return $this->isMeaningfulAction($routeName, $action);
    }

    private function isMeaningfulAction(?string $routeName, ?string $action): bool
    {
        $signature = strtolower(trim(($routeName ?? '') . ' ' . ($action ?? '')));

        if ($signature === '') {
            return false;
        }

        foreach ([
            'storerequest',
            'store',
            'update',
            'destroy',
            'delete',
            'approve',
            'reject',
            'restore',
            'reset',
            'upload',
            'timeout',
            'timein',
            'password',
            'profile',
            'checkstore',
        ] as $keyword) {
            if (strpos($signature, $keyword) !== false) {
                return true;
            }
        }

        return false;
    }

    private function makeDescription($request, ?string $routeName, ?string $action): string
    {
        $operation = $this->resolveOperation($request, $routeName, $action);
        $subject = $this->resolveSubject($routeName, $action);

        return trim(ucfirst($operation . ' ' . $subject));
    }

    private function resolveOperation($request, ?string $routeName, ?string $action): string
    {
        $signature = strtolower(trim(($routeName ?? '') . ' ' . ($action ?? '')));

        if (strpos($signature, 'storerequest') !== false) {
            return 'submitted';
        }

        if (strpos($signature, 'approve') !== false) {
            return 'approved';
        }

        if (strpos($signature, 'reject') !== false) {
            return 'rejected';
        }

        if (strpos($signature, 'restore') !== false) {
            return 'restored';
        }

        if (strpos($signature, 'reset') !== false) {
            return 'reset';
        }

        if (strpos($signature, 'upload') !== false) {
            return 'uploaded';
        }

        if (strpos($signature, 'checkstore') !== false) {
            return 'saved';
        }

        if (strpos($signature, 'timein') !== false) {
            return 'recorded';
        }

        if (strpos($signature, 'timeout') !== false) {
            return 'recorded';
        }

        if (strpos($signature, 'destroy') !== false || strtoupper($request->method()) === 'DELETE') {
            return 'deleted';
        }

        if (strpos($signature, 'update') !== false || in_array(strtoupper($request->method()), ['PUT', 'PATCH'], true)) {
            return 'updated';
        }

        return 'created';
    }

    private function resolveSubject(?string $routeName, ?string $action): string
    {
        $signature = strtolower(trim(($routeName ?? '') . ' ' . ($action ?? '')));

        $subjects = [
            'employee.settings.password' => 'password',
            'employee.password.update' => 'password',
            'employee.settings.profile' => 'profile',
            'admin.maintenance_form.templates' => 'maintenance form template',
            'admin.maintenance_form.fields' => 'maintenance form field',
            'admin.user_management' => 'user account',
            'admin.backups.delete_database' => 'database',
            'admin.backups.reset_database' => 'database',
            'admin.backups' => 'backup',
            'permit_to_teach_outside' => 'permit to teach outside request',
            'overtime_authorization' => 'overtime authorization request',
            'undertime_authorization' => 'undertime authorization request',
            'substitution' => 'substitution request',
            'subsitution' => 'substitution request',
            'resignation' => 'resignation request',
            'discount' => 'discount request',
            'leave' => 'leave request',
            'loan' => 'loan request',
            'departments' => 'department',
            'employees' => 'employee',
            'schedule.shifts' => 'schedule shift',
            'schedule' => 'schedule',
            'attendance' => 'attendance record',
            'check' => 'attendance check',
            'timein' => 'time in',
            'timeout' => 'time out',
            'maintenance' => 'maintenance record',
        ];

        foreach ($subjects as $needle => $label) {
            if (strpos($signature, $needle) !== false) {
                return $label;
            }
        }

        return 'record';
    }
}
