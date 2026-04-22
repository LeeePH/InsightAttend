@extends('layouts.master')

@section('css')
    @include('includes.employee_request_form_theme', ['pageWrapperClass' => 'loan-page'])
@endsection

@section('content')
@include('includes.flash')
@php
    /** @var \App\Models\LoanRequest $requestItem */
    $flags = is_array($requestItem->purpose_flags) ? $requestItem->purpose_flags : [];
    $purposeLabels = [
        'hospitalization' => 'Hospitalization/Medication',
        'calamity' => 'Emergency house repair due to calamity',
        'bereavement' => 'Bereavement',
        'tuition' => 'Tuition Fee',
        'dental' => 'Dental',
        'other' => 'Other',
    ];
@endphp

<div class="row loan-page">
    <div class="col-12 px-1">
        <div class="card">
            <div class="card-body">
                <h4 class="page-header-title"><i class="fa fa-file-text-o mr-1"></i> Company Loan Application</h4>
                <p class="page-subtitle">Preview of submitted details.</p>

                <div class="employee-panel mt-4">
                    <h6><i class="fa fa-user mr-1"></i> Employee Information</h6>
                    <p><strong>Name:</strong> {{ $requestItem->employee->name ?? ('Employee #' . $requestItem->emp_id) }}</p>
                    <p><strong>Department:</strong> {{ $requestItem->employee->department ?? 'N/A' }}</p>
                    <p><strong>Position:</strong> {{ $requestItem->employee->position ?? 'N/A' }}</p>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered">
                        <tbody>
                            <tr>
                                <th style="width: 220px;">Date Filed</th>
                                <td>{{ \Carbon\Carbon::parse($requestItem->date_filed)->format('M d, Y') }}</td>
                            </tr>
                            <tr>
                                <th>Civil Status</th>
                                <td>{{ $requestItem->civil_status === 'married' ? 'Married' : 'Single' }}</td>
                            </tr>
                            <tr>
                                <th>Contact number</th>
                                <td>{{ $requestItem->contact_number ?? '—' }}</td>
                            </tr>
                            <tr>
                                <th>Hire Date</th>
                                <td>{{ $requestItem->hire_date ? \Carbon\Carbon::parse($requestItem->hire_date)->format('M d, Y') : '—' }}</td>
                            </tr>
                            <tr>
                                <th>Amount requested</th>
                                <td>P {{ number_format((float) $requestItem->amount_requested, 2) }}</td>
                            </tr>
                            <tr>
                                <th>Purposes</th>
                                <td>
                                    @foreach($purposeLabels as $k => $label)
                                        @if(!empty($flags[$k]))
                                            <div>- {{ $label }}@if($k === 'other' && $requestItem->other_purpose) ({{ $requestItem->other_purpose }})@endif</div>
                                        @endif
                                    @endforeach
                                </td>
                            </tr>
                            @if(!empty($flags['hospitalization']))
                            <tr>
                                <th>Hospitalization details</th>
                                <td>
                                    <div><strong>Patient:</strong> {{ $requestItem->hospital_patient_name ?? '—' }}</div>
                                    <div><strong>Relationship:</strong> {{ $requestItem->hospital_relationship ?? '—' }}</div>
                                    <div><strong>Age:</strong> {{ $requestItem->hospital_age ?? '—' }}</div>
                                </td>
                            </tr>
                            @endif
                            @if(!empty($flags['calamity']))
                            <tr>
                                <th>Calamity details</th>
                                <td>{{ $requestItem->calamity_details ?? '—' }}</td>
                            </tr>
                            @endif
                            @if(!empty($flags['bereavement']))
                            <tr>
                                <th>Bereavement relationship</th>
                                <td>{{ $requestItem->bereavement_relationship ?? '—' }}</td>
                            </tr>
                            @endif
                            @if(!empty($flags['tuition']))
                            <tr>
                                <th>Tuition details</th>
                                <td>
                                    <div><strong>Child Name:</strong> {{ $requestItem->tuition_child_name ?? '—' }}</div>
                                    <div><strong>Age:</strong> {{ $requestItem->tuition_child_age ?? '—' }}</div>
                                    <div><strong>Level:</strong> {{ $requestItem->tuition_child_level ?? '—' }}</div>
                                </td>
                            </tr>
                            @endif
                            @if(!empty($flags['dental']))
                            <tr>
                                <th>Dental details</th>
                                <td>
                                    <div><strong>Patient:</strong> {{ $requestItem->dental_patient_name ?? '—' }}</div>
                                    <div><strong>Relationship:</strong> {{ $requestItem->dental_relationship ?? '—' }}</div>
                                    <div><strong>Age:</strong> {{ $requestItem->dental_age ?? '—' }}</div>
                                </td>
                            </tr>
                            @endif
                            <tr>
                                <th>Status</th>
                                <td>
                                    @if((int)$requestItem->status === 0)
                                        <span class="theme-badge">Pending</span>
                                    @elseif((int)$requestItem->status === 1)
                                        <span class="theme-badge">Approved</span>
                                    @else
                                        <span class="theme-badge">Rejected</span>
                                    @endif
                                </td>
                            </tr>
                            @if($requestItem->remarks)
                            <tr>
                                <th>Admin remarks</th>
                                <td>{{ $requestItem->remarks }}</td>
                            </tr>
                            @endif
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    <h6 class="mb-2">Attachments</h6>
                    @php $paths = is_array($requestItem->supporting_documents) ? $requestItem->supporting_documents : []; @endphp
                    @if(count($paths))
                        @foreach($paths as $i => $p)
                            <a class="d-inline-block mr-3 mb-2" href="{{ route('loan.attachment', ['id' => $requestItem->id, 'index' => $i]) }}" target="_blank" rel="noopener">
                                View file {{ $i + 1 }}
                            </a>
                        @endforeach
                    @else
                        <div class="text-muted">—</div>
                    @endif
                </div>

                <div class="mt-4">
                    @if((int)$requestItem->status === 1)
                        <a href="{{ route('loan.approvalLetterPdf', $requestItem->id) }}" class="btn theme-btn">
                            <i class="fa fa-download"></i> Download PDF
                        </a>
                    @endif
                    <a href="{{ url()->previous() }}" class="btn theme-btn ml-2">
                        <i class="fa fa-arrow-left"></i> Back
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

