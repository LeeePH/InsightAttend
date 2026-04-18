<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\ResignationRequest;
use App\Services\EmployeeRequestFormService;
use Illuminate\Http\Request;
use App\Models\User;
use App\Notifications\PendingRequestNotification;
use App\Notifications\RequestDecisionNotification;

class ResignationController extends Controller
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
        $template = $this->employeeRequestFormService->getTemplate('resignation_request');
        if ($template && !$template->is_active) {
            return redirect()->route('employee.dashboard')->with('error', 'Resignation request form is currently unavailable.');
        }

        $currentEmployee = null;
        $isEmployee = false;

        if (auth()->check() && auth()->user()->employee) {
            $currentEmployee = auth()->user()->employee;
            $isEmployee = true;
        }

        $formConfig = [
            'title' => $template ? $template->name : 'Resignation Request',
            'subtitle' => $template && $template->description ? $template->description : 'Submit your formal resignation request and transition details.',
            'labels' => [
                'last_working_day' => $this->employeeRequestFormService->fieldLabel($template, 'last_working_day', 'Intended Last Working Day'),
                'reason' => $this->employeeRequestFormService->fieldLabel($template, 'reason', 'Reason for Resignation'),
                'handover_notes' => $this->employeeRequestFormService->fieldLabel($template, 'handover_notes', 'Handover Notes (Optional)'),
                'acknowledgement' => $this->employeeRequestFormService->fieldLabel($template, 'acknowledgement', 'I confirm that the details provided are true and I understand this will be submitted for review.'),
            ],
            'required' => [
                'last_working_day' => $this->employeeRequestFormService->fieldRequired($template, 'last_working_day', true),
                'reason' => $this->employeeRequestFormService->fieldRequired($template, 'reason', true),
                'handover_notes' => $this->employeeRequestFormService->fieldRequired($template, 'handover_notes', false),
                'acknowledgement' => $this->employeeRequestFormService->fieldRequired($template, 'acknowledgement', true),
            ],
        ];

        $formUi = $this->employeeRequestFormService->getMergedUiSettings($template, 'resignation_request');
        $fieldUi = $this->employeeRequestFormService->fieldUiMap($template, 'resignation_request');

        return view('resignation.request')->with([
            'currentEmployee' => $currentEmployee,
            'isEmployee' => $isEmployee,
            'formConfig' => $formConfig,
            'formUi' => $formUi,
            'fieldUi' => $fieldUi,
        ]);
    }

    public function storeRequest(Request $request)
    {
        $this->employeeRequestFormService->ensureDefaults();
        $template = $this->employeeRequestFormService->getTemplate('resignation_request');
        if ($template && !$template->is_active) {
            return redirect()->route('employee.dashboard')->with('error', 'Resignation request form is currently unavailable.');
        }

        $rules = $this->employeeRequestFormService->applyTemplateRules('resignation_request', [
            'last_working_day' => 'required|date|after_or_equal:today',
            'reason' => 'required|string|max:2000',
            'handover_notes' => 'nullable|string|max:2000',
            'acknowledgement' => 'required|accepted',
        ]);

        $request->validate($rules);

        if (!auth()->check() || !auth()->user()->employee) {
            return redirect()->back()->with('error', 'Employee profile not found. Please contact administrator.');
        }

        /** @var Employee $employee */
        $employee = auth()->user()->employee;

        $req = ResignationRequest::create([
            'emp_id' => $employee->id,
            'last_working_day' => $request->last_working_day,
            'reason' => $request->reason,
            'handover_notes' => $request->handover_notes,
            'status' => ResignationRequest::STATUS_PENDING,
        ]);

        // Notify admins about pending resignation request
        $adminUsers = User::query()
            ->whereHas('roles', function ($q) {
                $q->where('slug', 'admin');
            })
            ->get();
        $adminUrl = route('resignation.admin');
        foreach ($adminUsers as $admin) {
            $admin->notify(new PendingRequestNotification(
                'resignation',
                (int) $req->id,
                $employee->name ?? ('Employee #' . $employee->id),
                (string) $req->created_at,
                $adminUrl
            ));
        }

        return redirect()->route('employee.dashboard')->with('success', 'Your resignation request has been submitted successfully.');
    }

    public function adminIndex()
    {
        $requests = ResignationRequest::with('employee')->orderBy('created_at', 'desc')->get();
        return view('admin.resignation', compact('requests'));
    }

    public function approve(Request $request, $id)
    {
        $requestItem = ResignationRequest::findOrFail($id);
        $requestItem->status = ResignationRequest::STATUS_APPROVED;
        $requestItem->reviewed_by = auth()->user()->employee->id ?? 1;
        $requestItem->reviewed_at = now();
        $requestItem->remarks = $request->remarks ?? '';
        $requestItem->save();

        // Notify employee about approval decision
        $employee = $requestItem->employee;
        $user = $employee ? User::where('email', $employee->email)->first() : null;
        if ($user) {
            $user->notify(new RequestDecisionNotification(
                'resignation',
                (int) $requestItem->id,
                'approved',
                (string) ($requestItem->remarks ?? ''),
                (string) ($requestItem->reviewed_at ?? now()),
                route('employee.dashboard')
            ));
        }

        return redirect()->route('resignation.approvalLetter', $requestItem->id);
    }

    public function reject(Request $request, $id)
    {
        $requestItem = ResignationRequest::findOrFail($id);
        $requestItem->status = ResignationRequest::STATUS_REJECTED;
        $requestItem->reviewed_by = auth()->user()->employee->id ?? 1;
        $requestItem->reviewed_at = now();
        $requestItem->remarks = $request->remarks ?? '';
        $requestItem->save();

        // Notify employee about rejection decision
        $employee = $requestItem->employee;
        $user = $employee ? User::where('email', $employee->email)->first() : null;
        if ($user) {
            $user->notify(new RequestDecisionNotification(
                'resignation',
                (int) $requestItem->id,
                'rejected',
                (string) ($requestItem->remarks ?? ''),
                (string) ($requestItem->reviewed_at ?? now()),
                route('employee.dashboard')
            ));
        }

        return redirect()->route('resignation.admin')->with('error', 'Resignation request rejected.');
    }

    public function generateApprovalLetter($id)
    {
        $requestItem = ResignationRequest::with('employee', 'reviewer')->findOrFail($id);

        if ($requestItem->status != ResignationRequest::STATUS_APPROVED) {
            return redirect()->route('resignation.admin')->with('error', 'Resignation request must be approved before generating the letter.');
        }

        $approverName = $requestItem->reviewer->name ?? 'Administrator';

        return view('resignation.approvalLetter', [
            'requestItem' => $requestItem,
            'approverName' => $approverName,
        ]);
    }
}
