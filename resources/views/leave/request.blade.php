@extends('layouts.master')

@section('css')
    @include('includes.employee_request_form_theme', ['pageWrapperClass' => 'leave-page'])
@endsection

@section('content')
@php
    $formUi = $formUi ?? [];
    $fieldUi = $fieldUi ?? [];
    $formConfig = $formConfig ?? [
        'title' => 'Request Leave',
        'subtitle' => 'Submit your leave request with complete details for faster approval.',
        'labels' => [
            'leave_date' => 'Start Date',
            'leave_date_end' => 'End Date',
            'type' => 'Type of Leave',
            'other_type' => 'Specify Other Leave Type',
            'reason' => 'Reason',
        ],
        'required' => [
            'leave_date' => true,
            'leave_date_end' => true,
            'type' => true,
            'other_type' => false,
            'reason' => true,
        ],
        'leaveTypeOptions' => [
            '1' => 'Sick Leave',
            '2' => 'Annual Leave',
            '3' => 'Personal Leave',
            '4' => 'Maternity Leave',
            '5' => 'Paternity Leave',
            '6' => 'Vacation Leave',
            '7' => 'Emergency Leave',
            '8' => 'Others (Please Specify)',
        ],
        'otherTypeKey' => '8',
    ];

    $holidayMap = (array) config('holidays.dates', []);
    $holidayDates = array_keys($holidayMap);
@endphp
<div class="row leave-page">
    <div class="col-12 px-1">
        <div class="card">
            <div class="card-body">
                <h4 class="leave-header"><i class="fa fa-calendar mr-1"></i> {{ $formConfig['title'] }}</h4>
                <p class="leave-subtitle">{{ $formConfig['subtitle'] }}</p>

                @if($isEmployee && $currentEmployee)
                    @if(!empty($formUi['show_employee_panel']))
                    <div class="employee-panel mt-4">
                        <h6><i class="fa fa-user mr-1"></i> {{ $formUi['employee_panel_title'] ?? 'Employee Information' }}</h6>
                        <p><strong>Name:</strong> {{ $currentEmployee->name }}</p>
                        <p><strong>Department:</strong> {{ $currentEmployee->department ?? 'N/A' }}</p>
                        <p><strong>Position:</strong> {{ $currentEmployee->position ?? 'N/A' }}</p>
                    </div>
                    @endif

                    <form method="POST" action="{{ route('leave.storeRequest') }}" id="leaveForm" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="emp_id" value="{{ $currentEmployee->id }}">
                        <input type="hidden" name="department" value="{{ $currentEmployee->department }}">
                        <input type="hidden" name="position" value="{{ $currentEmployee->position }}">

                        <div class="row">
                            <div class="{{ data_get($fieldUi, 'leave_date.column_class', 'col-md-6') }}">
                                <div class="form-group">
                                    <label for="leave_date">{{ $formConfig['labels']['leave_date'] }}</label>
                                    <input type="date" class="form-control" id="leave_date" name="leave_date" placeholder="{{ data_get($fieldUi, 'leave_date.placeholder') }}" {{ $formConfig['required']['leave_date'] ? 'required' : '' }}>
                                    @if(data_get($fieldUi, 'leave_date.help_text'))
                                        <small class="text-muted d-block mt-1">{{ data_get($fieldUi, 'leave_date.help_text') }}</small>
                                    @endif
                                </div>
                            </div>
                            <div class="{{ data_get($fieldUi, 'leave_date_end.column_class', 'col-md-6') }}">
                                <div class="form-group">
                                    <label for="leave_date_end">{{ $formConfig['labels']['leave_date_end'] }}</label>
                                    <input type="date" class="form-control" id="leave_date_end" name="leave_date_end" placeholder="{{ data_get($fieldUi, 'leave_date_end.placeholder') }}" {{ $formConfig['required']['leave_date_end'] ? 'required' : '' }}>
                                    @if(data_get($fieldUi, 'leave_date_end.help_text'))
                                        <small class="text-muted d-block mt-1">{{ data_get($fieldUi, 'leave_date_end.help_text') }}</small>
                                    @endif
                                </div>
                            </div>
                        </div>

                        @if(!empty($formUi['show_days_banner']))
                        <div class="days-display" id="daysDisplay">
                            <small>Number of Leave Days</small>
                            <h3 id="leaveDaysCount">0</h3>
                        </div>
                        @endif

                        <div class="form-group mt-3">
                            <div class="{{ data_get($fieldUi, 'type.column_class', 'col-12') }} px-0">
                            <label for="type">{{ $formConfig['labels']['type'] }}</label>
                            <select class="form-control" id="type" name="type" {{ $formConfig['required']['type'] ? 'required' : '' }}>
                                <option value="" selected>- Select Leave Type -</option>
                                @foreach($formConfig['leaveTypeOptions'] as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            @if(data_get($fieldUi, 'type.help_text'))
                                <small class="text-muted d-block mt-1">{{ data_get($fieldUi, 'type.help_text') }}</small>
                            @endif
                            </div>
                        </div>

                        <div class="form-group" id="other_type_container" style="display: none;">
                            <div class="{{ data_get($fieldUi, 'other_type.column_class', 'col-12') }} px-0">
                            <label for="other_type">{{ $formConfig['labels']['other_type'] }}</label>
                            <input type="text" class="form-control" id="other_type" name="other_type" placeholder="{{ data_get($fieldUi, 'other_type.placeholder', 'Please specify') }}" {{ $formConfig['required']['other_type'] ? 'required' : '' }}>
                            @if(data_get($fieldUi, 'other_type.help_text'))
                                <small class="text-muted d-block mt-1">{{ data_get($fieldUi, 'other_type.help_text') }}</small>
                            @endif
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="{{ data_get($fieldUi, 'reason.column_class', 'col-12') }} px-0">
                            <label for="reason">{{ $formConfig['labels']['reason'] }}</label>
                            <textarea class="form-control" id="reason" name="reason" rows="4" placeholder="{{ data_get($fieldUi, 'reason.placeholder', 'Enter reason for leave') }}" {{ $formConfig['required']['reason'] ? 'required' : '' }}></textarea>
                            @if(data_get($fieldUi, 'reason.help_text'))
                                <small class="text-muted d-block mt-2">{{ data_get($fieldUi, 'reason.help_text') }}</small>
                            @endif
                            @if(!empty(trim((string) ($formUi['reason_footer_note'] ?? ''))))
                            <small class="text-muted d-block mt-2">
                                {{ $formUi['reason_footer_note'] }}
                            </small>
                            @endif
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="supporting_documents">Supporting documents or images <span class="text-muted font-weight-normal">(required for most leave types)</span></label>
                            <input type="file" class="form-control-file" id="supporting_documents" name="supporting_documents[]" accept=".pdf,.doc,.docx,image/*" multiple>
                            <small class="text-muted d-block mt-1">Up to 5 files. Images or PDF/DOC up to 10&nbsp;MB each. Sick Leave requires at least one image.</small>
                        </div>

                        <div class="form-group mb-0">
                            <button type="submit" class="btn theme-btn">
                                <i class="fa fa-paper-plane"></i> {{ $formUi['submit_label'] ?? 'Submit Request' }}
                            </button>
                            <a href="{{ route('employee.dashboard') }}" class="btn theme-btn ml-2">
                                <i class="fa fa-home"></i> {{ $formUi['back_label_employee'] ?? 'Back to Dashboard' }}
                            </a>
                        </div>
                    </form>

                    <div class="mt-4">
                        <h5 class="leave-header mb-3">My Leave History</h5>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Start Date</th>
                                        <th>End Date</th>
                                        <th>Days</th>
                                        <th>Type</th>
                                        <th>Requested Time</th>
                                        <th>Applied At</th>
                                        <th>Status</th>
                                        <th>Attachments</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($leaveHistory as $history)
                                    <tr>
                                        <td>{{ \Carbon\Carbon::parse($history->leave_date)->format('M d, Y') }}</td>
                                        <td>{{ $history->leave_date_end ? \Carbon\Carbon::parse($history->leave_date_end)->format('M d, Y') : '-' }}</td>
                                        <td>{{ $history->leave_days ?? 1 }}</td>
                                        <td>
                                            {{ $formConfig['leaveTypeOptions'][(string)$history->type] ?? ($formConfig['leaveTypeOptions'][$history->type] ?? 'Other') }}
                                        </td>
                                        <td>{{ $history->leave_time ? \Carbon\Carbon::parse($history->leave_time)->format('h:i A') : '-' }}</td>
                                        <td>{{ \Carbon\Carbon::parse($history->created_at)->format('M d, Y h:i A') }}</td>
                                        <td>
                                            @if($history->status == 0)
                                                <span class="theme-badge">Pending</span>
                                            @elseif($history->status == 1)
                                                <span class="theme-badge">Approved</span>
                                            @else
                                                <span class="theme-badge">Rejected</span>
                                            @endif
                                        </td>
                                        <td class="text-nowrap">
                                            @php $docUrls = $history->supportingDocumentUrls(); @endphp
                                            @if(count($docUrls))
                                                @foreach($docUrls as $i => $url)
                                                    <a href="{{ $url }}" target="_blank" rel="noopener">View {{ $i + 1 }}</a>@if(!$loop->last)<br>@endif
                                                @endforeach
                                            @else
                                                —
                                            @endif
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="8" class="text-center">No leave requests submitted yet</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                @else
                    <form method="POST" action="{{ route('leave.storeRequest') }}" id="leaveForm" enctype="multipart/form-data">
                        @csrf

                        <div class="row mt-3">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="employee_name">Employee Name</label>
                                    <select class="form-control" id="employee_name" name="emp_id" required>
                                        <option value="" selected>- Select Employee -</option>
                                        @foreach($employees as $employee)
                                            <option value="{{ $employee->id }}" data-position="{{ $employee->position ?? '' }}" data-department="{{ $employee->department ?? '' }}">{{ $employee->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="department">Department</label>
                                    <select class="form-control" id="department" name="department" required>
                                        <option value="" selected>- Select Department -</option>
                                        <option value="Bachelor of Science in Information Technology">Bachelor of Science in Information Technology</option>
                                        <option value="Bachelor of Science in Hospitality Management">Bachelor of Science in Hospitality Management</option>
                                        <option value="Bachelor of Science in Tourism Management">Bachelor of Science in Tourism Management</option>
                                        <option value="Bachelor of Secondary Education - English">Bachelor of Secondary Education - English</option>
                                        <option value="Bachelor of Secondary Education - Filipino">Bachelor of Secondary Education - Filipino</option>
                                        <option value="Bachelor of Secondary Education - Mathematics">Bachelor of Secondary Education - Mathematics</option>
                                        <option value="Bachelor of Secondary Education - Social Science">Bachelor of Secondary Education - Social Science</option>
                                        <option value="Bachelor of Elementary Education">Bachelor of Elementary Education</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="position">Position</label>
                                    <input type="text" class="form-control" id="position" name="position" placeholder="Enter Position" required>
                                </div>
                            </div>
                            <div class="{{ data_get($fieldUi, 'leave_date.column_class', 'col-md-6') }}">
                                <div class="form-group">
                                    <label for="leave_date">{{ $formConfig['labels']['leave_date'] }}</label>
                                    <input type="date" class="form-control" id="leave_date" name="leave_date" placeholder="{{ data_get($fieldUi, 'leave_date.placeholder') }}" {{ $formConfig['required']['leave_date'] ? 'required' : '' }}>
                                    @if(data_get($fieldUi, 'leave_date.help_text'))
                                        <small class="text-muted d-block mt-1">{{ data_get($fieldUi, 'leave_date.help_text') }}</small>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="{{ data_get($fieldUi, 'leave_date_end.column_class', 'col-md-6') }}">
                                <div class="form-group">
                                    <label for="leave_date_end">{{ $formConfig['labels']['leave_date_end'] }}</label>
                                    <input type="date" class="form-control" id="leave_date_end" name="leave_date_end" placeholder="{{ data_get($fieldUi, 'leave_date_end.placeholder') }}" {{ $formConfig['required']['leave_date_end'] ? 'required' : '' }}>
                                    @if(data_get($fieldUi, 'leave_date_end.help_text'))
                                        <small class="text-muted d-block mt-1">{{ data_get($fieldUi, 'leave_date_end.help_text') }}</small>
                                    @endif
                                </div>
                            </div>
                        </div>

                        @if(!empty($formUi['show_days_banner']))
                        <div class="days-display" id="daysDisplay">
                            <small>Number of Leave Days</small>
                            <h3 id="leaveDaysCount">0</h3>
                        </div>
                        @endif

                        <div class="form-group mt-3">
                            <div class="{{ data_get($fieldUi, 'type.column_class', 'col-12') }} px-0">
                            <label for="type">{{ $formConfig['labels']['type'] }}</label>
                            <select class="form-control" id="type" name="type" {{ $formConfig['required']['type'] ? 'required' : '' }}>
                                <option value="" selected>- Select Leave Type -</option>
                                @foreach($formConfig['leaveTypeOptions'] as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            @if(data_get($fieldUi, 'type.help_text'))
                                <small class="text-muted d-block mt-1">{{ data_get($fieldUi, 'type.help_text') }}</small>
                            @endif
                            </div>
                        </div>

                        <div class="form-group" id="other_type_container" style="display: none;">
                            <div class="{{ data_get($fieldUi, 'other_type.column_class', 'col-12') }} px-0">
                            <label for="other_type">{{ $formConfig['labels']['other_type'] }}</label>
                            <input type="text" class="form-control" id="other_type" name="other_type" placeholder="{{ data_get($fieldUi, 'other_type.placeholder', 'Please specify') }}" {{ $formConfig['required']['other_type'] ? 'required' : '' }}>
                            @if(data_get($fieldUi, 'other_type.help_text'))
                                <small class="text-muted d-block mt-1">{{ data_get($fieldUi, 'other_type.help_text') }}</small>
                            @endif
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="{{ data_get($fieldUi, 'reason.column_class', 'col-12') }} px-0">
                            <label for="reason">{{ $formConfig['labels']['reason'] }}</label>
                            <textarea class="form-control" id="reason" name="reason" rows="4" placeholder="{{ data_get($fieldUi, 'reason.placeholder', 'Enter reason for leave') }}" {{ $formConfig['required']['reason'] ? 'required' : '' }}></textarea>
                            @if(data_get($fieldUi, 'reason.help_text'))
                                <small class="text-muted d-block mt-2">{{ data_get($fieldUi, 'reason.help_text') }}</small>
                            @endif
                            @if(!empty(trim((string) ($formUi['reason_footer_note'] ?? ''))))
                            <small class="text-muted d-block mt-2">
                                {{ $formUi['reason_footer_note'] }}
                            </small>
                            @endif
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="supporting_documents_guest">Supporting documents or images <span class="text-muted font-weight-normal">(optional)</span></label>
                            <input type="file" class="form-control-file" id="supporting_documents_guest" name="supporting_documents[]" accept=".pdf,.doc,.docx,image/*" multiple>
                            <small class="text-muted d-block mt-1">Up to 5 files. Images or PDF/DOC up to 10&nbsp;MB each.</small>
                        </div>

                        <div class="form-group mb-0">
                            <button type="submit" class="btn theme-btn">
                                <i class="fa fa-paper-plane"></i> {{ $formUi['submit_label'] ?? 'Submit Request' }}
                            </button>
                            <a href="{{ route('welcome') }}" class="btn theme-btn ml-2">
                                <i class="fa fa-home"></i> {{ $formUi['back_label_guest'] ?? 'Back to Home' }}
                            </a>
                        </div>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
    var otherTypeValue = @json((string)($formConfig['otherTypeKey'] ?? '8'));
    var otherTypeRequired = @json((bool)($formConfig['required']['other_type'] ?? false));

    function calculateDays() {
        var countEl = document.getElementById('leaveDaysCount');
        if (!countEl) {
            return;
        }
        var startDate = document.getElementById('leave_date').value;
        var endDate = document.getElementById('leave_date_end').value;

        if (startDate && endDate) {
            var start = new Date(startDate);
            var end = new Date(endDate);

            if (end >= start) {
                var diffTime = Math.abs(end - start);
                var diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
                countEl.textContent = diffDays;
            } else {
                countEl.textContent = '0';
            }
        } else {
            countEl.textContent = '0';
        }
    }

    var leaveDateInput = document.getElementById('leave_date');
    var leaveDateEndInput = document.getElementById('leave_date_end');
    if (leaveDateInput) leaveDateInput.addEventListener('change', calculateDays);
    if (leaveDateEndInput) leaveDateEndInput.addEventListener('change', calculateDays);

    var typeSelect = document.getElementById('type');
    if (typeSelect) {
        typeSelect.addEventListener('change', function() {
            var otherTypeContainer = document.getElementById('other_type_container');
            var otherTypeInput = document.getElementById('other_type');
            if (this.value === otherTypeValue) {
                otherTypeContainer.style.display = 'block';
                if (otherTypeRequired) {
                    otherTypeInput.setAttribute('required', 'required');
                }
            } else {
                otherTypeContainer.style.display = 'none';
                otherTypeInput.removeAttribute('required');
            }
        });
    }

    var employeeSelect = document.getElementById('employee_name');
    if (employeeSelect) {
        employeeSelect.addEventListener('change', function() {
            var selected = this.options[this.selectedIndex];
            var positionInput = document.getElementById('position');
            var departmentSelect = document.getElementById('department');

            if (positionInput) positionInput.value = selected.getAttribute('data-position') || '';
            if (departmentSelect) departmentSelect.value = selected.getAttribute('data-department') || '';
        });
    }

    (function () {
        var holidayMap = @json($holidayMap);
        var holidayDates = @json($holidayDates);
        var leaveDateInput = document.getElementById('leave_date');
        var leaveDateEndInput = document.getElementById('leave_date_end');
        var typeSelect = document.getElementById('type');
        var docsInput = document.getElementById('supporting_documents');
        var docsLabel = document.querySelector('label[for="supporting_documents"] span');

        function isHoliday(ymd) {
            return !!holidayMap[ymd];
        }

        function blockIfHoliday(inputEl) {
            if (!inputEl || !inputEl.value) return false;
            var ymd = inputEl.value;
            if (isHoliday(ymd)) {
                alert('Selected date is a holiday: ' + holidayMap[ymd] + '. You cannot file leave on this day.');
                inputEl.value = '';
                calculateDays();
                return true;
            }
            return false;
        }

        if (leaveDateInput) {
            leaveDateInput.addEventListener('change', function () {
                blockIfHoliday(leaveDateInput);
            });
        }
        if (leaveDateEndInput) {
            leaveDateEndInput.addEventListener('change', function () {
                blockIfHoliday(leaveDateEndInput);
            });
        }

        // Toggle docs hint based on leave type (personal/vacation optional)
        function refreshDocsRequirementHint() {
            if (!typeSelect || !docsInput) return;
            var t = String(typeSelect.value || '');
            var optional = (t === '3' || t === '6'); // personal or vacation
            docsInput.required = !optional;
        }
        if (typeSelect) {
            typeSelect.addEventListener('change', refreshDocsRequirementHint);
            refreshDocsRequirementHint();
        }
    })();
</script>
@endsection
