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
    $linkedUser = $employee->user;
    $portalRoleLocked = false;
    $portalRoleDefault = 'employee';
    if ($linkedUser) {
        $linkedUser->loadMissing('roles');
        $roleSlugs = $linkedUser->roles->pluck('slug')->all();
        $portalRoleLocked = !empty($roleSlugs) && !collect($roleSlugs)->every(fn ($s) => in_array($s, ['employee', 'secretary'], true));
        if ($linkedUser->hasRole('secretary')) {
            $portalRoleDefault = 'secretary';
        }
    }
    $portalRoleValue = old('portal_role', $portalRoleDefault);
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
                            <span class="info-label">Portal role</span>
                            <div class="info-value">
                                @if (!$linkedUser)
                                    No login account
                                @elseif ($linkedUser->hasRole('secretary'))
                                    Secretary
                                @elseif ($linkedUser->hasRole('employee'))
                                    Employee
                                @else
                                    {{ $linkedUser->roles->first()->name ?? 'User' }} <span class="text-muted font-weight-normal">(User Management)</span>
                                @endif
                            </div>
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
@php
    $rotationStartDate = $rotation?->start_date ? \Carbon\Carbon::parse($rotation->start_date)->toDateString() : '';
    $rotationPatternInput = str_replace(', ', ',', $rotationPattern);
@endphp
<div class="modal fade employee-edit-modal" id="edit-employee-{{ $employee->id }}" tabindex="-1" role="dialog" aria-labelledby="edit-employee-title-{{ $employee->id }}" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <form method="POST" action="{{ route('employees.update', $employee) }}">
                @csrf
                @method('PUT')
                <div class="edit-modal-hero d-flex justify-content-between align-items-start">
                    <div>
                        <h5 class="mb-1" id="edit-employee-title-{{ $employee->id }}">Edit employee</h5>
                        <small style="opacity: 0.9;">Update profile, portal access, and schedule in one place.</small>
                    </div>
                    <button type="button" class="close text-white ml-2" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body text-left px-4 py-3">
                    <div class="edit-section-title">Work</div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="edit-name-{{ $employee->id }}" class="font-weight-bold">Full name</label>
                                <input type="text" class="form-control" id="edit-name-{{ $employee->id }}" name="name" value="{{ $employee->name }}" required maxlength="64">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="edit-position-{{ $employee->id }}" class="font-weight-bold">Position</label>
                                <input type="text" class="form-control" id="edit-position-{{ $employee->id }}" name="position" value="{{ $employee->position }}" required maxlength="64">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="edit-department-{{ $employee->id }}" class="font-weight-bold">Department</label>
                                <select class="form-control" id="edit-department-{{ $employee->id }}" name="department_id" required>
                                    <option value="" {{ !$currentDeptId ? 'selected' : '' }}>Select department</option>
                                    @foreach(($departments ?? []) as $dept)
                                        <option value="{{ $dept->id }}" {{ (int) $currentDeptId === (int) $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="edit-schedule-dept-{{ $employee->id }}" class="font-weight-bold">Department management</label>
                                <select class="form-control" id="edit-schedule-dept-{{ $employee->id }}" name="schedule_department_key">
                                    <option value="">Infer from department name</option>
                                    <option value="IT" {{ ($employee->schedule_department_key ?? '') === 'IT' ? 'selected' : '' }}>IT</option>
                                    <option value="EDUC" {{ ($employee->schedule_department_key ?? '') === 'EDUC' ? 'selected' : '' }}>EDUC</option>
                                    <option value="SHTM" {{ ($employee->schedule_department_key ?? '') === 'SHTM' ? 'selected' : '' }}>SHTM</option>
                                </select>
                                <small class="text-muted d-block mt-1">For secretaries: which timetable area they manage (IT, EDUC, or SHTM).</small>
                            </div>
                        </div>
                    </div>

                    <div class="edit-section-title">Contact</div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="edit-email-{{ $employee->id }}" class="font-weight-bold">Email</label>
                                <input type="email" class="form-control" id="edit-email-{{ $employee->id }}" name="email" value="{{ $employee->email }}" autocomplete="email">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="edit-phone-{{ $employee->id }}" class="font-weight-bold">Phone (SMS)</label>
                                <input type="text" class="form-control" id="edit-phone-{{ $employee->id }}" name="phone" value="{{ $employee->phone }}" placeholder="+639xxxxxxxxx">
                            </div>
                        </div>
                    </div>

                    <div class="edit-section-title">Portal access</div>
                    <div class="rounded border bg-light p-3 mb-3">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-md-0">
                                    <label for="edit-portal-role-{{ $employee->id }}" class="font-weight-bold">Role</label>
                                    @if ($portalRoleLocked)
                                        <p class="small text-muted mb-2">This login has an admin or HR role. Change it from User Management.</p>
                                        <input type="hidden" name="portal_role" value="employee">
                                    @else
                                        <select class="form-control" id="edit-portal-role-{{ $employee->id }}" name="portal_role" required>
                                            <option value="employee" {{ $portalRoleValue === 'employee' ? 'selected' : '' }}>Employee</option>
                                            <option value="secretary" {{ $portalRoleValue === 'secretary' ? 'selected' : '' }}>Secretary</option>
                                        </select>
                                        <small class="text-muted d-block mt-1">Secretary requires IT, EDUC, or SHTM above.</small>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-0">
                                    <label for="edit-password-{{ $employee->id }}" class="font-weight-bold">New login password</label>
                                    <input type="password" class="form-control" id="edit-password-{{ $employee->id }}" name="password" placeholder="Leave blank to keep current" autocomplete="new-password">
                                    <small class="text-muted d-block mt-1">Optional. Min. 8 characters. Set email + password to create a new login.</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="edit-section-title">Profile</div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="edit-date-hired-{{ $employee->id }}" class="font-weight-bold">Date hired</label>
                                <input type="date" class="form-control" id="edit-date-hired-{{ $employee->id }}" name="date_hired" value="{{ optional($employee->date_hired)->toDateString() }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="edit-employment-type-{{ $employee->id }}" class="font-weight-bold">Employment type</label>
                                <select class="form-control" id="edit-employment-type-{{ $employee->id }}" name="employment_type">
                                    <option value="">Select</option>
                                    <option value="full_time" {{ $employee->employment_type === 'full_time' ? 'selected' : '' }}>Full-time</option>
                                    <option value="part_time" {{ $employee->employment_type === 'part_time' ? 'selected' : '' }}>Part-time</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="edit-skills-{{ $employee->id }}" class="font-weight-bold">Skills &amp; expertise</label>
                                <textarea class="form-control" id="edit-skills-{{ $employee->id }}" name="skills" rows="2" placeholder="One per line">{{ $employee->skills }}</textarea>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="edit-achievements-{{ $employee->id }}" class="font-weight-bold">Achievements</label>
                                <textarea class="form-control" id="edit-achievements-{{ $employee->id }}" name="achievements" rows="2" placeholder="One per line">{{ $employee->achievements }}</textarea>
                            </div>
                        </div>
                    </div>

                    <div class="edit-section-title">Emergency</div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group mb-3">
                                <label for="edit-emergency-name-{{ $employee->id }}" class="font-weight-bold">Contact name</label>
                                <input type="text" class="form-control" id="edit-emergency-name-{{ $employee->id }}" name="emergency_contact_name" value="{{ $employee->emergency_contact_name }}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group mb-3">
                                <label for="edit-emergency-relationship-{{ $employee->id }}" class="font-weight-bold">Relationship</label>
                                <input type="text" class="form-control" id="edit-emergency-relationship-{{ $employee->id }}" name="emergency_contact_relationship" value="{{ $employee->emergency_contact_relationship }}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group mb-3">
                                <label for="edit-emergency-phone-{{ $employee->id }}" class="font-weight-bold">Phone</label>
                                <input type="text" class="form-control" id="edit-emergency-phone-{{ $employee->id }}" name="emergency_contact_phone" value="{{ $employee->emergency_contact_phone }}">
                            </div>
                        </div>
                    </div>

                    <div class="edit-section-title">Attendance schedule</div>
                    <div class="form-group mb-2">
                        <label for="edit-schedule-{{ $employee->id }}" class="font-weight-bold">Schedule</label>
                        <select class="form-control" id="edit-schedule-{{ $employee->id }}" name="schedule" required>
                            <option value="" {{ !$currentScheduleSlug ? 'selected' : '' }}>Select schedule</option>
                            @foreach ($schedules as $schedule)
                                <option value="{{ $schedule->slug }}" {{ $currentScheduleSlug === $schedule->slug ? 'selected' : '' }}>
                                    {{ $schedule->slug }}
                                    @if (($schedule->schedule_type ?? 'fixed') === 'shifting')
                                        (Shifting)
                                    @else
                                        — {{ \Carbon\Carbon::parse($schedule->time_in)->format('g:i A') }} to {{ \Carbon\Carbon::parse($schedule->time_out)->format('g:i A') }}
                                    @endif
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group mb-0" data-rotation-fields style="{{ ($currentSchedule && ($currentSchedule->schedule_type ?? 'fixed') === 'shifting') ? '' : 'display:none;' }}">
                        <label class="font-weight-bold">Rotation start</label>
                        <input type="date" class="form-control mb-2" name="rotation_start_date" value="{{ $rotationStartDate }}">
                        <label class="font-weight-bold">Rotation pattern</label>
                        <input type="text" class="form-control" name="rotation_pattern" value="{{ $rotationPatternInput }}" placeholder="DAY,NIGHT,OFF">
                        <small class="text-muted d-block mt-1">Comma-separated codes for shifting schedules.</small>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0">
                    <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success" name="edit"><i class="fa fa-check"></i> Save changes</button>
                </div>
            </form>
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
