@php
    $currentDeptId = $employee->department_id ?? optional($employee->department)->id;
    $currentSchedule = $employee->schedules->first();
    $currentScheduleSlug = optional($currentSchedule)->slug;
    $rotation = $employee->shiftRotation;
    $rotationStart = $rotation?->start_date ? \Carbon\Carbon::parse($rotation->start_date)->format('M d, Y') : 'Not set';
    $rotationPattern = '';
    if ($rotation?->pattern_json) {
        $arr = json_decode((string) $rotation->pattern_json, true);
        if (is_array($arr)) {
            $rotationPattern = implode(', ', array_map('strval', $arr));
        }
    }
@endphp

<!-- View -->
<div class="modal fade employee-profile-modal" id="view-employee-{{ $employee->id }}" tabindex="-1" role="dialog" aria-labelledby="view-employee-title-{{ $employee->id }}" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="profile-hero">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center">
                        <div class="profile-avatar mr-3">
                            <i class="fa fa-user"></i>
                        </div>
                        <div>
                            <h4 class="mb-1" id="view-employee-title-{{ $employee->id }}">{{ $employee->name }}</h4>
                            <p class="mb-0">{{ $employee->position ?: 'No position set' }}</p>
                        </div>
                    </div>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            </div>

            <div class="modal-body p-4">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <div class="info-card">
                            <span class="info-label">Department</span>
                            <div class="info-value">{{ $employee->department?->name ?? 'Not assigned' }}</div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="info-card">
                            <span class="info-label">Email</span>
                            <div class="info-value">{{ $employee->email ?: 'No email saved' }}</div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="info-card">
                            <span class="info-label">Phone</span>
                            <div class="info-value">{{ $employee->phone ?: 'No phone saved' }}</div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="info-card">
                            <span class="info-label">Member Since</span>
                            <div class="info-value">{{ optional($employee->created_at)->format('M d, Y h:i A') ?: 'Unknown' }}</div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="info-card">
                            <span class="info-label">Schedule</span>
                            <div class="info-value">
                                @if($currentSchedule)
                                    {{ $currentSchedule->slug }}
                                    @if (($currentSchedule->schedule_type ?? 'fixed') === 'shifting')
                                        <div class="text-muted font-weight-normal mt-1">Shifting schedule</div>
                                    @else
                                        <div class="text-muted font-weight-normal mt-1">
                                            {{ \Carbon\Carbon::parse($currentSchedule->time_in)->format('g:i A') }} to {{ \Carbon\Carbon::parse($currentSchedule->time_out)->format('g:i A') }}
                                        </div>
                                    @endif
                                @else
                                    Not assigned
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="info-card">
                            <span class="info-label">Face Registration</span>
                            <div class="info-value">{{ $employee->face_registered ? 'Registered' : 'Not registered' }}</div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="info-card">
                            <span class="info-label">Rotation Start</span>
                            <div class="info-value">{{ $rotationStart }}</div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="info-card">
                            <span class="info-label">Rotation Pattern</span>
                            <div class="info-value">{{ $rotationPattern ?: 'Not set' }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Close</button>
                <a href="#edit-employee-{{ $employee->id }}" data-dismiss="modal" data-toggle="modal" class="btn btn-success">
                    <i class="fa fa-edit"></i> Edit Employee
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Edit -->
<div class="modal fade" id="edit-employee-{{ $employee->id }}" tabindex="-1" role="dialog" aria-labelledby="edit-employee-title-{{ $employee->id }}" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span></button>

            </div>
            <h4 class="modal-title" id="edit-employee-title-{{ $employee->id }}"><b><span class="employee_id">Edit Employee</span></b></h4>
            <div class="modal-body text-left">
                <form class="form-horizontal" method="POST" action="{{ route('employees.update', $employee) }}">
                    @csrf
                    <input type="hidden" name="_method" value="PUT">
                    <div class="form-group">
                        <label for="edit-name-{{ $employee->id }}" class="col-sm-3 control-label">Name</label>


                        <input type="text" class="form-control" id="edit-name-{{ $employee->id }}" name="name" value="{{ $employee->name }}"
                            required>

                    </div>
                    <div class="form-group">
                        <label for="edit-position-{{ $employee->id }}" class="col-sm-3 control-label">Position</label>


                        <input type="text" class="form-control" id="edit-position-{{ $employee->id }}" name="position" value="{{ $employee->position }}"
                            required>

                    </div>
                    <div class="form-group">
                        <label for="edit-department-{{ $employee->id }}" class="col-sm-3 control-label">Department</label>


                        <select class="form-control" id="edit-department-{{ $employee->id }}" name="department_id" required>
                            <option value="" {{ !$currentDeptId ? 'selected="selected"' : '' }}>- Select Department -</option>
                            @foreach(($departments ?? []) as $dept)
                                <option value="{{ $dept->id }}" {{ (int) $currentDeptId === (int) $dept->id ? 'selected="selected"' : '' }}>
                                    {{ $dept->name }}
                                </option>
                            @endforeach
                        </select>

                    </div>
                    <div class="form-group">
                        <label for="edit-schedule-dept-{{ $employee->id }}" class="col-sm-3 control-label">Scheduling dept (timetable)</label>
                        <select class="form-control" id="edit-schedule-dept-{{ $employee->id }}" name="schedule_department_key">
                            <option value="">— Infer from department name —</option>
                            <option value="IT" {{ ($employee->schedule_department_key ?? '') === 'IT' ? 'selected' : '' }}>IT</option>
                            <option value="EDUC" {{ ($employee->schedule_department_key ?? '') === 'EDUC' ? 'selected' : '' }}>EDUC</option>
                            <option value="SHTM" {{ ($employee->schedule_department_key ?? '') === 'SHTM' ? 'selected' : '' }}>SHTM</option>
                        </select>
                        <small class="text-muted d-block mt-1">IT / EDUC / SHTM for employee timetable and secretary access.</small>
                    </div>
                 
                  
                    <div class="form-group">
                        <label for="edit-email-{{ $employee->id }}" class="col-sm-3 control-label">Email</label>


                        <input type="email" class="form-control" id="edit-email-{{ $employee->id }}" name="email"
                            value="{{ $employee->email }}" >

                    </div>
                    <div class="form-group">
                        <label for="edit-phone-{{ $employee->id }}" class="col-sm-3 control-label">Phone (SMS)</label>
                        <input type="text" class="form-control" id="edit-phone-{{ $employee->id }}" name="phone"
                            value="{{ $employee->phone }}" placeholder="+639xxxxxxxxx">
                    </div>
                    <div class="form-group">
                        <label for="edit-date-hired-{{ $employee->id }}" class="col-sm-3 control-label">Date Hired</label>
                        <input type="date" class="form-control" id="edit-date-hired-{{ $employee->id }}" name="date_hired"
                            value="{{ optional($employee->date_hired)->toDateString() }}">
                    </div>
                    <div class="form-group">
                        <label for="edit-employment-type-{{ $employee->id }}" class="col-sm-3 control-label">Employment Status</label>
                        <select class="form-control" id="edit-employment-type-{{ $employee->id }}" name="employment_type">
                            <option value="">- Select -</option>
                            <option value="full_time" {{ $employee->employment_type === 'full_time' ? 'selected' : '' }}>Full-time</option>
                            <option value="part_time" {{ $employee->employment_type === 'part_time' ? 'selected' : '' }}>Part-time</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="edit-skills-{{ $employee->id }}" class="col-sm-3 control-label">Skills & Expertise</label>
                        <textarea class="form-control" id="edit-skills-{{ $employee->id }}" name="skills" rows="3" placeholder="One skill per line">{{ $employee->skills }}</textarea>
                    </div>
                    <div class="form-group">
                        <label for="edit-achievements-{{ $employee->id }}" class="col-sm-3 control-label">Achievements</label>
                        <textarea class="form-control" id="edit-achievements-{{ $employee->id }}" name="achievements" rows="3" placeholder="One achievement per line">{{ $employee->achievements }}</textarea>
                    </div>
                    <div class="form-group">
                        <label for="edit-emergency-name-{{ $employee->id }}" class="col-sm-3 control-label">Emergency Contact Name</label>
                        <input type="text" class="form-control" id="edit-emergency-name-{{ $employee->id }}" name="emergency_contact_name"
                            value="{{ $employee->emergency_contact_name }}">
                    </div>
                    <div class="form-group">
                        <label for="edit-emergency-relationship-{{ $employee->id }}" class="col-sm-3 control-label">Emergency Contact Relationship</label>
                        <input type="text" class="form-control" id="edit-emergency-relationship-{{ $employee->id }}" name="emergency_contact_relationship"
                            value="{{ $employee->emergency_contact_relationship }}">
                    </div>
                    <div class="form-group">
                        <label for="edit-emergency-phone-{{ $employee->id }}" class="col-sm-3 control-label">Emergency Contact Phone</label>
                        <input type="text" class="form-control" id="edit-emergency-phone-{{ $employee->id }}" name="emergency_contact_phone"
                            value="{{ $employee->emergency_contact_phone }}">
                    </div>
                    <div class="form-group">
                        <label for="edit-password-{{ $employee->id }}" class="col-sm-3 control-label">Password</label>
                        <input type="password" class="form-control" id="edit-password-{{ $employee->id }}" name="password" placeholder="New Login Password (optional)">
                        <small class="text-muted d-block mt-1">If set, password must be at least 8 characters.</small>
                    </div>
                    <div class="form-group">
                        <label for="edit-schedule-{{ $employee->id }}" class="col-sm-3 control-label">Schedule</label>


                        <select class="form-control" id="edit-schedule-{{ $employee->id }}" name="schedule" required>
                            <option value="" {{ !$currentScheduleSlug ? 'selected="selected"' : '' }}>— Select —</option>
                            @foreach ($schedules as $schedule)
                                <option value="{{ $schedule->slug }}" {{ $currentScheduleSlug === $schedule->slug ? 'selected="selected"' : '' }}>
                                    {{ $schedule->slug }}
                                    @if (($schedule->schedule_type ?? 'fixed') === 'shifting')
                                        (Shifting)
                                    @else
                                        -> from {{ \Carbon\Carbon::parse($schedule->time_in)->format('g:i A') }} to {{ \Carbon\Carbon::parse($schedule->time_out)->format('g:i A') }}
                                    @endif
                                </option>
                            @endforeach

                        </select>

                    </div>
                    @php
                        $rotationStartDate = $rotation?->start_date ? \Carbon\Carbon::parse($rotation->start_date)->toDateString() : '';
                        $rotationPatternInput = str_replace(', ', ',', $rotationPattern);
                    @endphp

                    <div class="form-group" data-rotation-fields style="{{ ($currentSchedule && ($currentSchedule->schedule_type ?? 'fixed') === 'shifting') ? '' : 'display:none;' }}">
                        <label class="col-sm-3 control-label">Rotation start</label>
                        <input type="date" class="form-control" name="rotation_start_date" value="{{ $rotationStartDate }}">
                        <small class="text-muted d-block mt-1">Start date for the rotation pattern.</small>
                    </div>
                    <div class="form-group" data-rotation-fields style="{{ ($currentSchedule && ($currentSchedule->schedule_type ?? 'fixed') === 'shifting') ? '' : 'display:none;' }}">
                        <label class="col-sm-3 control-label">Rotation pattern</label>
                        <input type="text" class="form-control" name="rotation_pattern" value="{{ $rotationPatternInput }}" placeholder="DAY,NIGHT,OFF">
                        <small class="text-muted d-block mt-1">Comma-separated shift codes (must exist under the selected shifting schedule).</small>
                    </div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default btn-flat pull-left" data-dismiss="modal"><i
                        class="fa fa-close"></i> Close</button>
                <button type="submit" class="btn btn-success btn-flat" name="edit"><i class="fa fa-check-square-o"></i>
                    Update</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    (function () {
        var sel = document.getElementById('edit-schedule-{{ $employee->id }}');
        if (!sel) return;
        var rotationFields = sel.closest('.modal-content') ? sel.closest('.modal-content').querySelectorAll('[data-rotation-fields]') : [];
        var scheduleTypeBySlug = {};
        @foreach ($schedules as $s)
            scheduleTypeBySlug[@json($s->slug)] = @json($s->schedule_type ?? 'fixed');
        @endforeach

        function refreshRotation() {
            var slug = sel.value || '';
            var stype = scheduleTypeBySlug[slug] || 'fixed';
            var show = (stype === 'shifting');
            rotationFields.forEach(function (el) {
                el.style.display = show ? '' : 'none';
            });
        }
        sel.addEventListener('change', refreshRotation);
        refreshRotation();
    })();
</script>

<!-- Delete -->
<div class="modal fade" id="delete-employee-{{ $employee->id }}" tabindex="-1" role="dialog" aria-labelledby="delete-employee-title-{{ $employee->id }}" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header " style="align-items: center">
               
              <h4 class="modal-title" id="delete-employee-title-{{ $employee->id }}"><span class="employee_id">Delete Employee</span></h4>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <form class="form-horizontal" method="POST" action="{{ route('employees.destroy', $employee) }}">
                    @csrf
                    {{ method_field('DELETE') }}
                    <div class="text-center">
                        <h6>Are you sure you want to delete:</h6>
                        <h2 class="bold del_employee_name">{{$employee->name}}</h2>
                    </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default btn-flat pull-left" data-dismiss="modal"><i
                        class="fa fa-close"></i> Close</button>
                <button type="submit" class="btn btn-danger btn-flat"><i class="fa fa-trash"></i> Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>
