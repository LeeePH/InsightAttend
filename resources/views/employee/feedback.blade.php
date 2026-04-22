@extends('layouts.master')

@php
use Illuminate\Support\Str;
@endphp

@section('css')
<style>
    :root {
        --theme-bg: #f8f1eb;
        --theme-text: #3e2412;
        --theme-muted: #7b5a45;
        --theme-border: #e2cdbd;
        --theme-accent-soft: #f3e4d7;
        --theme-chip: #f7ebe2;
    }

    body {
        background-color: var(--theme-bg);
    }

    .feedback-page .card {
        border: 1px solid var(--theme-border);
        box-shadow: 0 10px 24px rgba(19, 28, 43, 0.06);
        border-radius: 14px;
    }

    .feedback-page .card-body {
        padding: 20px 18px;
    }

    .feedback-page {
        margin-left: -8px;
        margin-right: -8px;
    }

    .feedback-title {
        color: var(--theme-text);
        margin-bottom: 6px;
    }

    .feedback-subtitle {
        color: var(--theme-muted);
        margin-bottom: 0;
    }

    .feedback-page label {
        color: var(--theme-text);
        font-weight: 600;
    }

    .feedback-page .form-control {
        border-radius: 10px;
        border: 1px solid #cfd7e6;
        color: var(--theme-text);
        background: #ffffff;
    }

    .feedback-page .form-control:focus {
        border-color: #9fb0cc;
        box-shadow: 0 0 0 0.15rem rgba(46, 63, 92, 0.12);
    }

    .feedback-page .table {
        color: var(--theme-text);
    }

    .feedback-page .table thead th {
        background: var(--theme-accent-soft);
        color: var(--theme-text);
        border-color: var(--theme-border);
        font-weight: 600;
    }

    .feedback-page .table td {
        border-color: var(--theme-border);
    }

    .theme-btn {
        border-radius: 999px;
        border: 1px solid #c5cede;
        background: #f4f7fc;
        color: var(--theme-text);
        font-weight: 600;
        padding: 10px 20px;
        transition: all 0.2s ease;
    }

    .theme-btn:hover {
        background: #e8edf6;
        color: var(--theme-text);
        text-decoration: none;
    }

    .theme-badge {
        background: var(--theme-chip);
        color: var(--theme-text);
        border: 1px solid #cfd7e6;
        font-weight: 600;
        padding: 5px 10px;
        border-radius: 999px;
        display: inline-block;
    }
</style>
@endsection

@section('breadcrumb')
<div class="col-sm-6">
    <h4 class="page-title text-left">Feedback</h4>
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="javascript:void(0);">Home</a></li>
        <li class="breadcrumb-item"><a href="javascript:void(0);">Feedback</a></li>
    </ol>
</div>
@endsection

@section('content')
@include('includes.flash')
@php
    $formConfig = $formConfig ?? [
        'title' => 'Submit Feedback',
        'subtitle' => 'Share concerns, suggestions, or issues for quick support.',
        'labels' => [
            'subject' => 'Subject',
            'other_subject' => 'Specify Subject',
            'message' => 'Your Feedback / Message',
        ],
        'required' => [
            'subject' => true,
            'other_subject' => false,
            'message' => true,
        ],
        'subjectOptions' => [
            'General Inquiry' => 'General Inquiry',
            'Attendance Issue' => 'Attendance Issue',
            'Leave Request Issue' => 'Leave Request Issue',
            'Schedule Concern' => 'Schedule Concern',
            'Technical Problem' => 'Technical Problem',
            'Suggestion' => 'Suggestion',
            'Complaint' => 'Complaint',
            'Other' => 'Other',
        ],
        'otherSubjectValue' => 'Other',
    ];
@endphp

<div class="row feedback-page">
    <div class="col-12 px-1">
        <div class="card">
            <div class="card-body">
                <h4 class="feedback-title">{{ $formConfig['title'] }}</h4>
                <p class="feedback-subtitle mb-4">{{ $formConfig['subtitle'] }}</p>

                <form method="POST" action="{{ route('employee.feedback.store') }}">
                    @csrf

                    <div class="form-group">
                        <label for="subject">{{ $formConfig['labels']['subject'] }}</label>
                        <select class="form-control" id="subject" name="subject" {{ $formConfig['required']['subject'] ? 'required' : '' }}>
                            <option value="" selected>- Select Subject -</option>
                            @foreach($formConfig['subjectOptions'] as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group" id="other_subject_container" style="display: none;">
                        <label for="other_subject">{{ $formConfig['labels']['other_subject'] }}</label>
                        <input type="text" class="form-control" id="other_subject" name="other_subject" placeholder="Please specify" {{ $formConfig['required']['other_subject'] ? 'required' : '' }}>
                    </div>

                    <div class="form-group">
                        <label for="message">{{ $formConfig['labels']['message'] }}</label>
                        <textarea class="form-control" id="message" name="message" rows="6" placeholder="Describe your feedback, concern, or suggestion in detail..." {{ $formConfig['required']['message'] ? 'required' : '' }}></textarea>
                        <small class="text-muted">Please provide as much detail as possible so we can better assist you.</small>
                    </div>

                    <div class="form-group mb-0">
                        <button type="submit" class="btn theme-btn">
                            <i class="fa fa-paper-plane"></i> Submit Feedback
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="row feedback-page mt-3">
    <div class="col-12 px-1">
        <div class="card">
            <div class="card-body">
                <h4 class="feedback-title mb-4">My Feedback History</h4>

                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Subject</th>
                                <th>Message</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($feedbacks as $feedback)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($feedback->created_at)->format('M d, Y') }}</td>
                                <td>{{ $feedback->subject }}</td>
                                <td>{{ Str::limit($feedback->message, 70) }}</td>
                                <td>
                                    @if($feedback->status == 0)
                                        <span class="theme-badge">Pending</span>
                                    @elseif($feedback->status == 1)
                                        <span class="theme-badge">Read</span>
                                    @else
                                        <span class="theme-badge">Resolved</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center">No feedback submitted yet</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
    var otherSubjectValue = @json((string)($formConfig['otherSubjectValue'] ?? 'Other'));
    var otherSubjectRequired = @json((bool)($formConfig['required']['other_subject'] ?? false));
    var subjectSelect = document.getElementById('subject');
    if (subjectSelect) {
        subjectSelect.addEventListener('change', function() {
            var otherContainer = document.getElementById('other_subject_container');
            var otherInput = document.getElementById('other_subject');
            if (this.value === otherSubjectValue) {
                otherContainer.style.display = 'block';
                if (otherSubjectRequired) {
                    otherInput.setAttribute('required', 'required');
                }
            } else {
                otherContainer.style.display = 'none';
                otherInput.removeAttribute('required');
            }
        });
    }
</script>
@endsection
