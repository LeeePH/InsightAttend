<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\PermitToTeachOutsideRequest;
use App\Models\User;
use App\Notifications\PendingRequestNotification;
use App\Notifications\RequestDecisionNotification;
use App\Services\EmployeeRequestFormService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class PermitToTeachOutsideController extends Controller
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
        $template = $this->employeeRequestFormService->getTemplate('permit_to_teach_outside_request');
        if ($template && !$template->is_active) {
            return redirect()->route('employee.dashboard')->with('error', 'Permit to Teach (Outside School) form is currently unavailable.');
        }

        if (!auth()->check() || !auth()->user()->employee) {
            return redirect()->route('employee.dashboard')->with('error', 'Employee profile not found. Please contact administrator.');
        }

        $currentEmployee = auth()->user()->employee;
        $isEmployee = true;
        $history = PermitToTeachOutsideRequest::where('emp_id', $currentEmployee->id)->orderByDesc('created_at')->get();
        $formUi = $this->employeeRequestFormService->getMergedUiSettings($template, 'permit_to_teach_outside_request');

        return view('permit_to_teach_outside.request', compact('currentEmployee', 'isEmployee', 'history', 'formUi'));
    }

    public function storeRequest(Request $request)
    {
        if (!auth()->check() || !auth()->user()->employee) {
            return back()->with('error', 'Employee profile not found. Please contact administrator.');
        }

        $rules = [
            'position_rank' => 'nullable|string|max:255',
            'department_school' => 'nullable|string|max:255',
            'employment_status' => 'required|in:full_time,part_time,probationary,regular',
            'other_school_name' => 'required|string|max:255',
            'other_school_address' => 'required|string|max:255',
            'institution_type' => 'required|in:public,private,review_center,others',
            'institution_type_others' => 'nullable|string|max:255',
            'subjects_to_teach' => 'required|string|max:255',
            'program_level' => 'required|in:basic_ed,senior_high,college,graduate',
            'units_or_hours_per_week' => 'nullable|string|max:100',
            'teaching_schedule' => 'nullable|string|max:2000',
            'engagement_from' => 'nullable|date',
            'engagement_to' => 'nullable|date|after_or_equal:engagement_from',
            'certification_confirmed' => 'required|accepted',
        ];
        $validated = $request->validate($rules);

        if ($validated['institution_type'] === 'others' && empty(trim((string) ($validated['institution_type_others'] ?? '')))) {
            return back()->withErrors(['institution_type_others' => 'Please specify the institution type.'])->withInput();
        }

        /** @var Employee $employee */
        $employee = auth()->user()->employee;

        $item = PermitToTeachOutsideRequest::create([
            'emp_id' => $employee->id,
            'position_rank' => $validated['position_rank'] ?? ($employee->position ?? null),
            'department_school' => $validated['department_school'] ?? ($employee->department ?? null),
            'employment_status' => $validated['employment_status'],
            'other_school_name' => $validated['other_school_name'],
            'other_school_address' => $validated['other_school_address'],
            'institution_type' => $validated['institution_type'],
            'institution_type_others' => $validated['institution_type_others'] ?? null,
            'subjects_to_teach' => $validated['subjects_to_teach'],
            'program_level' => $validated['program_level'],
            'units_or_hours_per_week' => $validated['units_or_hours_per_week'] ?? null,
            'teaching_schedule' => $validated['teaching_schedule'] ?? null,
            'engagement_from' => $validated['engagement_from'] ?? null,
            'engagement_to' => $validated['engagement_to'] ?? null,
            'certification_confirmed' => true,
            'status' => PermitToTeachOutsideRequest::STATUS_PENDING,
        ]);

        $adminUsers = User::query()->whereHas('roles', function ($q) {
            $q->where('slug', 'admin');
        })->get();
        $adminUrl = route('permit_to_teach_outside.admin');
        foreach ($adminUsers as $admin) {
            $admin->notify(new PendingRequestNotification(
                'permit to teach',
                (int) $item->id,
                $employee->name ?? ('Employee #' . $employee->id),
                (string) $item->created_at,
                $adminUrl
            ));
        }

        return redirect()->route('employee.dashboard')->with('success', 'Permit to Teach (Outside School) form submitted successfully.');
    }

    public function adminIndex()
    {
        $requests = PermitToTeachOutsideRequest::with('employee')->orderByDesc('created_at')->get();

        return view('admin.permit-to-teach-outside', compact('requests'));
    }

    public function show($id)
    {
        $requestItem = PermitToTeachOutsideRequest::with('employee', 'reviewer')->findOrFail($id);
        $this->authorizeViewer($requestItem);

        return view('permit_to_teach_outside.show', compact('requestItem'));
    }

    public function approve(Request $request, $id)
    {
        $request->validate([
            'remarks' => 'nullable|string|max:2000',
        ]);

        $item = PermitToTeachOutsideRequest::findOrFail($id);
        $item->status = PermitToTeachOutsideRequest::STATUS_APPROVED;
        $item->reviewed_by = auth()->user()->employee->id ?? 1;
        $item->reviewed_at = now();
        $item->remarks = $request->remarks ?? '';
        $item->save();

        $employee = $item->employee;
        $user = $employee ? User::where('email', $employee->email)->first() : null;
        if ($user) {
            $user->notify(new RequestDecisionNotification(
                'permit to teach',
                (int) $item->id,
                'approved',
                (string) ($item->remarks ?? ''),
                (string) ($item->reviewed_at ?? now()),
                route('employee.dashboard')
            ));
        }

        return redirect()->route('permit_to_teach_outside.approvalLetterPdf', $item->id);
    }

    public function reject(Request $request, $id)
    {
        $request->validate([
            'remarks' => 'required|string|max:2000',
        ]);

        $item = PermitToTeachOutsideRequest::findOrFail($id);
        $item->status = PermitToTeachOutsideRequest::STATUS_REJECTED;
        $item->reviewed_by = auth()->user()->employee->id ?? 1;
        $item->reviewed_at = now();
        $item->remarks = $request->remarks;
        $item->save();

        $employee = $item->employee;
        $user = $employee ? User::where('email', $employee->email)->first() : null;
        if ($user) {
            $user->notify(new RequestDecisionNotification(
                'permit to teach',
                (int) $item->id,
                'rejected',
                (string) ($item->remarks ?? ''),
                (string) ($item->reviewed_at ?? now()),
                route('employee.dashboard')
            ));
        }

        return redirect()->route('permit_to_teach_outside.admin')->with('error', 'Permit to Teach request rejected.');
    }

    public function destroy($id)
    {
        $item = PermitToTeachOutsideRequest::findOrFail($id);
        $item->delete();

        return redirect()->route('permit_to_teach_outside.admin')->with('error', 'Permit to Teach request deleted!');
    }

    public function generateApprovalLetterPdf($id)
    {
        $requestItem = PermitToTeachOutsideRequest::with('employee', 'reviewer')->findOrFail($id);
        if ((int) $requestItem->status !== PermitToTeachOutsideRequest::STATUS_APPROVED) {
            return redirect()->route('permit_to_teach_outside.admin')->with('error', 'Request must be approved before generating the PDF.');
        }

        $pdf = Pdf::loadView('permit_to_teach_outside.approvalLetterPdf', compact('requestItem'))
            ->setPaper('letter', 'portrait');

        return $pdf->download('permit-to-teach-outside-' . $requestItem->id . '.pdf');
    }

    private function authorizeViewer(PermitToTeachOutsideRequest $requestItem): void
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

        abort(403);
    }
}
