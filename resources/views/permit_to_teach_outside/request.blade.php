@extends('layouts.master')

@section('css')
    @include('includes.employee_request_form_theme', ['pageWrapperClass' => 'permit-page'])
@endsection

@section('content')
@include('includes.flash')

<div class="row permit-page">
    <div class="col-12 px-1">
        <div class="card">
            <div class="card-body">
                <h4 class="page-header-title"><i class="fa fa-file-text-o mr-1"></i> Permit to Teach (Outside School) Application Form</h4>
                <p class="page-subtitle">Submit your outside teaching request for review and approval.</p>

                <div class="employee-panel mt-4">
                    <h6><i class="fa fa-user mr-1"></i> Employee Information</h6>
                    <p><strong>Name:</strong> {{ $currentEmployee->name }}</p>
                    <p><strong>Department:</strong> {{ $currentEmployee->department ?? 'N/A' }}</p>
                    <p><strong>Position:</strong> {{ $currentEmployee->position ?? 'N/A' }}</p>
                </div>

                <form method="POST" action="{{ route('permit_to_teach_outside.storeRequest') }}">
                    @csrf

                    <h5 class="mt-4 mb-3">Applicant Details</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Position / Rank</label>
                                <input type="text" class="form-control" name="position_rank" value="{{ old('position_rank', $currentEmployee->position ?? '') }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Department / School</label>
                                <input type="text" class="form-control" name="department_school" value="{{ old('department_school', $currentEmployee->department ?? '') }}">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Employment Status</label>
                        <div class="pt-2">
                            <label class="mr-3"><input type="radio" name="employment_status" value="full_time" {{ old('employment_status') === 'full_time' ? 'checked' : '' }} required> Full-time</label>
                            <label class="mr-3"><input type="radio" name="employment_status" value="part_time" {{ old('employment_status') === 'part_time' ? 'checked' : '' }} required> Part-time</label>
                            <label class="mr-3"><input type="radio" name="employment_status" value="probationary" {{ old('employment_status') === 'probationary' ? 'checked' : '' }} required> Probationary</label>
                            <label><input type="radio" name="employment_status" value="regular" {{ old('employment_status') === 'regular' ? 'checked' : '' }} required> Regular</label>
                        </div>
                    </div>

                    <hr>
                    <h5 class="mt-3 mb-3">A. Outside Teaching Details</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Name of Other School/Institution</label>
                                <input type="text" class="form-control" name="other_school_name" value="{{ old('other_school_name') }}" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>School Address</label>
                                <input type="text" class="form-control" name="other_school_address" value="{{ old('other_school_address') }}" required>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Type of Institution</label>
                        <div class="pt-2">
                            <label class="mr-3"><input type="radio" name="institution_type" id="inst_public" value="public" {{ old('institution_type') === 'public' ? 'checked' : '' }} required> Public</label>
                            <label class="mr-3"><input type="radio" name="institution_type" id="inst_private" value="private" {{ old('institution_type') === 'private' ? 'checked' : '' }} required> Private</label>
                            <label class="mr-3"><input type="radio" name="institution_type" id="inst_review" value="review_center" {{ old('institution_type') === 'review_center' ? 'checked' : '' }} required> Review Center</label>
                            <label><input type="radio" name="institution_type" id="inst_others" value="others" {{ old('institution_type') === 'others' ? 'checked' : '' }} required> Others</label>
                        </div>
                    </div>

                    <div class="row" id="inst_others_wrap" style="display:none;">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Others (please specify)</label>
                                <input type="text" class="form-control" id="institution_type_others" name="institution_type_others" value="{{ old('institution_type_others') }}">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Subject(s) to be Taught</label>
                                <input type="text" class="form-control" name="subjects_to_teach" value="{{ old('subjects_to_teach') }}" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>No. of Units / Hours per Week</label>
                                <input type="text" class="form-control" name="units_or_hours_per_week" value="{{ old('units_or_hours_per_week') }}">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Program / Level</label>
                        <div class="pt-2">
                            <label class="mr-3"><input type="radio" name="program_level" value="basic_ed" {{ old('program_level') === 'basic_ed' ? 'checked' : '' }} required> Basic Ed</label>
                            <label class="mr-3"><input type="radio" name="program_level" value="senior_high" {{ old('program_level') === 'senior_high' ? 'checked' : '' }} required> Senior High</label>
                            <label class="mr-3"><input type="radio" name="program_level" value="college" {{ old('program_level') === 'college' ? 'checked' : '' }} required> College</label>
                            <label><input type="radio" name="program_level" value="graduate" {{ old('program_level') === 'graduate' ? 'checked' : '' }} required> Graduate</label>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Teaching Schedule in Other School (Day / Time / Subject)</label>
                        <textarea class="form-control" rows="4" name="teaching_schedule">{{ old('teaching_schedule') }}</textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Duration of Engagement - From</label>
                                <input type="date" class="form-control" name="engagement_from" value="{{ old('engagement_from') }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Duration of Engagement - To</label>
                                <input type="date" class="form-control" name="engagement_to" value="{{ old('engagement_to') }}">
                            </div>
                        </div>
                    </div>

                    <hr>
                    <h5 class="mt-3 mb-2">B. Certification by Applicant</h5>
                    <ul class="mb-2">
                        <li>My teaching engagement in another institution will not conflict with my official working hours, teaching load, or responsibilities in Colegio de Sta. Teresa De Avila.</li>
                        <li>I will prioritize my duties and performance in this institution.</li>
                        <li>I understand that failure to comply may result in revocation of this permit and possible administrative action.</li>
                    </ul>
                    <div class="form-group">
                        <label><input type="checkbox" name="certification_confirmed" value="1" {{ old('certification_confirmed') ? 'checked' : '' }} required> I certify that all information above is true and correct.</label>
                    </div>

                    <div class="form-group mb-0">
                        <button type="submit" class="btn theme-btn"><i class="fa fa-paper-plane"></i> Submit Permit to Teach Form</button>
                        <a href="{{ route('employee.dashboard') }}" class="btn theme-btn ml-2"><i class="fa fa-home"></i> Back to Dashboard</a>
                    </div>
                </form>

                <div class="mt-4">
                    <h5 class="page-header-title mb-3">My Permit to Teach Requests</h5>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Submitted</th>
                                    <th>Other School</th>
                                    <th>Subject(s)</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($history as $item)
                                <tr>
                                    <td>{{ optional($item->created_at)->format('M d, Y') }}</td>
                                    <td>{{ $item->other_school_name }}</td>
                                    <td>{{ $item->subjects_to_teach }}</td>
                                    <td>
                                        @if((int)$item->status === 0)<span class="theme-badge">Pending</span>
                                        @elseif((int)$item->status === 1)<span class="theme-badge">Approved</span>
                                        @else <span class="theme-badge">Rejected</span>
                                        @endif
                                    </td>
                                    <td><a href="{{ route('permit_to_teach_outside.show', $item->id) }}" class="btn theme-btn btn-sm"><i class="fa fa-eye"></i> View</a></td>
                                </tr>
                                @empty
                                <tr><td colspan="5" class="text-center">No permit to teach requests submitted yet</td></tr>
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
    function toggleInstitutionOthers() {
        var otherRadio = document.getElementById('inst_others');
        var wrap = document.getElementById('inst_others_wrap');
        var input = document.getElementById('institution_type_others');
        if (!otherRadio || !wrap || !input) return;

        if (otherRadio.checked) {
            wrap.style.display = '';
            input.setAttribute('required', 'required');
        } else {
            wrap.style.display = 'none';
            input.removeAttribute('required');
        }
    }

    ['inst_public', 'inst_private', 'inst_review', 'inst_others'].forEach(function (id) {
        var el = document.getElementById(id);
        if (el) el.addEventListener('change', toggleInstitutionOthers);
    });
    toggleInstitutionOthers();
</script>
@endsection
