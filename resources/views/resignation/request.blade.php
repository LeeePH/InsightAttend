@extends('layouts.master')

@section('css')
    @include('includes.employee_request_form_theme', ['pageWrapperClass' => 'resignation-page'])
@endsection

@section('content')
@include('includes.flash')
@php
    $formUi = $formUi ?? [];
    $fieldUi = $fieldUi ?? [];
    $formConfig = $formConfig ?? [
        'title' => 'Resignation Request',
        'subtitle' => 'Submit your formal resignation request and transition details.',
        'labels' => [
            'last_working_day' => 'Intended Last Working Day',
            'reason' => 'Reason for Resignation',
            'handover_notes' => 'Handover Notes (Optional)',
            'acknowledgement' => 'I confirm that the details provided are true and I understand this will be submitted for review.',
        ],
        'required' => [
            'last_working_day' => true,
            'reason' => true,
            'handover_notes' => false,
            'acknowledgement' => true,
        ],
    ];
@endphp

<div class="row resignation-page">
    <div class="col-12 px-1">
        <div class="card">
            <div class="card-body">
                <h4 class="page-header-title"><i class="fa fa-file-text-o mr-1"></i> {{ $formConfig['title'] }}</h4>
                <p class="page-subtitle">{{ $formConfig['subtitle'] }}</p>

                @if($isEmployee && $currentEmployee)
                    @if(!empty($formUi['show_employee_panel']))
                    <div class="employee-panel">
                        <h6><i class="fa fa-user mr-1"></i> {{ $formUi['employee_panel_title'] ?? 'Employee Information' }}</h6>
                        <p><strong>Name:</strong> {{ $currentEmployee->name }}</p>
                        <p><strong>Department:</strong> {{ $currentEmployee->department ?? 'N/A' }}</p>
                        <p><strong>Position:</strong> {{ $currentEmployee->position ?? 'N/A' }}</p>
                    </div>
                    @endif

                    @if(!empty($formUi['show_notice_box']) && !empty(trim((string) ($formUi['notice_text'] ?? ''))))
                    <div class="notice-box">
                        <strong>Note:</strong> {{ $formUi['notice_text'] }}
                    </div>
                    @endif

                    <form method="POST" action="{{ route('resignation.storeRequest') }}">
                        @csrf

                        <div class="row">
                            <div class="{{ data_get($fieldUi, 'last_working_day.column_class', 'col-md-6') }}">
                                <div class="form-group">
                                    <label for="last_working_day">{{ $formConfig['labels']['last_working_day'] }}</label>
                                    <input type="date" class="form-control" id="last_working_day" name="last_working_day" placeholder="{{ data_get($fieldUi, 'last_working_day.placeholder') }}" {{ $formConfig['required']['last_working_day'] ? 'required' : '' }}>
                                    @if(data_get($fieldUi, 'last_working_day.help_text'))
                                        <small class="text-muted d-block mt-1">{{ data_get($fieldUi, 'last_working_day.help_text') }}</small>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="{{ data_get($fieldUi, 'reason.column_class', 'col-12') }} px-0">
                                <label for="reason">{{ $formConfig['labels']['reason'] }}</label>
                                <textarea class="form-control" id="reason" name="reason" rows="5" placeholder="{{ data_get($fieldUi, 'reason.placeholder', 'Enter your reason for resignation') }}" {{ $formConfig['required']['reason'] ? 'required' : '' }}></textarea>
                                @if(data_get($fieldUi, 'reason.help_text'))
                                    <small class="text-muted d-block mt-1">{{ data_get($fieldUi, 'reason.help_text') }}</small>
                                @endif
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="{{ data_get($fieldUi, 'handover_notes.column_class', 'col-12') }} px-0">
                                <label for="handover_notes">{{ $formConfig['labels']['handover_notes'] }}</label>
                                <textarea class="form-control" id="handover_notes" name="handover_notes" rows="4" placeholder="{{ data_get($fieldUi, 'handover_notes.placeholder', 'Include key tasks, files, and pending responsibilities') }}" {{ $formConfig['required']['handover_notes'] ? 'required' : '' }}></textarea>
                                @if(data_get($fieldUi, 'handover_notes.help_text'))
                                    <small class="text-muted d-block mt-1">{{ data_get($fieldUi, 'handover_notes.help_text') }}</small>
                                @endif
                            </div>
                        </div>

                        <div class="form-group form-check">
                            <div class="{{ data_get($fieldUi, 'acknowledgement.column_class', 'col-12') }} px-0">
                            <input type="checkbox" class="form-check-input" id="acknowledgement" name="acknowledgement" value="1" {{ $formConfig['required']['acknowledgement'] ? 'required' : '' }}>
                            <label class="form-check-label" for="acknowledgement">
                                {{ $formConfig['labels']['acknowledgement'] }}
                            </label>
                            @if(data_get($fieldUi, 'acknowledgement.help_text'))
                                <small class="text-muted d-block mt-1">{{ data_get($fieldUi, 'acknowledgement.help_text') }}</small>
                            @endif
                            </div>
                        </div>

                        <div class="form-group mb-0">
                            <button type="submit" class="btn theme-btn">
                                <i class="fa fa-paper-plane"></i> {{ $formUi['submit_label'] ?? 'Submit Resignation Request' }}
                            </button>
                            <a href="{{ route('employee.dashboard') }}" class="btn theme-btn ml-2">
                                <i class="fa fa-home"></i> {{ $formUi['back_label_employee'] ?? 'Back to Dashboard' }}
                            </a>
                        </div>
                    </form>
                @else
                    <div class="alert alert-warning mb-0">
                        Employee profile was not found for your account. Please contact the administrator.
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
@endsection
