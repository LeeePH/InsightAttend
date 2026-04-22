@extends('layouts.master')

@section('css')
    @include('includes.employee_request_form_theme', ['pageWrapperClass' => 'permit-page'])
@endsection

@section('content')
@include('includes.flash')

@php
    $employmentMap = [
        'full_time' => 'Full-time',
        'part_time' => 'Part-time',
        'probationary' => 'Probationary',
        'regular' => 'Regular',
    ];
    $institutionMap = [
        'public' => 'Public',
        'private' => 'Private',
        'review_center' => 'Review Center',
        'others' => 'Others',
    ];
    $levelMap = [
        'basic_ed' => 'Basic Ed',
        'senior_high' => 'Senior High',
        'college' => 'College',
        'graduate' => 'Graduate',
    ];
@endphp

<div class="row permit-page">
    <div class="col-12 px-1">
        <div class="card">
            <div class="card-body">
                <h4 class="page-header-title"><i class="fa fa-file-text-o mr-1"></i> Permit to Teach (Outside School) Application</h4>
                <p class="page-subtitle">Preview of submitted details.</p>

                <div class="table-responsive mt-3">
                    <table class="table table-bordered">
                        <tbody>
                            <tr><th style="width:250px;">Employee Name</th><td>{{ $requestItem->employee->name ?? 'N/A' }}</td></tr>
                            <tr><th>Position / Rank</th><td>{{ $requestItem->position_rank ?: '—' }}</td></tr>
                            <tr><th>Department / School</th><td>{{ $requestItem->department_school ?: '—' }}</td></tr>
                            <tr><th>Employment Status</th><td>{{ $employmentMap[$requestItem->employment_status] ?? '—' }}</td></tr>
                            <tr><th>Name of Other School/Institution</th><td>{{ $requestItem->other_school_name }}</td></tr>
                            <tr><th>School Address</th><td>{{ $requestItem->other_school_address }}</td></tr>
                            <tr><th>Type of Institution</th><td>{{ $institutionMap[$requestItem->institution_type] ?? '—' }}{{ $requestItem->institution_type === 'others' && $requestItem->institution_type_others ? ' - '.$requestItem->institution_type_others : '' }}</td></tr>
                            <tr><th>Subject(s) to be Taught</th><td>{{ $requestItem->subjects_to_teach }}</td></tr>
                            <tr><th>Program / Level</th><td>{{ $levelMap[$requestItem->program_level] ?? '—' }}</td></tr>
                            <tr><th>No. of Units / Hours per Week</th><td>{{ $requestItem->units_or_hours_per_week ?: '—' }}</td></tr>
                            <tr><th>Teaching Schedule</th><td>{!! nl2br(e($requestItem->teaching_schedule ?: '—')) !!}</td></tr>
                            <tr><th>Duration of Engagement</th><td>{{ $requestItem->engagement_from ? \Carbon\Carbon::parse($requestItem->engagement_from)->format('M d, Y') : '—' }} to {{ $requestItem->engagement_to ? \Carbon\Carbon::parse($requestItem->engagement_to)->format('M d, Y') : '—' }}</td></tr>
                            <tr><th>Certification by Applicant</th><td>{{ $requestItem->certification_confirmed ? 'Confirmed' : 'Not confirmed' }}</td></tr>
                            <tr>
                                <th>Status</th>
                                <td>
                                    @if((int)$requestItem->status === 0)<span class="theme-badge">Pending</span>
                                    @elseif((int)$requestItem->status === 1)<span class="theme-badge">Approved</span>
                                    @else <span class="theme-badge">Rejected</span>
                                    @endif
                                </td>
                            </tr>
                            @if($requestItem->remarks)<tr><th>Remarks</th><td>{{ $requestItem->remarks }}</td></tr>@endif
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    @if((int)$requestItem->status === 1)
                    <a href="{{ route('permit_to_teach_outside.approvalLetterPdf', $requestItem->id) }}" class="btn theme-btn"><i class="fa fa-download"></i> Download PDF</a>
                    @endif
                    <a href="{{ url()->previous() }}" class="btn theme-btn ml-2"><i class="fa fa-arrow-left"></i> Back</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
