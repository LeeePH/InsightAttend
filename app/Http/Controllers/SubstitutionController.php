<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\SubstitutionRequest;
use App\Models\User;
use App\Notifications\PendingRequestNotification;
use App\Notifications\RequestDecisionNotification;
use App\Services\EmployeeRequestFormService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class SubstitutionController extends Controller
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
        $template = $this->employeeRequestFormService->getTemplate('substitution_form_request');
        if ($template && !$template->is_active) {
            return redirect()->route('employee.dashboard')->with('error', 'Subsitution Form is currently unavailable.');
        }

        if (!auth()->check() || !auth()->user()->employee) {
            return redirect()->route('employee.dashboard')->with('error', 'Employee profile not found. Please contact administrator.');
        }

        $currentEmployee = auth()->user()->employee;
        $isEmployee = true;
        $history = SubstitutionRequest::where('emp_id', $currentEmployee->id)->orderByDesc('created_at')->get();
        $formUi = $this->employeeRequestFormService->getMergedUiSettings($template, 'substitution_form_request');

        return view('substitution.request', compact('currentEmployee', 'isEmployee', 'history', 'formUi'));
    }

    public function storeRequest(Request $request)
    {
        if (!auth()->check() || !auth()->user()->employee) {
            return back()->with('error', 'Employee profile not found. Please contact administrator.');
        }

        $validated = $request->validate([
            'absent_teacher_name' => 'required|string|max:255',
            'substitute_teacher_name' => 'required|string|max:255',
            'entries' => 'required|array|min:1',
            'entries.*.subject' => 'nullable|string|max:255',
            'entries.*.year_section' => 'nullable|string|max:120',
            'entries.*.date' => 'nullable|date',
            'entries.*.time' => 'nullable|string|max:80',
            'entries.*.room' => 'nullable|string|max:80',
            'entries.*.hours' => 'nullable|string|max:40',
        ]);

        /** @var Employee $employee */
        $employee = auth()->user()->employee;

        $rows = [];
        foreach ($validated['entries'] as $row) {
            $rows[] = [
                'subject' => $row['subject'] ?? '',
                'year_section' => $row['year_section'] ?? '',
                'date' => $row['date'] ?? '',
                'time' => $row['time'] ?? '',
                'room' => $row['room'] ?? '',
                'hours' => $row['hours'] ?? '',
            ];
        }

        $item = SubstitutionRequest::create([
            'emp_id' => $employee->id,
            'absent_teacher_name' => $validated['absent_teacher_name'],
            'substitute_teacher_name' => $validated['substitute_teacher_name'],
            'entries' => $rows,
            'status' => SubstitutionRequest::STATUS_PENDING,
        ]);

        $adminUsers = User::query()->whereHas('roles', function ($q) {
            $q->where('slug', 'admin');
        })->get();
        $adminUrl = route('substitution.admin');
        foreach ($adminUsers as $admin) {
            $admin->notify(new PendingRequestNotification(
                'subsitution',
                (int) $item->id,
                $employee->name ?? ('Employee #' . $employee->id),
                (string) $item->created_at,
                $adminUrl
            ));
        }

        return redirect()->route('employee.dashboard')->with('success', 'Subsitution Form submitted successfully.');
    }

    public function adminIndex()
    {
        $requests = SubstitutionRequest::with('employee')->orderByDesc('created_at')->get();

        return view('admin.substitution', compact('requests'));
    }

    public function show($id)
    {
        $requestItem = SubstitutionRequest::with('employee', 'reviewer')->findOrFail($id);
        $this->authorizeViewer($requestItem);

        return view('substitution.show', compact('requestItem'));
    }

    public function approve(Request $request, $id)
    {
        $request->validate([
            'remarks' => 'nullable|string|max:2000',
        ]);

        $item = SubstitutionRequest::findOrFail($id);
        $item->status = SubstitutionRequest::STATUS_APPROVED;
        $item->reviewed_by = auth()->user()->employee->id ?? 1;
        $item->reviewed_at = now();
        $item->remarks = $request->remarks ?? '';
        $item->save();

        $employee = $item->employee;
        $user = $employee ? User::where('email', $employee->email)->first() : null;
        if ($user) {
            $user->notify(new RequestDecisionNotification(
                'subsitution',
                (int) $item->id,
                'approved',
                (string) ($item->remarks ?? ''),
                (string) ($item->reviewed_at ?? now()),
                route('employee.dashboard')
            ));
        }

        return redirect()->route('substitution.approvalLetterPdf', $item->id);
    }

    public function reject(Request $request, $id)
    {
        $request->validate([
            'remarks' => 'required|string|max:2000',
        ]);

        $item = SubstitutionRequest::findOrFail($id);
        $item->status = SubstitutionRequest::STATUS_REJECTED;
        $item->reviewed_by = auth()->user()->employee->id ?? 1;
        $item->reviewed_at = now();
        $item->remarks = $request->remarks;
        $item->save();

        $employee = $item->employee;
        $user = $employee ? User::where('email', $employee->email)->first() : null;
        if ($user) {
            $user->notify(new RequestDecisionNotification(
                'subsitution',
                (int) $item->id,
                'rejected',
                (string) ($item->remarks ?? ''),
                (string) ($item->reviewed_at ?? now()),
                route('employee.dashboard')
            ));
        }

        return redirect()->route('substitution.admin')->with('error', 'Subsitution Form rejected.');
    }

    public function destroy($id)
    {
        $item = SubstitutionRequest::findOrFail($id);
        $item->delete();

        return redirect()->route('substitution.admin')->with('error', 'Subsitution Form deleted.');
    }

    public function generateApprovalLetterPdf($id)
    {
        $requestItem = SubstitutionRequest::with('employee', 'reviewer')->findOrFail($id);
        if ((int) $requestItem->status !== SubstitutionRequest::STATUS_APPROVED) {
            return redirect()->route('substitution.admin')->with('error', 'Request must be approved before generating the PDF.');
        }

        $pdf = Pdf::loadView('substitution.approvalLetterPdf', compact('requestItem'))
            ->setPaper('letter', 'portrait');

        return $pdf->download('subsitution-form-' . $requestItem->id . '.pdf');
    }

    private function authorizeViewer(SubstitutionRequest $requestItem): void
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
