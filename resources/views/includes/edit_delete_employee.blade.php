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


                        @php $currentDeptId = $employee->department_id ?? optional($employee->department)->id; @endphp
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
                        <label for="edit-password-{{ $employee->id }}" class="col-sm-3 control-label">Password</label>
                        <input type="password" class="form-control" id="edit-password-{{ $employee->id }}" name="password" placeholder="New Login Password (optional)">
                        <small class="text-muted d-block mt-1">If set, password must be at least 8 characters.</small>
                    </div>
                    <div class="form-group">
                        <label for="edit-schedule-{{ $employee->id }}" class="col-sm-3 control-label">Schedule</label>


                        @php $currentScheduleSlug = optional($employee->schedules->first())->slug; @endphp
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
                        $currentSchedule = $employee->schedules->first();
                        $rotation = $employee->shiftRotation;
                        $rotationStart = $rotation?->start_date ? \Carbon\Carbon::parse($rotation->start_date)->toDateString() : '';
                        $rotationPattern = '';
                        if ($rotation?->pattern_json) {
                            $arr = json_decode((string) $rotation->pattern_json, true);
                            if (is_array($arr)) {
                                $rotationPattern = implode(',', array_map('strval', $arr));
                            }
                        }
                    @endphp

                    <div class="form-group" data-rotation-fields style="{{ ($currentSchedule && ($currentSchedule->schedule_type ?? 'fixed') === 'shifting') ? '' : 'display:none;' }}">
                        <label class="col-sm-3 control-label">Rotation start</label>
                        <input type="date" class="form-control" name="rotation_start_date" value="{{ $rotationStart }}">
                        <small class="text-muted d-block mt-1">Start date for the rotation pattern.</small>
                    </div>
                    <div class="form-group" data-rotation-fields style="{{ ($currentSchedule && ($currentSchedule->schedule_type ?? 'fixed') === 'shifting') ? '' : 'display:none;' }}">
                        <label class="col-sm-3 control-label">Rotation pattern</label>
                        <input type="text" class="form-control" name="rotation_pattern" value="{{ $rotationPattern }}" placeholder="DAY,NIGHT,OFF">
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
