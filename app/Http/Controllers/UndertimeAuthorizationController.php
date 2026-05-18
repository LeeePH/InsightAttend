<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\UndertimeAuthorizationRequest;
use App\Models\User;
use App\Notifications\PendingRequestNotification;
use App\Notifications\RequestDecisionNotification;
use App\Services\EmployeeRequestFormService;
use App\Services\SchedulingDepartmentService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class UndertimeAuthorizationController extends Controller
{
    /** @var EmployeeRequestFormService */
    private $employeeRequestFormService;

    public function __construct(EmployeeRequestFormService $employeeRequestFormService)
    {
        $this->employeeRequestFormService = $employeeRequestFormService;
    }

    public function requestForm()
    {
        $this->employeeRequestFormService->ensureDefaults();
        $template = $this->employeeRequestFormService->getTemplate('undertime_authorization_request');
        if ($template && !$template->is_active) {
            return redirect()->route('employee.dashboard')->with('error', 'Undertime Authorization form is currently unavailable.');
        }

        if (!auth()->check() || !auth()->user()->employee) {
            return redirect()->route('employee.dashboard')->with('error', 'Employee profile not found. Please contact administrator.');
        }

        $currentEmployee = auth()->user()->employee;
        $isEmployee = true;
        $history = UndertimeAuthorizationRequest::where('emp_id', $currentEmployee->id)->orderByDesc('created_at')->get();
        $formUi = $this->employeeRequestFormService->getMergedUiSettings($template, 'undertime_authorization_request');

        return view('undertime_authorization.request', compact('currentEmployee', 'isEmployee', 'history', 'formUi'));
    }

    public function storeRequest(Request $request)
    {
        if (!auth()->check() || !auth()->user()->employee) {
            return back()->with('error', 'Employee profile not found. Please contact administrator.');
        }

        $rules = [
            'entries' => 'required|array|min:1',
            'entries.*.date' => 'required|date',
            'entries.*.work_from' => 'required|date_format:H:i',
            'entries.*.work_to' => 'required|date_format:H:i',
            'entries.*.ut_from' => 'required|date_format:H:i',
            'entries.*.ut_to' => 'required|date_format:H:i',
            'entries.*.total_hours' => 'required|numeric|min:0|max:24',
            'entries.*.reason' => 'required|string|max:500',
        ];
        $validated = $request->validate($rules);

        /** @var Employee $employee */
        $employee = auth()->user()->employee;

        $rows = [];
        foreach ($validated['entries'] as $row) {
            $rows[] = [
                'date' => $row['date'],
                'work_from' => $row['work_from'],
                'work_to' => $row['work_to'],
                'ut_from' => $row['ut_from'],
                'ut_to' => $row['ut_to'],
                'total_hours' => $row['total_hours'],
                'reason' => $row['reason'],
            ];
        }

        $req = UndertimeAuthorizationRequest::create([
            'emp_id' => $employee->id,
            'date_filed' => now()->toDateString(),
            'employee_name' => $employee->name,
            'employee_position' => $employee->position ?? '',
            'employee_department' => $employee->department ?? '',
            'entries' => $rows,
            'status' => UndertimeAuthorizationRequest::STATUS_PENDING,
        ]);

        $adminUsers = User::query()->whereHas('roles', function ($q) {
            $q->where('slug', 'admin');
        })->get();
        $adminUrl = route('undertime_authorization.admin');
        foreach ($adminUsers as $admin) {
            $admin->notify(new PendingRequestNotification(
                'undertime',
                (int) $req->id,
                $employee->name ?? ('Employee #' . $employee->id),
                (string) $req->created_at,
                $adminUrl
            ));
        }

        $employeeDepartmentKey = SchedulingDepartmentService::resolveEmployeeDepartmentKey($employee);
        if (SchedulingDepartmentService::isValidKey($employeeDepartmentKey)) {
            $secretaries = User::query()
                ->where('managed_schedule_department', strtoupper((string) $employeeDepartmentKey))
                ->whereHas('roles', function ($q) {
                    $q->where('slug', 'secretary');
                })
                ->get();

            foreach ($secretaries as $secretary) {
                $secretary->notify(new PendingRequestNotification(
                    'undertime',
                    (int) $req->id,
                    $employee->name ?? ('Employee #' . $employee->id),
                    (string) $req->created_at,
                    $adminUrl
                ));
            }
        }

        return redirect()->route('employee.dashboard')->with('success', 'Undertime authorization form submitted successfully.');
    }

    public function adminIndex()
    {
        $user = auth()->user();
        $requests = UndertimeAuthorizationRequest::with('employee')->orderByDesc('created_at')->get();

        if ($user && method_exists($user, 'hasRole') && $user->hasRole('secretary')) {
            $managedDepartment = strtoupper((string) $user->managed_schedule_department);
            abort_unless(
                SchedulingDepartmentService::isValidKey($managedDepartment),
                403,
                'Your account must have a managed department before you can review undertime requests.'
            );

            $requests = $requests->filter(function (UndertimeAuthorizationRequest $requestItem) use ($managedDepartment) {
                return $this->requestDepartmentMatches($requestItem, $managedDepartment);
            })->values();
        }

        return view('admin.undertime-authorization', compact('requests'));
    }

    public function show($id)
    {
        $requestItem = UndertimeAuthorizationRequest::with('employee', 'reviewer')->findOrFail($id);
        $this->authorizeViewer($requestItem);

        return view('undertime_authorization.show', compact('requestItem'));
    }

    public function approve(Request $request, $id)
    {
        $request->validate([
            'remarks' => 'nullable|string|max:2000',
        ]);

        $item = UndertimeAuthorizationRequest::findOrFail($id);
        $item->status = UndertimeAuthorizationRequest::STATUS_APPROVED;
        $item->reviewed_by = auth()->user()->employee->id ?? 1;
        $item->reviewed_at = now();
        $item->remarks = $request->remarks ?? '';
        $item->save();

        $employee = $item->employee;
        $user = $employee ? User::where('email', $employee->email)->first() : null;
        if ($user) {
            $user->notify(new RequestDecisionNotification(
                'undertime',
                (int) $item->id,
                'approved',
                (string) ($item->remarks ?? ''),
                (string) ($item->reviewed_at ?? now()),
                route('employee.dashboard')
            ));
        }

        return redirect()->route('undertime_authorization.approvalLetterPdf', $item->id);
    }

    public function reject(Request $request, $id)
    {
        $request->validate([
            'remarks' => 'required|string|max:2000',
        ]);

        $item = UndertimeAuthorizationRequest::findOrFail($id);
        $item->status = UndertimeAuthorizationRequest::STATUS_REJECTED;
        $item->reviewed_by = auth()->user()->employee->id ?? 1;
        $item->reviewed_at = now();
        $item->remarks = $request->remarks;
        $item->save();

        $employee = $item->employee;
        $user = $employee ? User::where('email', $employee->email)->first() : null;
        if ($user) {
            $user->notify(new RequestDecisionNotification(
                'undertime',
                (int) $item->id,
                'rejected',
                (string) ($item->remarks ?? ''),
                (string) ($item->reviewed_at ?? now()),
                route('employee.dashboard')
            ));
        }

        return redirect()->route('undertime_authorization.admin')->with('error', 'Undertime authorization rejected.');
    }

    public function destroy($id)
    {
        $item = UndertimeAuthorizationRequest::findOrFail($id);
        $item->delete();

        return redirect()->route('undertime_authorization.admin')->with('error', 'Undertime authorization deleted!');
    }

    public function generateApprovalLetterPdf($id)
    {
        $requestItem = UndertimeAuthorizationRequest::with('employee', 'reviewer')->findOrFail($id);
        if ((int) $requestItem->status !== UndertimeAuthorizationRequest::STATUS_APPROVED) {
            return redirect()->route('undertime_authorization.admin')->with('error', 'Undertime request must be approved before generating the PDF.');
        }

        $pdf = Pdf::loadView('undertime_authorization.approvalLetterPdf', compact('requestItem'))
            ->setPaper('letter', 'portrait');

        return $pdf->download('undertime-authorization-' . $requestItem->id . '.pdf');
    }

    private function authorizeViewer(UndertimeAuthorizationRequest $requestItem): void
    {
        $user = auth()->user();
        $isAdmin = $user && method_exists($user, 'hasRole') && $user->hasRole('admin');
        $isEmployee = $user && method_exists($user, 'hasRole') && $user->hasAnyRole(['employee', 'secretary']);

        if ($isAdmin) {
            return;
        }
        if ($isEmployee && $user->employee && (int) $user->employee->id === (int) $requestItem->emp_id) {
            return;
        }
        if ($user && method_exists($user, 'hasRole') && $user->hasRole('secretary')) {
            $managedDepartment = strtoupper((string) $user->managed_schedule_department);
            if ($this->requestDepartmentMatches($requestItem, $managedDepartment)) {
                return;
            }
        }

        abort(403);
    }

    private function requestDepartmentMatches(UndertimeAuthorizationRequest $requestItem, ?string $departmentKey): bool
    {
        if (!SchedulingDepartmentService::isValidKey($departmentKey)) {
            return false;
        }

        $employee = $requestItem->employee;
        $requestDept = $employee
            ? SchedulingDepartmentService::resolveEmployeeDepartmentKey($employee)
            : SchedulingDepartmentService::inferKeyFromText($requestItem->employee_department);

        return strtoupper((string) $requestDept) === strtoupper((string) $departmentKey);
    }
}
