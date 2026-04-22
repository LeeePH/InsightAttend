@extends('layouts.master')

@section('css')
    @include('includes.employee_request_form_theme', ['pageWrapperClass' => 'loan-page'])
@endsection

@section('content')
@include('includes.flash')
@php
    $formUi = $formUi ?? [];
    $fieldUi = $fieldUi ?? [];
    $formConfig = $formConfig ?? [];
    $purposeOptions = $formConfig['purposeOptions'] ?? [];
    $today = \Carbon\Carbon::now()->toDateString();
@endphp

<div class="row loan-page">
    <div class="col-12 px-1">
        <div class="card">
            <div class="card-body">
                <h4 class="page-header-title"><i class="fa fa-money mr-1"></i> {{ $formConfig['title'] ?? 'Company Loan Application' }}</h4>
                <p class="page-subtitle">{{ $formConfig['subtitle'] ?? 'Submit your loan application with complete details for faster processing.' }}</p>

                @if($isEmployee && $currentEmployee)
                    @if(!empty($formUi['show_employee_panel']))
                    <div class="employee-panel mt-4">
                        <h6><i class="fa fa-user mr-1"></i> {{ $formUi['employee_panel_title'] ?? 'Employee Information' }}</h6>
                        <p><strong>Name:</strong> {{ $currentEmployee->name }}</p>
                        <p><strong>Employee Number:</strong> {{ $currentEmployee->id }}</p>
                        <p><strong>Department:</strong> {{ $currentEmployee->department ?? 'N/A' }}</p>
                        <p><strong>Position:</strong> {{ $currentEmployee->position ?? 'N/A' }}</p>
                    </div>
                    @endif

                    @if(!empty($formUi['show_notice_box']) && !empty(trim((string) ($formUi['notice_text'] ?? ''))))
                    <div class="notice-box">
                        <strong>Note:</strong> {{ $formUi['notice_text'] }}
                    </div>
                    @endif

                    <form method="POST" action="{{ route('loan.storeRequest') }}" enctype="multipart/form-data" id="loanForm">
                        @csrf

                        <div class="row">
                            <div class="{{ data_get($fieldUi, 'date_filed.column_class', 'col-md-6') }}">
                                <div class="form-group">
                                    <label for="date_filed">{{ data_get($formConfig, 'labels.date_filed', 'Date Filed') }}</label>
                                    <input type="date" class="form-control" id="date_filed" name="date_filed" value="{{ old('date_filed', $today) }}" {{ data_get($formConfig, 'required.date_filed') ? 'required' : '' }}>
                                </div>
                            </div>
                            <div class="{{ data_get($fieldUi, 'civil_status.column_class', 'col-md-6') }}">
                                <div class="form-group">
                                    <label for="civil_status">{{ data_get($formConfig, 'labels.civil_status', 'Civil Status') }}</label>
                                    <select class="form-control" id="civil_status" name="civil_status" {{ data_get($formConfig, 'required.civil_status') ? 'required' : '' }}>
                                        <option value="" {{ old('civil_status') ? '' : 'selected' }}>- Select -</option>
                                        <option value="single" {{ old('civil_status') === 'single' ? 'selected' : '' }}>Single</option>
                                        <option value="married" {{ old('civil_status') === 'married' ? 'selected' : '' }}>Married</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="{{ data_get($fieldUi, 'contact_number.column_class', 'col-md-6') }}">
                                <div class="form-group">
                                    <label for="contact_number">{{ data_get($formConfig, 'labels.contact_number', 'Contact number') }}</label>
                                    <input type="text" class="form-control" id="contact_number" name="contact_number" value="{{ old('contact_number', $currentEmployee->phone ?? '') }}" placeholder="{{ data_get($fieldUi, 'contact_number.placeholder', 'Enter contact number') }}" {{ data_get($formConfig, 'required.contact_number') ? 'required' : '' }}>
                                </div>
                            </div>
                            <div class="{{ data_get($fieldUi, 'hire_date.column_class', 'col-md-6') }}">
                                <div class="form-group">
                                    <label for="hire_date">{{ data_get($formConfig, 'labels.hire_date', 'Hire Date') }}</label>
                                    <input type="date" class="form-control" id="hire_date" name="hire_date" value="{{ old('hire_date') }}" {{ data_get($formConfig, 'required.hire_date') ? 'required' : '' }}>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="{{ data_get($fieldUi, 'amount_requested.column_class', 'col-md-6') }}">
                                <div class="form-group">
                                    <label for="amount_requested">{{ data_get($formConfig, 'labels.amount_requested', 'Amount requested by employee') }}</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">₱</span>
                                        </div>
                                        <input type="number" class="form-control" id="amount_requested" name="amount_requested" step="0.01" min="0" value="{{ old('amount_requested') }}" placeholder="{{ data_get($fieldUi, 'amount_requested.placeholder', '0.00') }}" {{ data_get($formConfig, 'required.amount_requested') ? 'required' : '' }}>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group mt-2">
                            <label>{{ data_get($formConfig, 'labels.purpose', 'Below are the valid reasons for availing company loan. Please check the purpose of your loan application and attach supporting documents.') }}</label>

                            @php
                                $oldPurposes = (array) old('purpose', []);
                                $checked = function ($key) use ($oldPurposes) { return in_array($key, $oldPurposes, true); };
                            @endphp

                            <div class="mt-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="purpose_hospitalization" name="purpose[]" value="hospitalization" {{ $checked('hospitalization') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="purpose_hospitalization">
                                        {{ $purposeOptions['hospitalization'] ?? 'Hospitalization/Medication' }}
                                        <small class="text-muted d-block">Emergency treatment or hospitalization of employee or qualified dependent</small>
                                    </label>
                                </div>
                                <div class="row mt-2" id="section_hospitalization" style="display:none;">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="hospital_patient_name">Patient's Name</label>
                                            <input type="text" class="form-control" id="hospital_patient_name" name="hospital_patient_name" value="{{ old('hospital_patient_name') }}">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="hospital_relationship">Relationship</label>
                                            <input type="text" class="form-control" id="hospital_relationship" name="hospital_relationship" value="{{ old('hospital_relationship') }}">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="hospital_age">Age</label>
                                            <input type="number" class="form-control" id="hospital_age" name="hospital_age" min="0" max="120" value="{{ old('hospital_age') }}">
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <small class="text-muted d-block mb-2">Attach: Hospital bills / discharge sheet / doctor's prescription / medical certificate</small>
                                    </div>
                                </div>

                                <hr class="my-3">

                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="purpose_calamity" name="purpose[]" value="calamity" {{ $checked('calamity') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="purpose_calamity">
                                        {{ $purposeOptions['calamity'] ?? 'Emergency house repair due to calamity' }}
                                        <small class="text-muted d-block">House must be owned by employee</small>
                                    </label>
                                </div>
                                <div class="mt-2" id="section_calamity" style="display:none;">
                                    <div class="form-group">
                                        <label for="calamity_details">Details</label>
                                        <textarea class="form-control" id="calamity_details" name="calamity_details" rows="3" placeholder="Briefly describe the damage and needed repairs">{{ old('calamity_details') }}</textarea>
                                        <small class="text-muted d-block mt-1">Attach: Proof of ownership, pictures showing damage, cost estimate of repairs</small>
                                    </div>
                                </div>

                                <hr class="my-3">

                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="purpose_bereavement" name="purpose[]" value="bereavement" {{ $checked('bereavement') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="purpose_bereavement">
                                        {{ $purposeOptions['bereavement'] ?? 'Bereavement' }}
                                        <small class="text-muted d-block">Attach: Copy of registered death certificate</small>
                                    </label>
                                </div>
                                <div class="row mt-2" id="section_bereavement" style="display:none;">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="bereavement_relationship">Relationship</label>
                                            <input type="text" class="form-control" id="bereavement_relationship" name="bereavement_relationship" value="{{ old('bereavement_relationship') }}">
                                        </div>
                                    </div>
                                </div>

                                <hr class="my-3">

                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="purpose_tuition" name="purpose[]" value="tuition" {{ $checked('tuition') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="purpose_tuition">
                                        {{ $purposeOptions['tuition'] ?? 'Tuition Fee' }}
                                        <small class="text-muted d-block">For employee or dependent children</small>
                                    </label>
                                </div>
                                <div class="row mt-2" id="section_tuition" style="display:none;">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="tuition_child_name">Name of Child</label>
                                            <input type="text" class="form-control" id="tuition_child_name" name="tuition_child_name" value="{{ old('tuition_child_name') }}">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="tuition_child_age">Age</label>
                                            <input type="number" class="form-control" id="tuition_child_age" name="tuition_child_age" min="0" max="120" value="{{ old('tuition_child_age') }}">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="tuition_child_level">Level</label>
                                            <input type="text" class="form-control" id="tuition_child_level" name="tuition_child_level" value="{{ old('tuition_child_level') }}">
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <small class="text-muted d-block mb-2">Attach: Detailed assessment of fees</small>
                                    </div>
                                </div>

                                <hr class="my-3">

                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="purpose_dental" name="purpose[]" value="dental" {{ $checked('dental') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="purpose_dental">
                                        {{ $purposeOptions['dental'] ?? 'Dental' }}
                                        <small class="text-muted d-block">Attach: Dentist's prescription indicating procedure, cost and schedule</small>
                                    </label>
                                </div>
                                <div class="row mt-2" id="section_dental" style="display:none;">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="dental_patient_name">Patient's Name</label>
                                            <input type="text" class="form-control" id="dental_patient_name" name="dental_patient_name" value="{{ old('dental_patient_name') }}">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="dental_relationship">Relationship</label>
                                            <input type="text" class="form-control" id="dental_relationship" name="dental_relationship" value="{{ old('dental_relationship') }}">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="dental_age">Age</label>
                                            <input type="number" class="form-control" id="dental_age" name="dental_age" min="0" max="120" value="{{ old('dental_age') }}">
                                        </div>
                                    </div>
                                </div>

                                <hr class="my-3">

                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="purpose_other" name="purpose[]" value="other" {{ $checked('other') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="purpose_other">
                                        {{ $purposeOptions['other'] ?? 'Other (please specify)' }}
                                    </label>
                                </div>
                                <div class="mt-2" id="section_other" style="display:none;">
                                    <div class="form-group mb-0">
                                        <label for="other_purpose">{{ data_get($formConfig, 'labels.other_purpose', 'Other (please specify)') }}</label>
                                        <input type="text" class="form-control" id="other_purpose" name="other_purpose" value="{{ old('other_purpose') }}" placeholder="{{ data_get($fieldUi, 'other_purpose.placeholder', 'Please specify') }}">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="supporting_documents">Supporting documents or images</label>
                            <input type="file" class="form-control-file" id="supporting_documents" name="supporting_documents[]" accept=".pdf,.doc,.docx,image/*" multiple>
                            <small class="text-muted d-block mt-1">Up to 5 files. Images or PDF/DOC up to 10&nbsp;MB each.</small>
                        </div>

                        <div class="form-group form-check">
                            <input type="checkbox" class="form-check-input" id="employee_statement" name="employee_statement" value="1" {{ old('employee_statement') ? 'checked' : '' }} {{ data_get($formConfig, 'required.employee_statement') ? 'required' : '' }}>
                            <label class="form-check-label" for="employee_statement">
                                {{ data_get($formConfig, 'labels.employee_statement', 'I hereby certify that the foregoing statements and information are true and correct.') }}
                            </label>
                        </div>

                        <div class="form-group mb-0">
                            <button type="submit" class="btn theme-btn">
                                <i class="fa fa-paper-plane"></i> {{ $formUi['submit_label'] ?? 'Submit Loan Application' }}
                            </button>
                            <a href="{{ route('employee.dashboard') }}" class="btn theme-btn ml-2">
                                <i class="fa fa-home"></i> {{ $formUi['back_label_employee'] ?? 'Back to Dashboard' }}
                            </a>
                        </div>
                    </form>

                    <div class="mt-4">
                        <h5 class="page-header-title mb-3">My Loan Applications</h5>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Date Filed</th>
                                        <th>Amount Requested</th>
                                        <th>Status</th>
                                        <th>Attachments</th>
                                        <th class="text-nowrap">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($loanHistory as $history)
                                    <tr>
                                        <td>{{ \Carbon\Carbon::parse($history->date_filed)->format('M d, Y') }}</td>
                                        <td>₱{{ number_format((float) $history->amount_requested, 2) }}</td>
                                        <td>
                                            @if((int)$history->status === 0)
                                                <span class="theme-badge">Pending</span>
                                            @elseif((int)$history->status === 1)
                                                <span class="theme-badge">Approved</span>
                                            @else
                                                <span class="theme-badge">Rejected</span>
                                            @endif
                                        </td>
                                        <td class="text-nowrap">
                                            @php $paths = is_array($history->supporting_documents) ? $history->supporting_documents : []; @endphp
                                            @if(count($paths))
                                                @foreach($paths as $i => $p)
                                                    <a href="{{ route('loan.attachment', ['id' => $history->id, 'index' => $i]) }}" target="_blank" rel="noopener">View {{ $i + 1 }}</a>@if(!$loop->last)<br>@endif
                                                @endforeach
                                            @else — @endif
                                        </td>
                                        <td class="text-nowrap">
                                            <a href="{{ route('loan.show', $history->id) }}" class="btn theme-btn btn-sm">
                                                <i class="fa fa-eye"></i> View
                                            </a>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="5" class="text-center">No loan applications submitted yet</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
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
<script>
    function toggleSection(checkboxId, sectionId) {
        var cb = document.getElementById(checkboxId);
        var section = document.getElementById(sectionId);
        if (!cb || !section) return;
        var on = cb.checked;
        section.style.display = on ? '' : 'none';
    }

    function initPurposeToggles() {
        toggleSection('purpose_hospitalization', 'section_hospitalization');
        toggleSection('purpose_calamity', 'section_calamity');
        toggleSection('purpose_bereavement', 'section_bereavement');
        toggleSection('purpose_tuition', 'section_tuition');
        toggleSection('purpose_dental', 'section_dental');
        toggleSection('purpose_other', 'section_other');

        var ids = [
            ['purpose_hospitalization', 'section_hospitalization'],
            ['purpose_calamity', 'section_calamity'],
            ['purpose_bereavement', 'section_bereavement'],
            ['purpose_tuition', 'section_tuition'],
            ['purpose_dental', 'section_dental'],
            ['purpose_other', 'section_other'],
        ];
        ids.forEach(function (pair) {
            var cb = document.getElementById(pair[0]);
            if (cb) cb.addEventListener('change', function () { toggleSection(pair[0], pair[1]); });
        });
    }

    initPurposeToggles();
</script>
@endsection

