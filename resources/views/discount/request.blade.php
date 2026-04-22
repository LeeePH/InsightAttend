@extends('layouts.master')

@section('css')
    @include('includes.employee_request_form_theme', ['pageWrapperClass' => 'discount-page'])
@endsection

@section('content')
@include('includes.flash')
@php
    $formConfig = $formConfig ?? [];
    $today = \Carbon\Carbon::now()->toDateString();
@endphp

<div class="row discount-page">
    <div class="col-12 px-1">
        <div class="card">
            <div class="card-body">
                <h4 class="page-header-title"><i class="fa fa-file-text-o mr-1"></i> {{ $formConfig['title'] ?? 'Application for Discount Form' }}</h4>
                <p class="page-subtitle">{{ $formConfig['subtitle'] ?? 'Submit your application for tuition discount.' }}</p>

                <div class="employee-panel mt-4">
                    <h6><i class="fa fa-user mr-1"></i> Employee Information</h6>
                    <p><strong>Name:</strong> {{ $currentEmployee->name }}</p>
                    <p><strong>Department:</strong> {{ $currentEmployee->department ?? 'N/A' }}</p>
                    <p><strong>Position:</strong> {{ $currentEmployee->position ?? 'N/A' }}</p>
                </div>

                <form method="POST" action="{{ route('discount.storeRequest') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Term/Semester</label>
                                <input type="text" class="form-control" name="term_semester" value="{{ old('term_semester') }}" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>School/Academic Year</label>
                                <input type="text" class="form-control" name="school_year" value="{{ old('school_year') }}" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Date of Request</label>
                                <input type="date" class="form-control" name="date_request" value="{{ old('date_request', $today) }}" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Department</label>
                                <input type="text" class="form-control" name="employee_department" value="{{ old('employee_department', $currentEmployee->department ?? '') }}" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Position</label>
                                <input type="text" class="form-control" name="employee_position" value="{{ old('employee_position', $currentEmployee->position ?? '') }}" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Date of Hire</label>
                                <input type="date" class="form-control" name="date_hire" value="{{ old('date_hire') }}" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Employment Status</label>
                                <select class="form-control" name="employment_status" required>
                                    <option value="">- Select -</option>
                                    <option value="probationary" {{ old('employment_status') === 'probationary' ? 'selected' : '' }}>Probationary</option>
                                    <option value="regular" {{ old('employment_status') === 'regular' ? 'selected' : '' }}>Regular</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>School</label>
                                <div class="pt-2">
                                    <label class="mr-3"><input type="radio" name="school" value="stsn" {{ old('school') === 'stsn' ? 'checked' : '' }} required> STSN</label>
                                    <label><input type="radio" name="school" value="csta" {{ old('school') === 'csta' ? 'checked' : '' }} required> CSTA</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Name of Student</label>
                                <input type="text" class="form-control" name="student_name" value="{{ old('student_name') }}" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Student No</label>
                                <input type="text" class="form-control" name="student_no" value="{{ old('student_no') }}" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Track & Strand/Program</label>
                                <input type="text" class="form-control" name="track_program" value="{{ old('track_program') }}" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Grade/Year Level</label>
                                <input type="text" class="form-control" name="grade_year_level" value="{{ old('grade_year_level') }}" required>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Department</label>
                        <div class="pt-2">
                            <label class="mr-3"><input type="radio" name="student_department" value="grade_school" {{ old('student_department') === 'grade_school' ? 'checked' : '' }} required> Grade School</label>
                            <label class="mr-3"><input type="radio" name="student_department" value="junior_high" {{ old('student_department') === 'junior_high' ? 'checked' : '' }} required> Junior High School</label>
                            <label class="mr-3"><input type="radio" name="student_department" value="senior_high" {{ old('student_department') === 'senior_high' ? 'checked' : '' }} required> Senior High School</label>
                            <label><input type="radio" name="student_department" value="college" {{ old('student_department') === 'college' ? 'checked' : '' }} required> College</label>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Discount Applied</label>
                        <div class="pt-2">
                            <label class="mr-3"><input type="radio" name="discount_applied" id="discount_family" value="family_relative" {{ old('discount_applied') === 'family_relative' ? 'checked' : '' }} required> Family Relative (sponsorship)</label>
                            <label><input type="radio" name="discount_applied" id="discount_privileges" value="employee_privileges" {{ old('discount_applied') === 'employee_privileges' ? 'checked' : '' }} required> Employee's Privileges</label>
                        </div>
                    </div>

                    <div class="row" id="family_fields" style="display:none;">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Relationship</label>
                                <input type="text" class="form-control" name="family_relationship" id="family_relationship" value="{{ old('family_relationship') }}">
                            </div>
                        </div>
                    </div>

                    <div class="row" id="privilege_fields" style="display:none;">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Employee's Privileges</label>
                                <select class="form-control" name="privilege_child_order" id="privilege_child_order">
                                    <option value="">- Select child -</option>
                                    <option value="1st" {{ old('privilege_child_order') === '1st' ? 'selected' : '' }}>1st child</option>
                                    <option value="2nd" {{ old('privilege_child_order') === '2nd' ? 'selected' : '' }}>2nd child</option>
                                    <option value="3rd" {{ old('privilege_child_order') === '3rd' ? 'selected' : '' }}>3rd child</option>
                                    <option value="4th" {{ old('privilege_child_order') === '4th' ? 'selected' : '' }}>4th child</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Documents to Submit (attachments)</label>
                        <div id="documents_to_submit_hint" class="mb-2 text-muted">
                            Select a discount type to see required document guidance.
                        </div>
                        <input type="file" class="form-control-file" name="supporting_documents[]" accept=".pdf,.doc,.docx,image/*" multiple>
                        <small class="text-muted d-block mt-1">Up to 5 files. Images or PDF/DOC up to 10 MB each.</small>
                    </div>

                    <div class="form-group mb-0">
                        <button type="submit" class="btn theme-btn"><i class="fa fa-paper-plane"></i> Submit Application</button>
                        <a href="{{ route('employee.dashboard') }}" class="btn theme-btn ml-2"><i class="fa fa-home"></i> Back to Dashboard</a>
                    </div>
                </form>

                <div class="mt-4">
                    <h5 class="page-header-title mb-3">My Discount Applications</h5>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Date Request</th>
                                    <th>Student</th>
                                    <th>Discount Type</th>
                                    <th>Status</th>
                                    <th>Attachments</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($discountHistory as $history)
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($history->date_request)->format('M d, Y') }}</td>
                                    <td>{{ $history->student_name }}</td>
                                    <td>{{ $history->discount_applied === 'family_relative' ? 'Family Relative' : 'Employee Privileges' }}</td>
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
                                                <a href="{{ route('discount.attachment', ['id' => $history->id, 'index' => $i]) }}" target="_blank" rel="noopener">View {{ $i + 1 }}</a>@if(!$loop->last)<br>@endif
                                            @endforeach
                                        @else — @endif
                                    </td>
                                    <td><a href="{{ route('discount.show', $history->id) }}" class="btn theme-btn btn-sm"><i class="fa fa-eye"></i> View</a></td>
                                </tr>
                                @empty
                                <tr><td colspan="6" class="text-center">No discount applications submitted yet</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
    function toggleDiscountFields() {
        var family = document.getElementById('discount_family');
        var privileges = document.getElementById('discount_privileges');
        var familyWrap = document.getElementById('family_fields');
        var privilegeWrap = document.getElementById('privilege_fields');
        var relationship = document.getElementById('family_relationship');
        var childOrder = document.getElementById('privilege_child_order');
        var docsHint = document.getElementById('documents_to_submit_hint');

        if (!family || !privileges) return;

        var isFamily = family.checked;
        var isPrivilege = privileges.checked;

        familyWrap.style.display = isFamily ? '' : 'none';
        privilegeWrap.style.display = isPrivilege ? '' : 'none';

        if (relationship) {
            if (isFamily) relationship.setAttribute('required', 'required');
            else relationship.removeAttribute('required');
        }
        if (childOrder) {
            if (isPrivilege) childOrder.setAttribute('required', 'required');
            else childOrder.removeAttribute('required');
        }

        if (docsHint) {
            if (isFamily) {
                docsHint.textContent = 'Document to Submit: Photocopy of PSA Birth Certificate (1st Application only)';
            } else if (isPrivilege) {
                docsHint.textContent = 'Document to Submit: Photocopy of PSA Birth Certificate';
            } else {
                docsHint.textContent = 'Select a discount type to see required document guidance.';
            }
        }
    }

    ['discount_family', 'discount_privileges'].forEach(function (id) {
        var el = document.getElementById(id);
        if (el) el.addEventListener('change', toggleDiscountFields);
    });
    toggleDiscountFields();
</script>
@endsection

