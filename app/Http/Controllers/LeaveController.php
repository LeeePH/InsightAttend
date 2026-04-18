<?php

namespace App\Http\Controllers;

use DateTime;
use App\Models\User;
use App\Models\Employee;
use App\Models\Overtime;
use App\Models\FingerDevices;
use App\Helpers\FingerHelper;
use App\Models\Leave;
use App\Services\EmployeeRequestFormService;
use App\Http\Requests\AttendanceEmp;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Notifications\PendingRequestNotification;
use App\Notifications\RequestDecisionNotification;

class LeaveController extends Controller
{
    /** @var EmployeeRequestFormService */
    private $employeeRequestFormService;

    public function __construct(EmployeeRequestFormService $employeeRequestFormService)
    {
        $this->employeeRequestFormService = $employeeRequestFormService;
    }

    public function index()
    {
        $leaves = Leave::with('employee')->orderBy('created_at', 'desc')->get();
        return view('admin.leave')->with(['leaves' => $leaves]);
    }
    
    // Public leave request form
    public function requestForm()
    {
        $this->employeeRequestFormService->ensureDefaults();
        $template = $this->employeeRequestFormService->getTemplate('leave_request');
        if ($template && !$template->is_active) {
            $redirectRoute = auth()->check() ? 'employee.dashboard' : 'welcome';
            return redirect()->route($redirectRoute)->with('error', 'Leave request form is currently unavailable.');
        }

        $employees = Employee::all();
        $currentEmployee = null;
        $isEmployee = false;
        $leaveHistory = collect();
        
        // If user is logged in and has an employee record, auto-select
        if (auth()->check() && auth()->user()->employee) {
            $currentEmployee = auth()->user()->employee;
            $isEmployee = true;
            $leaveHistory = Leave::where('emp_id', $currentEmployee->id)
                ->orderBy('created_at', 'desc')
                ->get();
        }

        $leaveTypeOptions = $this->employeeRequestFormService->selectOptions($template, 'type', [
            '1' => 'Sick Leave',
            '2' => 'Annual Leave',
            '3' => 'Personal Leave',
            '4' => 'Maternity Leave',
            '5' => 'Paternity Leave',
            '6' => 'Vacation Leave',
            '7' => 'Emergency Leave',
            '8' => 'Others (Please Specify)',
        ]);

        $formUi = $this->employeeRequestFormService->getMergedUiSettings($template, 'leave_request');
        $fieldUi = $this->employeeRequestFormService->fieldUiMap($template, 'leave_request');

        $formConfig = [
            'title' => $template ? $template->name : 'Request Leave',
            'subtitle' => $template && $template->description ? $template->description : 'Submit your leave request with complete details for faster approval.',
            'labels' => [
                'leave_date' => $this->employeeRequestFormService->fieldLabel($template, 'leave_date', 'Start Date'),
                'leave_date_end' => $this->employeeRequestFormService->fieldLabel($template, 'leave_date_end', 'End Date'),
                'type' => $this->employeeRequestFormService->fieldLabel($template, 'type', 'Type of Leave'),
                'other_type' => $this->employeeRequestFormService->fieldLabel($template, 'other_type', 'Specify Other Leave Type'),
                'reason' => $this->employeeRequestFormService->fieldLabel($template, 'reason', 'Reason'),
            ],
            'required' => [
                'leave_date' => $this->employeeRequestFormService->fieldRequired($template, 'leave_date', true),
                'leave_date_end' => $this->employeeRequestFormService->fieldRequired($template, 'leave_date_end', true),
                'type' => $this->employeeRequestFormService->fieldRequired($template, 'type', true),
                'other_type' => $this->employeeRequestFormService->fieldRequired($template, 'other_type', false),
                'reason' => $this->employeeRequestFormService->fieldRequired($template, 'reason', true),
            ],
            'leaveTypeOptions' => $leaveTypeOptions,
            'otherTypeKey' => $this->findOtherOptionKey($leaveTypeOptions, '8'),
        ];
        
        return view('leave.request')->with([
            'employees' => $employees,
            'currentEmployee' => $currentEmployee,
            'isEmployee' => $isEmployee,
            'leaveHistory' => $leaveHistory,
            'formConfig' => $formConfig,
            'formUi' => $formUi,
            'fieldUi' => $fieldUi,
        ]);
    }
    
    // Store public leave request
    public function storeRequest(Request $request)
    {
        $this->employeeRequestFormService->ensureDefaults();
        $template = $this->employeeRequestFormService->getTemplate('leave_request');
        if ($template && !$template->is_active) {
            $redirectRoute = auth()->check() ? 'employee.dashboard' : 'welcome';
            return redirect()->route($redirectRoute)->with('error', 'Leave request form is currently unavailable.');
        }

        $rules = $this->employeeRequestFormService->applyTemplateRules('leave_request', [
            'emp_id' => 'required|exists:employees,id',
            'leave_date' => 'required|date',
            'leave_date_end' => 'required|date|after_or_equal:leave_date',
            'type' => 'required|integer|between:1,8',
            'reason' => 'required|string|max:500',
            'other_type' => 'nullable|string|max:255',
            'supporting_documents' => 'nullable|array|max:5',
            'supporting_documents.*' => 'file|mimes:jpeg,jpg,png,gif,webp,pdf,doc,docx|max:10240',
        ]);

        $typeOptions = $this->employeeRequestFormService->selectOptions($template, 'type', []);
        if (count($typeOptions) > 0) {
            $ids = array_keys($typeOptions);
            $rules['type'] = 'required|integer|in:' . implode(',', $ids);
        }

        $validated = $request->validate($rules);

        // Enforce: max 3 leave requests per employee per month (by leave start date)
        $start = Carbon::parse($validated['leave_date'])->startOfMonth()->toDateString();
        $end = Carbon::parse($validated['leave_date'])->endOfMonth()->toDateString();
        $monthlyCount = Leave::query()
            ->where('emp_id', $validated['emp_id'])
            ->whereBetween('leave_date', [$start, $end])
            ->count();
        if ($monthlyCount >= 3) {
            return back()->withErrors(['leave_date' => 'You have reached the maximum of 3 leave requests for this month.'])->withInput();
        }

        // Holidays: block filing on holiday dates (any date in selected range)
        $holidayDates = array_keys((array) config('holidays.dates', []));
        $from = Carbon::parse($validated['leave_date']);
        $to = Carbon::parse($validated['leave_date_end']);
        if ($to->lessThan($from)) {
            [$from, $to] = [$to, $from];
        }
        for ($d = $from->copy(); $d->lte($to); $d->addDay()) {
            if (in_array($d->toDateString(), $holidayDates, true)) {
                return back()->withErrors(['leave_date' => 'You cannot file a leave request on a holiday (' . $d->toDateString() . ').'])->withInput();
            }
        }

        // Supporting docs rules:
        // - Required for Sick + other leave types except Personal and Vacation (optional)
        // - Sick leave requires at least one image file
        $type = (int) $validated['type'];
        $docs = $request->file('supporting_documents', []);
        $docsCount = is_array($docs) ? count(array_filter($docs)) : 0;
        $docsRequiredTypes = [1, 2, 4, 5, 7, 8]; // sick, annual, maternity, paternity, emergency, others
        $docsOptionalTypes = [3, 6]; // personal, vacation

        if (in_array($type, $docsRequiredTypes, true) && $docsCount === 0) {
            return back()->withErrors(['supporting_documents' => 'Supporting documentation is required for this leave type.'])->withInput();
        }
        if ($type === 1 && $docsCount > 0) {
            $hasImage = false;
            foreach ($docs as $f) {
                if ($f && $f->isValid()) {
                    $mime = (string) $f->getMimeType();
                    if (str_starts_with($mime, 'image/')) {
                        $hasImage = true;
                        break;
                    }
                }
            }
            if (!$hasImage) {
                return back()->withErrors(['supporting_documents' => 'Sick Leave requires at least one supporting image.'])->withInput();
            }
        }
        
        $leave = new Leave();
        $leave->emp_id = $validated['emp_id'];
        $leave->leave_date = $validated['leave_date'];
        $leave->leave_date_end = $validated['leave_date_end'];
        
        // Calculate number of leave days
        $startDate = new DateTime($validated['leave_date']);
        $endDate = new DateTime($validated['leave_date_end']);
        $leaveDays = $endDate->diff($startDate)->days + 1;
        $leave->leave_days = $leaveDays;
        
        $leave->leave_time = date('H:i:s');
        $leave->type = $validated['type'];
        $leave->reason = $validated['reason'];
        $leave->status = Leave::STATUS_PENDING;
        
        // If "Others" is selected, append the specification
        $leaveTypeOptions = $this->employeeRequestFormService->selectOptions($template, 'type', []);
        $otherTypeKey = $this->findOtherOptionKey($leaveTypeOptions, '8');

        if ((string) $validated['type'] === (string) $otherTypeKey && $request->other_type) {
            $leave->reason = $validated['reason'] . ' [Other: ' . $request->other_type . ']';
        }

        $leave->save();

        $storedPaths = [];
        if ($request->hasFile('supporting_documents')) {
            foreach ($request->file('supporting_documents') as $file) {
                if ($file && $file->isValid()) {
                    $storedPaths[] = $file->store('leave_attachments/' . $leave->id, 'public');
                }
            }
        }
        if ($storedPaths !== []) {
            $leave->supporting_documents = $storedPaths;
            $leave->save();
        }

        // Notify admins about pending leave request
        $employee = Employee::find($leave->emp_id);
        $adminUsers = User::query()
            ->whereHas('roles', function ($q) {
                $q->where('slug', 'admin');
            })
            ->get();
        $adminUrl = route('leave');
        foreach ($adminUsers as $admin) {
            $admin->notify(new PendingRequestNotification(
                'leave',
                (int) $leave->id,
                $employee?->name ?? ('Employee #' . $leave->emp_id),
                (string) $leave->created_at,
                $adminUrl
            ));
        }

        // Redirect to employee dashboard with success message
        return redirect()->route('employee.dashboard')->with(['success' => 'Leave request submitted successfully!']);
    }
    
    public function store(Request $request)
    {
        $request->validate([
            'emp_id' => 'required|exists:employees,id',
            'leave_date' => 'required|date',
            'type' => 'required|integer|between:1,4',
            'reason' => 'required|string|max:500'
        ]);
        
        $leave = new Leave();
        $leave->emp_id = $request->emp_id;
        $leave->leave_date = $request->leave_date;
        $leave->leave_time = $request->leave_time ?? date('H:i:s');
        $leave->type = $request->type;
        $leave->reason = $request->reason;
        $leave->status = Leave::STATUS_PENDING;
        $leave->save();
        
        return redirect()->route('leave')->with(['success' => 'Leave request submitted successfully!']);
    }
    
    public function approve(Request $request, $id)
    {
        $leave = Leave::findOrFail($id);
        $leave->status = Leave::STATUS_APPROVED;
        $leave->approved_by = auth()->user()->employee->id ?? 1;
        $leave->approved_at = now();
        $leave->remarks = $request->remarks ?? '';
        $leave->save();

        // Notify employee about approval decision
        $employee = $leave->employee;
        $user = $employee ? User::where('email', $employee->email)->first() : null;
        if ($user) {
            $user->notify(new RequestDecisionNotification(
                'leave',
                (int) $leave->id,
                'approved',
                (string) ($leave->remarks ?? ''),
                (string) ($leave->approved_at ?? now()),
                route('employee.dashboard')
            ));
        }
        
        return redirect()->route('leave.approvalLetter', $leave->id);
    }
    
    public function reject(Request $request, $id)
    {
        $leave = Leave::findOrFail($id);
        $leave->status = Leave::STATUS_REJECTED;
        $leave->approved_by = auth()->user()->employee->id ?? 1;
        $leave->approved_at = now();
        $leave->remarks = $request->remarks ?? '';
        $leave->save();

        // Notify employee about rejection decision
        $employee = $leave->employee;
        $user = $employee ? User::where('email', $employee->email)->first() : null;
        if ($user) {
            $user->notify(new RequestDecisionNotification(
                'leave',
                (int) $leave->id,
                'rejected',
                (string) ($leave->remarks ?? ''),
                (string) ($leave->approved_at ?? now()),
                route('employee.dashboard')
            ));
        }
        
        return redirect()->route('leave')->with(['error' => 'Leave request rejected!']);
    }
    
    public function destroy($id)
    {
        $leave = Leave::findOrFail($id);
        Storage::disk('public')->deleteDirectory('leave_attachments/' . $leave->id);
        $leave->delete();

        return redirect()->route('leave')->with(['error' => 'Leave request deleted!']);
    }
    
    // Generate approval letter
    public function generateApprovalLetter($id)
    {
        $leave = Leave::with('employee', 'approver')->findOrFail($id);
        
        if ($leave->status != Leave::STATUS_APPROVED) {
            return redirect()->route('leave')->with(['error' => 'Leave must be approved to generate approval letter!']);
        }
        
        return view('leave.approvalLetter')->with(['leave' => $leave]);
    }

    public function indexOvertime()
    {
        return view('admin.overtime')->with(['overtimes' => Overtime::all()]);
    }

    // public static function overTime(Employee $employee)
    // {
    //     $current_t = new DateTime(date('H:i:s'));
    //     $start_t = new DateTime($employee->schedules->first()->time_out);
    //     $difference = $start_t->diff($current_t)->format('%H:%I:%S');

    //     $overtime = new Overtime();
    //     $overtime->emp_id = $employee->id;
    //     $overtime->duration = $difference;
    //     $overtime->overtime_date = date('Y-m-d');
    //     $overtime->save();
    // }
    public static function overTimeDevice($att_dateTime, Employee $employee)
    {
        
            $attendance_time =new DateTime($att_dateTime);
            $checkout = new DateTime($employee->schedules->first()->time_out);
            $difference = $checkout->diff($attendance_time)->format('%H:%I:%S');

            $overtime = new Overtime();
            $overtime->emp_id = $employee->id;
            $overtime->duration = $difference;
            $overtime->overtime_date = date('Y-m-d', strtotime($att_dateTime));
            $overtime->save();
        
    }

    private function findOtherOptionKey(array $options, string $default): string
    {
        foreach ($options as $value => $label) {
            if (stripos($label, 'other') !== false) {
                return (string) $value;
            }
        }

        return $default;
    }
}
