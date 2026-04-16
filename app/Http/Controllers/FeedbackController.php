<?php

namespace App\Http\Controllers;

use App\Models\Feedback;
use App\Services\EmployeeRequestFormService;
use Illuminate\Http\Request;

class FeedbackController extends Controller
{
    /** @var EmployeeRequestFormService */
    private $employeeRequestFormService;

    public function __construct(EmployeeRequestFormService $employeeRequestFormService)
    {
        $this->employeeRequestFormService = $employeeRequestFormService;
    }

    // Employee: Show feedback form
    public function index()
    {
        $this->employeeRequestFormService->ensureDefaults();
        $template = $this->employeeRequestFormService->getTemplate('feedback_request');
        if ($template && !$template->is_active) {
            return redirect()->route('employee.dashboard')->with('error', 'Feedback form is currently unavailable.');
        }

        if (!auth()->check() || !auth()->user()->employee) {
            return view('employee.feedback', ['feedbacks' => collect()])
                ->with('error', 'Employee profile not found. Please contact administrator.');
        }

        $feedbacks = Feedback::where('emp_id', auth()->user()->employee->id)
            ->orderBy('created_at', 'desc')
            ->get();
        
        $subjectOptions = $this->employeeRequestFormService->selectOptions($template, 'subject', [
            'General Inquiry' => 'General Inquiry',
            'Attendance Issue' => 'Attendance Issue',
            'Leave Request Issue' => 'Leave Request Issue',
            'Schedule Concern' => 'Schedule Concern',
            'Technical Problem' => 'Technical Problem',
            'Suggestion' => 'Suggestion',
            'Complaint' => 'Complaint',
            'Other' => 'Other',
        ]);

        $formConfig = [
            'title' => $template ? $template->name : 'Submit Feedback',
            'subtitle' => $template && $template->description ? $template->description : 'Share concerns, suggestions, or issues for quick support.',
            'labels' => [
                'subject' => $this->employeeRequestFormService->fieldLabel($template, 'subject', 'Subject'),
                'other_subject' => $this->employeeRequestFormService->fieldLabel($template, 'other_subject', 'Specify Subject'),
                'message' => $this->employeeRequestFormService->fieldLabel($template, 'message', 'Your Feedback / Message'),
            ],
            'required' => [
                'subject' => $this->employeeRequestFormService->fieldRequired($template, 'subject', true),
                'other_subject' => $this->employeeRequestFormService->fieldRequired($template, 'other_subject', false),
                'message' => $this->employeeRequestFormService->fieldRequired($template, 'message', true),
            ],
            'subjectOptions' => $subjectOptions,
            'otherSubjectValue' => $this->findOtherOptionValue($subjectOptions, 'Other'),
        ];

        return view('employee.feedback', compact('feedbacks', 'formConfig'));
    }
    
    // Employee: Submit feedback
    public function store(Request $request)
    {
        $this->employeeRequestFormService->ensureDefaults();
        $template = $this->employeeRequestFormService->getTemplate('feedback_request');
        if ($template && !$template->is_active) {
            return redirect()->route('employee.dashboard')->with('error', 'Feedback form is currently unavailable.');
        }

        $rules = $this->employeeRequestFormService->applyTemplateRules('feedback_request', [
            'subject' => 'required|string|max:255',
            'other_subject' => 'nullable|string|max:255',
            'message' => 'required|string|max:2000',
        ]);

        $request->validate($rules);

        if (!auth()->check() || !auth()->user()->employee) {
            return redirect()->route('employee.feedback')->with('error', 'Employee profile not found. Please contact administrator.');
        }

        $subject = $request->subject;
        $subjectOptions = $this->employeeRequestFormService->selectOptions($template, 'subject', []);
        $otherSubjectValue = $this->findOtherOptionValue($subjectOptions, 'Other');
        if ($subject === $otherSubjectValue && $request->filled('other_subject')) {
            $subject = $request->other_subject;
        }
        
        $feedback = new Feedback();
        $feedback->emp_id = auth()->user()->employee->id;
        $feedback->subject = $subject;
        $feedback->message = $request->message;
        $feedback->status = Feedback::STATUS_PENDING;
        $feedback->save();
        
        return redirect()->route('employee.feedback')->with(['success' => 'Feedback submitted successfully!']);
    }
    
    // Admin: View all feedbacks
    public function adminIndex()
    {
        $feedbacks = Feedback::with('employee')
            ->orderBy('created_at', 'desc')
            ->get();
        return view('admin.feedback', compact('feedbacks'));
    }
    
    // Admin: Mark as read
    public function markRead($id)
    {
        $feedback = Feedback::findOrFail($id);
        $feedback->status = Feedback::STATUS_READ;
        $feedback->save();
        
        return redirect()->route('admin.feedback')->with(['success' => 'Feedback marked as read!']);
    }
    
    // Admin: Mark as resolved
    public function markResolved($id)
    {
        $feedback = Feedback::findOrFail($id);
        $feedback->status = Feedback::STATUS_RESOLVED;
        $feedback->save();
        
        return redirect()->route('admin.feedback')->with(['success' => 'Feedback marked as resolved!']);
    }

    private function findOtherOptionValue(array $options, string $default): string
    {
        foreach ($options as $value => $label) {
            if (stripos($label, 'other') !== false) {
                return (string) $value;
            }
        }

        return $default;
    }
}
