<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\OvertimeAuthorizationRequest;
use App\Models\User;
use App\Notifications\PendingRequestNotification;
use App\Notifications\RequestDecisionNotification;
use App\Services\EmployeeRequestFormService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class OvertimeAuthorizationController extends Controller
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
        $template = $this->employeeRequestFormService->getTemplate('overtime_authorization_request');
        if ($template && !$template->is_active) {
            return redirect()->route('employee.dashboard')->with('error', 'Overtime Authorization form is currently unavailable.');
        }

        if (!auth()->check() || !auth()->user()->employee) {
            return redirect()->route('employee.dashboard')->with('error', 'Employee profile not found. Please contact administrator.');
        }

        $currentEmployee = auth()->user()->employee;
        $isEmployee = true;
        $history = OvertimeAuthorizationRequest::where('emp_id', $currentEmployee->id)->orderByDesc('created_at')->get();
        $formUi = $this->employeeRequestFormService->getMergedUiSettings($template, 'overtime_authorization_request');

        return view('overtime_authorization.request', compact('currentEmployee', 'isEmployee', 'history', 'formUi'));
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
            'entries.*.ot_from' => 'required|date_format:H:i',
            'entries.*.ot_to' => 'required|date_format:H:i',
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
                'ot_from' => $row['ot_from'],
                'ot_to' => $row['ot_to'],
                'total_hours' => $row['total_hours'],
                'reason' => $row['reason'],
            ];
        }

        $req = OvertimeAuthorizationRequest::create([
            'emp_id' => $employee->id,
            'date_filed' => now()->toDateString(),
            'employee_name' => $employee->name,
            'employee_position' => $employee->position ?? '',
            'employee_department' => $employee->department ?? '',
            'entries' => $rows,
            'status' => OvertimeAuthorizationRequest::STATUS_PENDING,
        ]);

        $adminUsers = User::query()->whereHas('roles', function ($q) {
            $q->where('slug', 'admin');
        })->get();
        $adminUrl = route('overtime_authorization.admin');
        foreach ($adminUsers as $admin) {
            $admin->notify(new PendingRequestNotification(
                'overtime',
                (int) $req->id,
                $employee->name ?? ('Employee #' . $employee->id),
                (string) $req->created_at,
                $adminUrl
            ));
        }

        return redirect()->route('employee.dashboard')->with('success', 'Overtime authorization form submitted successfully.');
    }

    public function adminIndex()
    {
        $requests = OvertimeAuthorizationRequest::with('employee')->orderByDesc('created_at')->get();

        return view('admin.overtime-authorization', compact('requests'));
    }

    public function show($id)
    {
        $requestItem = OvertimeAuthorizationRequest::with('employee', 'reviewer')->findOrFail($id);
        $this->authorizeViewer($requestItem);

        return view('overtime_authorization.show', compact('requestItem'));
    }

    public function approve(Request $request, $id)
    {
        $request->validate([
            'remarks' => 'nullable|string|max:2000',
        ]);

        $item = OvertimeAuthorizationRequest::findOrFail($id);
        $item->status = OvertimeAuthorizationRequest::STATUS_APPROVED;
        $item->reviewed_by = auth()->user()->employee->id ?? 1;
        $item->reviewed_at = now();
        $item->remarks = $request->remarks ?? '';
        $item->save();

        $employee = $item->employee;
        $user = $employee ? User::where('email', $employee->email)->first() : null;
        if ($user) {
            $user->notify(new RequestDecisionNotification(
                'overtime',
                (int) $item->id,
                'approved',
                (string) ($item->remarks ?? ''),
                (string) ($item->reviewed_at ?? now()),
                route('employee.dashboard')
            ));
        }

        return redirect()->route('overtime_authorization.approvalLetterPdf', $item->id);
    }

    public function reject(Request $request, $id)
    {
        $request->validate([
            'remarks' => 'required|string|max:2000',
        ]);

        $item = OvertimeAuthorizationRequest::findOrFail($id);
        $item->status = OvertimeAuthorizationRequest::STATUS_REJECTED;
        $item->reviewed_by = auth()->user()->employee->id ?? 1;
        $item->reviewed_at = now();
        $item->remarks = $request->remarks;
        $item->save();

        $employee = $item->employee;
        $user = $employee ? User::where('email', $employee->email)->first() : null;
        if ($user) {
            $user->notify(new RequestDecisionNotification(
                'overtime',
                (int) $item->id,
                'rejected',
                (string) ($item->remarks ?? ''),
                (string) ($item->reviewed_at ?? now()),
                route('employee.dashboard')
            ));
        }

        return redirect()->route('overtime_authorization.admin')->with('error', 'Overtime authorization rejected.');
    }

    public function destroy($id)
    {
        $item = OvertimeAuthorizationRequest::findOrFail($id);
        $item->delete();

        return redirect()->route('overtime_authorization.admin')->with('error', 'Overtime authorization deleted!');
    }

    public function generateApprovalLetterPdf($id)
    {
        $requestItem = OvertimeAuthorizationRequest::with('employee', 'reviewer')->findOrFail($id);
        if ((int) $requestItem->status !== OvertimeAuthorizationRequest::STATUS_APPROVED) {
            return redirect()->route('overtime_authorization.admin')->with('error', 'Overtime request must be approved before generating the PDF.');
        }

        $pdf = Pdf::loadView('overtime_authorization.approvalLetterPdf', compact('requestItem'))
            ->setPaper('letter', 'portrait');

        return $pdf->download('overtime-authorization-' . $requestItem->id . '.pdf');
    }

    private function authorizeViewer(OvertimeAuthorizationRequest $requestItem): void
    {
        $user = auth()->user();
        $isAdmin = $user && method_exists($user, 'hasRole') && $user->hasRole('admin');
        $isEmployee = $user && method_exists($user, 'hasRole') && $user->hasRole('employee');

        if ($isAdmin) {
            return;
        }
        if ($isEmployee && $user->employee && (int) $user->employee->id === (int) $requestItem->emp_id) {
            return;
        }

        abort(403);
    }
}

