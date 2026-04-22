@extends('layouts.master')

@section('css')
    @include('includes.employee_request_form_theme', ['pageWrapperClass' => 'discount-page'])
@endsection

@section('content')
@include('includes.flash')

@php
    $schoolMap = ['stsn' => 'STSN', 'csta' => 'CSTA'];
    $employmentMap = ['probationary' => 'Probationary', 'regular' => 'Regular'];
    $deptMap = ['grade_school' => 'Grade School', 'junior_high' => 'Junior High School', 'senior_high' => 'Senior High School', 'college' => 'College'];
@endphp

<div class="row discount-page">
    <div class="col-12 px-1">
        <div class="card">
            <div class="card-body">
                <h4 class="page-header-title"><i class="fa fa-file-text-o mr-1"></i> Application for Discount</h4>
                <p class="page-subtitle">Preview of submitted details.</p>

                <div class="table-responsive mt-3">
                    <table class="table table-bordered">
                        <tbody>
                            <tr><th style="width:220px;">Employee</th><td>{{ $requestItem->employee->name ?? ('Employee #' . $requestItem->emp_id) }}</td></tr>
                            <tr><th>Date Request</th><td>{{ \Carbon\Carbon::parse($requestItem->date_request)->format('M d, Y') }}</td></tr>
                            <tr><th>Term/Semester</th><td>{{ $requestItem->term_semester }}</td></tr>
                            <tr><th>School Year</th><td>{{ $requestItem->school_year }}</td></tr>
                            <tr><th>Department</th><td>{{ $requestItem->employee_department }}</td></tr>
                            <tr><th>Position</th><td>{{ $requestItem->employee_position }}</td></tr>
                            <tr><th>Date Hire</th><td>{{ $requestItem->date_hire ? \Carbon\Carbon::parse($requestItem->date_hire)->format('M d, Y') : '—' }}</td></tr>
                            <tr><th>Employment Status</th><td>{{ $employmentMap[$requestItem->employment_status] ?? '—' }}</td></tr>
                            <tr><th>School</th><td>{{ $schoolMap[$requestItem->school] ?? '—' }}</td></tr>
                            <tr><th>Student</th><td>{{ $requestItem->student_name }} ({{ $requestItem->student_no }})</td></tr>
                            <tr><th>Track/Program</th><td>{{ $requestItem->track_program }}</td></tr>
                            <tr><th>Grade/Year Level</th><td>{{ $requestItem->grade_year_level }}</td></tr>
                            <tr><th>Student Department</th><td>{{ $deptMap[$requestItem->student_department] ?? '—' }}</td></tr>
                            <tr><th>Discount Applied</th><td>{{ $requestItem->discount_applied === 'family_relative' ? 'Family Relative' : 'Employee Privileges' }}</td></tr>
                            @if($requestItem->family_relationship)<tr><th>Relationship</th><td>{{ $requestItem->family_relationship }}</td></tr>@endif
                            @if($requestItem->privilege_child_order)<tr><th>Child Order</th><td>{{ $requestItem->privilege_child_order }}</td></tr>@endif
                            @if($requestItem->tuition_fee_discount_percent !== null)<tr><th>HR Discount</th><td>{{ number_format((float)$requestItem->tuition_fee_discount_percent, 2) }}%</td></tr>@endif
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
                    <h6>Attachments</h6>
                    @php $paths = is_array($requestItem->supporting_documents) ? $requestItem->supporting_documents : []; @endphp
                    @if(count($paths))
                        @foreach($paths as $i => $p)
                            <a class="d-inline-block mr-3 mb-2" href="{{ route('discount.attachment', ['id' => $requestItem->id, 'index' => $i]) }}" target="_blank" rel="noopener">View file {{ $i + 1 }}</a>
                        @endforeach
                    @else
                        <div class="text-muted">—</div>
                    @endif
                </div>

                <div class="mt-3">
                    @if((int)$requestItem->status === 1)
                    <a href="{{ route('discount.approvalLetterPdf', $requestItem->id) }}" class="btn theme-btn">
                        <i class="fa fa-download"></i> Download PDF
                    </a>
                    @endif
                    <a href="{{ url()->previous() }}" class="btn theme-btn ml-2"><i class="fa fa-arrow-left"></i> Back</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

