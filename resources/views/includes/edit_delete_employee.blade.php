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


                        <select class="form-control" id="edit-department-{{ $employee->id }}" name="department" required>
                            <option value="" selected>- Select Department -</option>
                            <option value="Bachelor of Science in Information Technology" @selected(in_array($employee->department, ['Bachelor of Science in Information Technology', 'SIT'], true))>Bachelor of Science in Information Technology</option>
                            <option value="Bachelor of Science in Hospitality Management" @selected(in_array($employee->department, ['Bachelor of Science in Hospitality Management', 'SHTM'], true))>Bachelor of Science in Hospitality Management</option>
                            <option value="Bachelor of Science in Tourism Management" @selected($employee->department === 'Bachelor of Science in Tourism Management')>Bachelor of Science in Tourism Management</option>
                            <option value="Bachelor of Secondary Education - English" @selected(in_array($employee->department, ['Bachelor of Secondary Education - English'], true))>Bachelor of Secondary Education - English</option>
                            <option value="Bachelor of Secondary Education - Filipino" @selected(in_array($employee->department, ['Bachelor of Secondary Education - Filipino'], true))>Bachelor of Secondary Education - Filipino</option>
                            <option value="Bachelor of Secondary Education - Mathematics" @selected(in_array($employee->department, ['Bachelor of Secondary Education - Mathematics'], true))>Bachelor of Secondary Education - Mathematics</option>
                            <option value="Bachelor of Secondary Education - Social Science" @selected(in_array($employee->department, ['Bachelor of Secondary Education - Social Science'], true))>Bachelor of Secondary Education - Social Science</option>
                            <option value="Bachelor of Elementary Education" @selected(in_array($employee->department, ['Bachelor of Elementary Education'], true))>Bachelor of Elementary Education</option>
                        </select>

                    </div>
                 
                  
                    <div class="form-group">
                        <label for="edit-email-{{ $employee->id }}" class="col-sm-3 control-label">Email</label>


                        <input type="email" class="form-control" id="edit-email-{{ $employee->id }}" name="email"
                            value="{{ $employee->email }}" >

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
                            <option value="" @selected(!$currentScheduleSlug)>— Select —</option>
                            @foreach ($schedules as $schedule)
                                <option value="{{ $schedule->slug }}" @selected($currentScheduleSlug === $schedule->slug)>{{ $schedule->slug }} -> from
                                    {{ \Carbon\Carbon::parse($schedule->time_in)->format('g:i A') }} to {{ \Carbon\Carbon::parse($schedule->time_out)->format('g:i A') }} </option>
                            @endforeach

                        </select>

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
