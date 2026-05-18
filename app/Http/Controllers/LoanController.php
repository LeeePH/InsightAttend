<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\LoanRequest;
use App\Models\User;
use App\Notifications\PendingRequestNotification;
use App\Notifications\RequestDecisionNotification;
use App\Services\EmployeeRequestFormService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

class LoanController extends Controller
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
        $template = $this->employeeRequestFormService->getTemplate('loan_request');
        if ($template && !$template->is_active) {
            return redirect()->route('employee.dashboard')->with('error', 'Loan application form is currently unavailable.');
        }

        $currentEmployee = null;
        $isEmployee = false;
        $loanHistory = collect();

        if (auth()->check() && auth()->user()->employee) {
            $currentEmployee = auth()->user()->employee;
            $isEmployee = true;
            $loanHistory = LoanRequest::where('emp_id', $currentEmployee->id)
                ->orderBy('created_at', 'desc')
                ->get();
        }

        $purposeOptions = [
            'hospitalization' => 'Hospitalization/Medication',
            'calamity' => 'Emergency house repair due to calamity',
            'bereavement' => 'Bereavement',
            'tuition' => 'Tuition Fee',
            'dental' => 'Dental',
            'other' => 'Other (please specify)',
        ];

        $formConfig = [
            'title' => $template ? $template->name : 'Company Loan Application',
            'subtitle' => $template && $template->description ? $template->description : 'Submit your loan application with complete details for faster processing.',
            'labels' => [
                'date_filed' => $this->employeeRequestFormService->fieldLabel($template, 'date_filed', 'Date Filed'),
                'civil_status' => $this->employeeRequestFormService->fieldLabel($template, 'civil_status', 'Civil Status'),
                'contact_number' => $this->employeeRequestFormService->fieldLabel($template, 'contact_number', 'Contact number'),
                'hire_date' => $this->employeeRequestFormService->fieldLabel($template, 'hire_date', 'Hire Date'),
                'amount_requested' => $this->employeeRequestFormService->fieldLabel($template, 'amount_requested', 'Amount requested by employee'),
                'purpose' => $this->employeeRequestFormService->fieldLabel($template, 'purpose', 'Reason(s) for availing company loan'),
                'other_purpose' => $this->employeeRequestFormService->fieldLabel($template, 'other_purpose', 'Other (please specify)'),
                'employee_statement' => $this->employeeRequestFormService->fieldLabel(
                    $template,
                    'employee_statement',
                    "I hereby certify that the foregoing statements and information are true and correct. I understand that any dishonest information or false documents I presented to HR to support my loan application will constitute a sufficient cause for my dismissal. I understand and agree that the approved total loan amount will be deducted from the salary. In case my employment with the company has ceased before full payment of the loan, the balance will be deducted from my quitclaim."
                ),
            ],
            'required' => [
                'date_filed' => $this->employeeRequestFormService->fieldRequired($template, 'date_filed', true),
                'civil_status' => $this->employeeRequestFormService->fieldRequired($template, 'civil_status', true),
                'contact_number' => $this->employeeRequestFormService->fieldRequired($template, 'contact_number', true),
                'hire_date' => $this->employeeRequestFormService->fieldRequired($template, 'hire_date', true),
                'amount_requested' => $this->employeeRequestFormService->fieldRequired($template, 'amount_requested', true),
                'purpose' => $this->employeeRequestFormService->fieldRequired($template, 'purpose', true),
                'other_purpose' => $this->employeeRequestFormService->fieldRequired($template, 'other_purpose', false),
                'employee_statement' => $this->employeeRequestFormService->fieldRequired($template, 'employee_statement', true),
            ],
            'purposeOptions' => $purposeOptions,
        ];

        $formUi = $this->employeeRequestFormService->getMergedUiSettings($template, 'loan_request');
        $fieldUi = $this->employeeRequestFormService->fieldUiMap($template, 'loan_request');

        return view('loan.request')->with([
            'currentEmployee' => $currentEmployee,
            'isEmployee' => $isEmployee,
            'loanHistory' => $loanHistory,
            'formConfig' => $formConfig,
            'formUi' => $formUi,
            'fieldUi' => $fieldUi,
        ]);
    }

    public function storeRequest(Request $request)
    {
        $this->employeeRequestFormService->ensureDefaults();
        $template = $this->employeeRequestFormService->getTemplate('loan_request');
        if ($template && !$template->is_active) {
            return redirect()->route('employee.dashboard')->with('error', 'Loan application form is currently unavailable.');
        }

        if (!auth()->check() || !auth()->user()->employee) {
            return redirect()->back()->with('error', 'Employee profile not found. Please contact administrator.');
        }

        $rules = $this->employeeRequestFormService->applyTemplateRules('loan_request', [
            'date_filed' => 'required|date',
            'civil_status' => 'required|string|in:single,married',
            'contact_number' => 'required|string|max:32',
            'hire_date' => 'required|date',
            'amount_requested' => 'required|numeric|min:0.01|max:999999.99',
            'purpose' => 'required|array|min:1',
            'purpose.*' => 'in:hospitalization,calamity,bereavement,tuition,dental,other',
            'other_purpose' => 'nullable|string|max:255',
            'hospital_patient_name' => 'nullable|string|max:255',
            'hospital_relationship' => 'nullable|string|max:255',
            'hospital_age' => 'nullable|integer|min:0|max:120',
            'calamity_details' => 'nullable|string|max:2000',
            'bereavement_relationship' => 'nullable|string|max:255',
            'tuition_child_name' => 'nullable|string|max:255',
            'tuition_child_age' => 'nullable|integer|min:0|max:120',
            'tuition_child_level' => 'nullable|string|max:255',
            'dental_patient_name' => 'nullable|string|max:255',
            'dental_relationship' => 'nullable|string|max:255',
            'dental_age' => 'nullable|integer|min:0|max:120',
            'employee_statement' => 'required|accepted',
            'supporting_documents' => 'nullable|array|max:5',
            'supporting_documents.*' => 'file|mimes:jpeg,jpg,png,gif,webp,pdf,doc,docx|max:10240',
        ]);

        $validated = $request->validate($rules);

        /** @var Employee $employee */
        $employee = auth()->user()->employee;

        $selectedPurposes = array_values(array_unique(array_map('strval', $validated['purpose'] ?? [])));
        $purposeFlags = [];
        foreach ($selectedPurposes as $p) {
            $purposeFlags[$p] = true;
        }

        if (in_array('other', $selectedPurposes, true) && empty(trim((string) ($validated['other_purpose'] ?? '')))) {
            return back()->withErrors(['other_purpose' => 'Please specify the other purpose.'])->withInput();
        }

        $req = LoanRequest::create([
            'emp_id' => $employee->id,
            'date_filed' => $validated['date_filed'],
            'civil_status' => $validated['civil_status'],
            'contact_number' => $validated['contact_number'],
            'hire_date' => $validated['hire_date'],
            'amount_requested' => $validated['amount_requested'],
            'purpose_flags' => $purposeFlags,
            'hospital_patient_name' => $validated['hospital_patient_name'] ?? null,
            'hospital_relationship' => $validated['hospital_relationship'] ?? null,
            'hospital_age' => $validated['hospital_age'] ?? null,
            'calamity_details' => $validated['calamity_details'] ?? null,
            'bereavement_relationship' => $validated['bereavement_relationship'] ?? null,
            'tuition_child_name' => $validated['tuition_child_name'] ?? null,
            'tuition_child_age' => $validated['tuition_child_age'] ?? null,
            'tuition_child_level' => $validated['tuition_child_level'] ?? null,
            'dental_patient_name' => $validated['dental_patient_name'] ?? null,
            'dental_relationship' => $validated['dental_relationship'] ?? null,
            'dental_age' => $validated['dental_age'] ?? null,
            'other_purpose' => $validated['other_purpose'] ?? null,
            'employee_statement' => true,
            'status' => LoanRequest::STATUS_PENDING,
        ]);

        $storedPaths = [];
        if ($request->hasFile('supporting_documents')) {
            foreach ($request->file('supporting_documents') as $file) {
                if ($file && $file->isValid()) {
                    $storedPaths[] = $file->store('loan_attachments/' . $req->id, 'public');
                }
            }
        }
        if ($storedPaths !== []) {
            $req->supporting_documents = $storedPaths;
            $req->save();
        }

        // Notify admins about pending loan request
        $adminUsers = User::query()
            ->whereHas('roles', function ($q) {
                $q->where('slug', 'admin');
            })
            ->get();
        $adminUrl = route('loan.admin');
        foreach ($adminUsers as $admin) {
            $admin->notify(new PendingRequestNotification(
                'loan',
                (int) $req->id,
                $employee->name ?? ('Employee #' . $employee->id),
                (string) $req->created_at,
                $adminUrl
            ));
        }

        return redirect()->route('employee.dashboard')->with('success', 'Your loan application has been submitted successfully.');
    }

    public function adminIndex()
    {
        $requests = LoanRequest::with('employee')->orderBy('created_at', 'desc')->get();
        return view('admin.loan', compact('requests'));
    }

    public function show($id)
    {
        $requestItem = LoanRequest::with('employee', 'reviewer')->findOrFail($id);

        $user = auth()->user();
        $isAdmin = $user && method_exists($user, 'hasRole') && $user->hasRole('admin');
        $isEmployee = $user && method_exists($user, 'hasRole') && $user->hasAnyRole(['employee', 'secretary']);

        if ($isAdmin) {
            // ok
        } elseif ($isEmployee && $user->employee && (int) $user->employee->id === (int) $requestItem->emp_id) {
            // ok
        } else {
            abort(403);
        }

        return view('loan.show', [
            'requestItem' => $requestItem,
        ]);
    }

    public function downloadAttachment($id, $index)
    {
        $requestItem = LoanRequest::with('employee')->findOrFail($id);

        $user = auth()->user();
        $isAdmin = $user && method_exists($user, 'hasRole') && $user->hasRole('admin');
        $isEmployee = $user && method_exists($user, 'hasRole') && $user->hasAnyRole(['employee', 'secretary']);

        if ($isAdmin) {
            // ok
        } elseif ($isEmployee && $user->employee && (int) $user->employee->id === (int) $requestItem->emp_id) {
            // ok
        } else {
            abort(403);
        }

        $paths = is_array($requestItem->supporting_documents) ? $requestItem->supporting_documents : [];
        $i = (int) $index;
        if (!isset($paths[$i]) || !is_string($paths[$i]) || $paths[$i] === '') {
            abort(404);
        }

        $path = $paths[$i];
        if (!Storage::disk('public')->exists($path)) {
            abort(404);
        }

        return Storage::disk('public')->download($path);
    }

    public function approve(Request $request, $id)
    {
        $requestItem = LoanRequest::findOrFail($id);
        $requestItem->status = LoanRequest::STATUS_APPROVED;
        $requestItem->reviewed_by = auth()->user()->employee->id ?? 1;
        $requestItem->reviewed_at = now();
        $requestItem->remarks = $request->remarks ?? '';
        if ($request->filled('amount_approved')) {
            $requestItem->amount_approved = $request->input('amount_approved');
        }
        $requestItem->save();

        $employee = $requestItem->employee;
        $user = $employee ? User::where('email', $employee->email)->first() : null;
        if ($user) {
            $user->notify(new RequestDecisionNotification(
                'loan',
                (int) $requestItem->id,
                'approved',
                (string) ($requestItem->remarks ?? ''),
                (string) ($requestItem->reviewed_at ?? now()),
                route('employee.dashboard')
            ));
        }

        return redirect()->route('loan.approvalLetterPdf', $requestItem->id);
    }

    public function reject(Request $request, $id)
    {
        $requestItem = LoanRequest::findOrFail($id);
        $requestItem->status = LoanRequest::STATUS_REJECTED;
        $requestItem->reviewed_by = auth()->user()->employee->id ?? 1;
        $requestItem->reviewed_at = now();
        $requestItem->remarks = $request->remarks ?? '';
        $requestItem->save();

        $employee = $requestItem->employee;
        $user = $employee ? User::where('email', $employee->email)->first() : null;
        if ($user) {
            $user->notify(new RequestDecisionNotification(
                'loan',
                (int) $requestItem->id,
                'rejected',
                (string) ($requestItem->remarks ?? ''),
                (string) ($requestItem->reviewed_at ?? now()),
                route('employee.dashboard')
            ));
        }

        return redirect()->route('loan.admin')->with('error', 'Loan application rejected.');
    }

    public function destroy($id)
    {
        $req = LoanRequest::findOrFail($id);
        Storage::disk('public')->deleteDirectory('loan_attachments/' . $req->id);
        $req->delete();

        return redirect()->route('loan.admin')->with('error', 'Loan application deleted!');
    }

    public function generateApprovalLetterPdf($id)
    {
        $requestItem = LoanRequest::with('employee', 'reviewer')->findOrFail($id);

        if ((int) $requestItem->status !== LoanRequest::STATUS_APPROVED) {
            return redirect()->route('loan.admin')->with('error', 'Loan application must be approved before generating the PDF.');
        }

        $purposeLabels = [
            'hospitalization' => 'Hospitalization/Medication',
            'calamity' => 'Emergency house repair due to calamity',
            'bereavement' => 'Bereavement',
            'tuition' => 'Tuition Fee',
            'dental' => 'Dental',
            'other' => 'Other',
        ];

        $pdf = Pdf::loadView('loan.approvalLetterPdf', [
            'requestItem' => $requestItem,
            'purposeLabels' => $purposeLabels,
        ])->setPaper('a4');

        return $pdf->download('company-loan-application-' . $requestItem->id . '.pdf');
    }
}

