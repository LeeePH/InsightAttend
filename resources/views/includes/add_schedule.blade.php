<!-- Add -->
<div class="modal fade" id="addnew">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span></button>
              
            </div>
            <h4 class="modal-title"><b>Add Schedule</b></h4>
            <div class="modal-body text-left">
                <form class="form-horizontal" method="POST" action="{{ route('schedule.store') }}">
                    @csrf
                    <div class="form-group">
                        <label for="name" class="col-sm-3 control-label">Name</label>

                        
                            <div class="bootstrap-timepicker">
                                <input type="text" class="form-control timepicker" id="name" name="slug">
                            </div>
                        
                    </div>
                    <div class="form-group">
                        <label for="schedule_type" class="col-sm-3 control-label">Type</label>
                        <select class="form-control" id="schedule_type" name="schedule_type" required>
                            <option value="fixed" selected>Fixed</option>
                            <option value="shifting">Shifting</option>
                        </select>
                        <small class="text-muted d-block mt-1">
                            Fixed schedules use one Time In/Out. Shifting schedules define multiple shift options.
                        </small>
                    </div>
                    <div class="form-group">
                        <label for="time_in" class="col-sm-3 control-label">Time In</label>

                        
                            <div class="bootstrap-timepicker">
                                <input type="time" class="form-control timepicker" id="time_in" name="time_in" required>
                            </div>
                        
                    </div>
                    <div class="form-group">
                        <label for="time_out" class="col-sm-3 control-label">Time Out</label>

                        
                            <div class="bootstrap-timepicker">
                                <input type="time" class="form-control timepicker" id="time_out" name="time_out" required>
                            </div>
                        
                    </div>
                    <div class="form-group">
                        <label for="break_minutes" class="col-sm-3 control-label">Break (minutes)</label>
                        <input type="number" min="0" max="600" class="form-control" id="break_minutes" name="break_minutes" value="0" required>
                    </div>
                    <div class="form-group">
                        <label for="grace_minutes" class="col-sm-3 control-label">Grace (minutes)</label>
                        <input type="number" min="0" max="120" class="form-control" id="grace_minutes" name="grace_minutes" value="0" required>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-3 control-label">Break window</label>
                        <div class="d-flex" style="gap: 10px;">
                            <input type="time" class="form-control" id="break_start" name="break_start">
                            <input type="time" class="form-control" id="break_end" name="break_end">
                        </div>
                        <small class="text-muted d-block mt-1">Optional. If set, overrides break minutes for worked-hours calculations.</small>
                    </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default btn-flat pull-left" data-dismiss="modal"><i class="fa fa-close"></i> Close</button>
                <button type="submit" class="btn btn-primary btn-flat"><i class="fa fa-save"></i> Save</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    (function () {
        var typeEl = document.getElementById('schedule_type');
        var timeInEl = document.getElementById('time_in');
        var timeOutEl = document.getElementById('time_out');
        var breakStartEl = document.getElementById('break_start');
        var breakEndEl = document.getElementById('break_end');
        if (!typeEl || !timeInEl || !timeOutEl) return;

        function refresh() {
            var isFixed = typeEl.value === 'fixed';
            timeInEl.required = isFixed;
            timeOutEl.required = isFixed;
            if (!isFixed) {
                timeInEl.value = '';
                timeOutEl.value = '';
            }
            timeInEl.disabled = !isFixed;
            timeOutEl.disabled = !isFixed;

            // Break window is allowed for both types, but keep enabled always.
            if (breakStartEl) breakStartEl.disabled = false;
            if (breakEndEl) breakEndEl.disabled = false;
        }

        typeEl.addEventListener('change', refresh);
        refresh();
    })();
</script>

