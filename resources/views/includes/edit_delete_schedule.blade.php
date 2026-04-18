<!-- Edit -->
<div class="modal fade" id="edit{{ $schedule->slug }}">
    <div class=" modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span></button>

            </div>
            <h4 class="modal-title"><b>Update Schedule</b></h4>
            <div class="modal-body text-left">
                <form class="form-horizontal" method="POST" action="{{ route('schedule.update', $schedule->slug) }}">
                    @csrf
                    <input type="hidden" name="_method" value="PUT">
                    @php
                        $stype = $schedule->schedule_type ?? 'fixed';
                    @endphp

                    <div class="form-group">
                        <label for="name" class="col-sm-3 control-label">Name</label>


                        <div class="bootstrap-timepicker">
                            <input type="text" class="form-control timepicker" id="name" name="slug"
                                value="{{ $schedule->slug }}">
                        </div>

                    </div>
                    <div class="form-group">
                        <label for="edit_type_{{ $schedule->slug }}" class="col-sm-3 control-label">Type</label>
                        <select class="form-control" id="edit_type_{{ $schedule->slug }}" name="schedule_type" required>
                            <option value="fixed" @selected($stype === 'fixed')>Fixed</option>
                            <option value="shifting" @selected($stype === 'shifting')>Shifting</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="edit_time_in" class="col-sm-3 control-label">Time In</label>


                        <div class="bootstrap-timepicker">
                            <input type="time" class="form-control timepicker" id="edit_time_in" name="time_in"
                                value="{{ $schedule->time_in }}">
                        </div>

                    </div>
                    <div class="form-group">
                        <label for="edit_time_out" class="col-sm-3 control-label">Time out</label>


                        <div class="bootstrap-timepicker">
                            <input type="time" class="form-control timepicker" id="edit_time_out" name="time_out"
                                value="{{ $schedule->time_out }}">
                        </div>

                    </div>
                    <div class="form-group">
                        <label for="edit_break_{{ $schedule->slug }}" class="col-sm-3 control-label">Break (minutes)</label>
                        <input type="number" min="0" max="600" class="form-control" id="edit_break_{{ $schedule->slug }}"
                            name="break_minutes" value="{{ (int) ($schedule->break_minutes ?? 0) }}" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_grace_{{ $schedule->slug }}" class="col-sm-3 control-label">Grace (minutes)</label>
                        <input type="number" min="0" max="120" class="form-control" id="edit_grace_{{ $schedule->slug }}"
                            name="grace_minutes" value="{{ (int) ($schedule->grace_minutes ?? 0) }}" required>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-3 control-label">Break window</label>
                        <div class="d-flex" style="gap: 10px;">
                            <input type="time" class="form-control" name="break_start" value="{{ $schedule->break_start }}">
                            <input type="time" class="form-control" name="break_end" value="{{ $schedule->break_end }}">
                        </div>
                        <small class="text-muted d-block mt-1">Optional. If set, overrides break minutes for worked-hours calculations.</small>
                    </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default btn-flat pull-left" data-dismiss="modal"><i
                        class="fa fa-close"></i> Close</button>
                <button type="submit" class="btn btn-success btn-flat"><i class="fa fa-check-square-o"></i>
                    Update</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    (function () {
        var typeEl = document.getElementById('edit_type_{{ $schedule->slug }}');
        var timeInEl = document.getElementById('edit_time_in');
        var timeOutEl = document.getElementById('edit_time_out');
        if (!typeEl || !timeInEl || !timeOutEl) return;

        function refresh() {
            var isFixed = typeEl.value === 'fixed';
            timeInEl.required = isFixed;
            timeOutEl.required = isFixed;
            timeInEl.disabled = !isFixed;
            timeOutEl.disabled = !isFixed;
            if (!isFixed) {
                timeInEl.value = '';
                timeOutEl.value = '';
            }
        }

        typeEl.addEventListener('change', refresh);
        refresh();
    })();
</script>

<!-- Delete -->
<div class="modal fade" id="delete{{ $schedule->slug }}">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header " style="align-items: center">
               
                <h4 class="modal-title "><span class="employee_id">Delete Schedule</span></h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
              </div>
            <div class="modal-body">
                <form class="form-horizontal" method="POST" action="{{ route('schedule.destroy', $schedule->slug) }}">
                    @csrf
                    {{ method_field('DELETE') }}
                    <div class="text-center">
                        <h6>Are you sure you want to delete:</h6>
                        <h2 class="bold del_employee_name">{{ $schedule->slug}}</h2>
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