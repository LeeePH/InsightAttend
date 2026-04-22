<?php

namespace App\Http\Controllers;

use App\Models\DiscountApplication;
use App\Models\Employee;
use App\Models\User;
use App\Notifications\PendingRequestNotification;
use App\Notifications\RequestDecisionNotification;
use App\Services\EmployeeRequestFormService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DiscountApplicationController extends Controller
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
        $template = $this->employeeRequestFormService->getTemplate('discount_request');
        if ($template && !$template->is_active) {
            return redirect()->route('employee.dashboard')->with('error', 'Application for Discount form is currently unavailable.');
        }

        if (!auth()->check() || !auth()->user()->employee) {
            return redirect()->route('employee.dashboard')->with('error', 'Employee profile not found. Please contact administrator.');
        }

        $currentEmployee = auth()->user()->employee;
        $isEmployee = true;
        $discountHistory = DiscountApplication::where('emp_id', $currentEmployee->id)->orderByDesc('created_at')->get();

        $formConfig = [
            'title' => $template ? $template->name : 'Application for Discount Form',
            'subtitle' => $template && $template->description ? $template->description : 'Submit your application for tuition discount.',
            'labels' => [
                'term_semester' => $this->employeeRequestFormService->fieldLabel($template, 'term_semester', 'Term/Semester'),
                'school_year' => $this->employeeRequestFormService->fieldLabel($template, 'school_year', 'School/Academic Year'),
                'date_request' => $this->employeeRequestFormService->fieldLabel($template, 'date_request', 'Date of Request'),
                'employee_department' => $this->employeeRequestFormService->fieldLabel($template, 'employee_department', 'Department'),
                'employee_position' => $this->employeeRequestFormService->fieldLabel($template, 'employee_position', 'Position'),
                'date_hire' => $this->employeeRequestFormService->fieldLabel($template, 'date_hire', 'Date of Hire'),
                'employment_status' => $this->employeeRequestFormService->fieldLabel($template, 'employment_status', 'Employment Status'),
                'school' => $this->employeeRequestFormService->fieldLabel($template, 'school', 'School'),
                'student_name' => $this->employeeRequestFormService->fieldLabel($template, 'student_name', 'Name of Student'),
                'student_no' => $this->employeeRequestFormService->fieldLabel($template, 'student_no', 'Student No'),
                'track_program' => $this->employeeRequestFormService->fieldLabel($template, 'track_program', 'Track & Strand/Program'),
                'grade_year_level' => $this->employeeRequestFormService->fieldLabel($template, 'grade_year_level', 'Grade/Year Level'),
                'student_department' => $this->employeeRequestFormService->fieldLabel($template, 'student_department', 'Department'),
                'discount_applied' => $this->employeeRequestFormService->fieldLabel($template, 'discount_applied', 'Discount Applied'),
                'family_relationship' => $this->employeeRequestFormService->fieldLabel($template, 'family_relationship', 'Relationship'),
                'privilege_child_order' => $this->employeeRequestFormService->fieldLabel($template, 'privilege_child_order', 'Employee Privileges Child'),
            ],
        ];

        $formUi = $this->employeeRequestFormService->getMergedUiSettings($template, 'discount_request');
        $fieldUi = $this->employeeRequestFormService->fieldUiMap($template, 'discount_request');

        return view('discount.request', compact('currentEmployee', 'isEmployee', 'discountHistory', 'formConfig', 'formUi', 'fieldUi'));
    }

    public function storeRequest(Request $request)
    {
        $this->employeeRequestFormService->ensureDefaults();
        $template = $this->employeeRequestFormService->getTemplate('discount_request');
        if ($template && !$template->is_active) {
            return redirect()->route('employee.dashboard')->with('error', 'Application for Discount form is currently unavailable.');
        }

        if (!auth()->check() || !auth()->user()->employee) {
            return back()->with('error', 'Employee profile not found. Please contact administrator.');
        }

        $rules = $this->employeeRequestFormService->applyTemplateRules('discount_request', [
            'term_semester' => 'required|string|max:64',
            'school_year' => 'required|string|max:64',
            'date_request' => 'required|date',
            'employee_department' => 'required|string|max:255',
            'employee_position' => 'required|string|max:255',
            'date_hire' => 'required|date',
            'employment_status' => 'required|in:probationary,regular',
            'school' => 'required|in:stsn,csta',
            'student_name' => 'required|string|max:255',
            'student_no' => 'required|string|max:255',
            'track_program' => 'required|string|max:255',
            'grade_year_level' => 'required|string|max:255',
            'student_department' => 'required|in:grade_school,junior_high,senior_high,college',
            'discount_applied' => 'required|in:family_relative,employee_privileges',
            'family_relationship' => 'nullable|string|max:255',
            'privilege_child_order' => 'nullable|in:1st,2nd,3rd,4th',
            'supporting_documents' => 'nullable|array|max:5',
            'supporting_documents.*' => 'file|mimes:jpeg,jpg,png,gif,webp,pdf,doc,docx|max:10240',
        ]);

        $validated = $request->validate($rules);

        if ($validated['discount_applied'] === 'family_relative' && empty(trim((string) ($validated['family_relationship'] ?? '')))) {
            return back()->withErrors(['family_relationship' => 'Relationship is required for family relative discount.'])->withInput();
        }
        if ($validated['discount_applied'] === 'employee_privileges' && empty(trim((string) ($validated['privilege_child_order'] ?? '')))) {
            return back()->withErrors(['privilege_child_order' => 'Child order is required for employee privileges.'])->withInput();
        }

        /** @var Employee $employee */
        $employee = auth()->user()->employee;

        $discount = DiscountApplication::create([
            'emp_id' => $employee->id,
            'term_semester' => $validated['term_semester'],
            'school_year' => $validated['school_year'],
            'date_request' => $validated['date_request'],
            'employee_department' => $validated['employee_department'],
            'employee_position' => $validated['employee_position'],
            'date_hire' => $validated['date_hire'],
            'employment_status' => $validated['employment_status'],
            'school' => $validated['school'],
            'student_name' => $validated['student_name'],
            'student_no' => $validated['student_no'],
            'track_program' => $validated['track_program'],
            'grade_year_level' => $validated['grade_year_level'],
            'student_department' => $validated['student_department'],
            'discount_applied' => $validated['discount_applied'],
            'family_relationship' => $validated['family_relationship'] ?? null,
            'privilege_child_order' => $validated['privilege_child_order'] ?? null,
            'status' => DiscountApplication::STATUS_PENDING,
        ]);

        $storedPaths = [];
        if ($request->hasFile('supporting_documents')) {
            foreach ($request->file('supporting_documents') as $file) {
                if ($file && $file->isValid()) {
                    $storedPaths[] = $file->store('discount_attachments/' . $discount->id, 'public');
                }
            }
        }
        if ($storedPaths !== []) {
            $discount->supporting_documents = $storedPaths;
            $discount->save();
        }

        $adminUsers = User::query()
            ->whereHas('roles', function ($q) {
                $q->where('slug', 'admin');
            })
            ->get();
        $adminUrl = route('discount.admin');
        foreach ($adminUsers as $admin) {
            $admin->notify(new PendingRequestNotification(
                'discount',
                (int) $discount->id,
                $employee->name ?? ('Employee #' . $employee->id),
                (string) $discount->created_at,
                $adminUrl
            ));
        }

        return redirect()->route('employee.dashboard')->with('success', 'Application for Discount submitted successfully.');
    }

    public function adminIndex()
    {
        $requests = DiscountApplication::with('employee')->orderByDesc('created_at')->get();

        return view('admin.discount', compact('requests'));
    }

    public function show($id)
    {
        $requestItem = DiscountApplication::with('employee', 'reviewer')->findOrFail($id);
        $this->authorizeViewer($requestItem);

        return view('discount.show', compact('requestItem'));
    }

    public function downloadAttachment($id, $index)
    {
        $requestItem = DiscountApplication::with('employee')->findOrFail($id);
        $this->authorizeViewer($requestItem);

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
        $request->validate([
            'tuition_fee_discount_percent' => 'required|numeric|min:0|max:100',
            'remarks' => 'nullable|string|max:2000',
        ]);

        $requestItem = DiscountApplication::findOrFail($id);
        $requestItem->status = DiscountApplication::STATUS_APPROVED;
        $requestItem->reviewed_by = auth()->user()->employee->id ?? 1;
        $requestItem->reviewed_at = now();
        $requestItem->remarks = $request->remarks ?? '';
        $requestItem->tuition_fee_discount_percent = $request->tuition_fee_discount_percent;
        $requestItem->save();

        $employee = $requestItem->employee;
        $user = $employee ? User::where('email', $employee->email)->first() : null;
        if ($user) {
            $user->notify(new RequestDecisionNotification(
                'discount',
                (int) $requestItem->id,
                'approved',
                (string) ($requestItem->remarks ?? ''),
                (string) ($requestItem->reviewed_at ?? now()),
                route('employee.dashboard')
            ));
        }

        return redirect()->route('discount.approvalLetterPdf', $requestItem->id);
    }

    public function reject(Request $request, $id)
    {
        $request->validate([
            'remarks' => 'required|string|max:2000',
        ]);

        $requestItem = DiscountApplication::findOrFail($id);
        $requestItem->status = DiscountApplication::STATUS_REJECTED;
        $requestItem->reviewed_by = auth()->user()->employee->id ?? 1;
        $requestItem->reviewed_at = now();
        $requestItem->remarks = $request->remarks;
        $requestItem->save();

        $employee = $requestItem->employee;
        $user = $employee ? User::where('email', $employee->email)->first() : null;
        if ($user) {
            $user->notify(new RequestDecisionNotification(
                'discount',
                (int) $requestItem->id,
                'rejected',
                (string) ($requestItem->remarks ?? ''),
                (string) ($requestItem->reviewed_at ?? now()),
                route('employee.dashboard')
            ));
        }

        return redirect()->route('discount.admin')->with('error', 'Discount application rejected.');
    }

    public function destroy($id)
    {
        $req = DiscountApplication::findOrFail($id);
        Storage::disk('public')->deleteDirectory('discount_attachments/' . $req->id);
        $req->delete();

        return redirect()->route('discount.admin')->with('error', 'Discount application deleted!');
    }

    public function generateApprovalLetterPdf($id)
    {
        $requestItem = DiscountApplication::with('employee', 'reviewer')->findOrFail($id);

        if ((int) $requestItem->status !== DiscountApplication::STATUS_APPROVED) {
            return redirect()->route('discount.admin')->with('error', 'Discount application must be approved before generating the PDF.');
        }

        $pdf = Pdf::loadView('discount.approvalLetterPdf', [
            'requestItem' => $requestItem,
        ])->setPaper('letter', 'portrait');

        return $pdf->download('application-for-discount-' . $requestItem->id . '.pdf');
    }

    private function authorizeViewer(DiscountApplication $requestItem): void
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

