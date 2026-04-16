<?php

namespace App\Http\Middleware;

use App\Models\AuditLog;
use Closure;
use Illuminate\Support\Facades\Schema;

class AuditTrailMiddleware
{
    private static $auditTableExists = null;

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

        if (!$this->shouldLog($request)) {
            return $response;
        }

        $user = auth()->user();
        $firstRole = $user ? $user->roles()->first() : null;
        $role = $firstRole ? $firstRole->slug : null;
        $routeName = optional($request->route())->getName();
        $action = optional($request->route())->getActionName();

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
            'description' => $this->makeDescription($request, $routeName),
            'metadata' => $safeInput,
        ]);

        return $response;
    }

    private function shouldLog($request): bool
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
        $routeName = optional($request->route())->getName();

        if (strpos($path, 'admin/audit-logs') === 0) {
            return false;
        }

        if ($routeName && strpos($routeName, 'ignition.') === 0) {
            return false;
        }

        return true;
    }

    private function makeDescription($request, ?string $routeName): string
    {
        if ($routeName) {
            return strtoupper($request->method()) . ' ' . $routeName;
        }

        return strtoupper($request->method()) . ' ' . $request->path();
    }
}
