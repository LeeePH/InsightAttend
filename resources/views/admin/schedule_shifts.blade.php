@extends('layouts.master')

@section('breadcrumb')
    <div class="col-sm-6">
        <h4 class="page-title text-left">Schedule Shifts</h4>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin') }}">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('schedule.index') }}">Schedules</a></li>
            <li class="breadcrumb-item active">{{ $schedule->slug }}</li>
        </ol>
    </div>
@endsection

@section('button')
    <a href="#addShift" data-toggle="modal" class="btn btn-primary btn-sm btn-flat">
        <i class="mdi mdi-plus mr-2"></i>Add Shift
    </a>
@endsection

@section('content')
@include('includes.flash')

@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between flex-wrap mb-2">
                    <div>
                        <div class="text-muted small mb-1">Shifting schedule</div>
                        <h5 class="mb-0">{{ $schedule->slug }}</h5>
                    </div>
                    <div class="text-muted">
                        Default break: <strong>{{ (int) ($schedule->break_minutes ?? 0) }} min</strong>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th>Code</th>
                                <th>Name</th>
                                <th>Time In</th>
                                <th>Time Out</th>
                                <th>Break</th>
                                <th>Grace</th>
                                <th>Off</th>
                                <th style="width: 180px;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($schedule->shifts as $shift)
                                <tr>
                                    <td>{{ $shift->shift_code ?? '—' }}</td>
                                    <td>{{ $shift->name }}</td>
                                    <td>{{ \Carbon\Carbon::parse($shift->time_in)->format('g:i A') }}</td>
                                    <td>{{ \Carbon\Carbon::parse($shift->time_out)->format('g:i A') }}</td>
                                    <td>
                                        @php $b = $shift->breaks->first(); @endphp
                                        @if ($b)
                                            {{ \Carbon\Carbon::parse($b->break_start)->format('g:i A') }}
                                            – {{ \Carbon\Carbon::parse($b->break_end)->format('g:i A') }}
                                        @else
                                            {{ (int) ($shift->break_minutes ?? $schedule->break_minutes ?? 0) }} min
                                        @endif
                                    </td>
                                    <td>{{ (int) ($shift->grace_minutes ?? 0) }} min</td>
                                    <td>{{ ($shift->is_off ?? false) ? 'Yes' : 'No' }}</td>
                                    <td class="text-nowrap">
                                        <a href="#editShift{{ $shift->id }}" data-toggle="modal" class="btn btn-success btn-sm btn-flat">
                                            <i class="fa fa-edit"></i> Edit
                                        </a>
                                        <a href="#deleteShift{{ $shift->id }}" data-toggle="modal" class="btn btn-danger btn-sm btn-flat">
                                            <i class="fa fa-trash"></i> Delete
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted">No shifts yet. Add one.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Shift Modal -->
<div class="modal fade" id="addShift">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <h4 class="modal-title"><b>Add Shift</b></h4>
            <div class="modal-body text-left">
                <form class="form-horizontal" method="POST" action="{{ route('schedule.shifts.store', $schedule) }}">
                    @csrf
                    <div class="form-group">
                        <label for="shift_code" class="col-sm-3 control-label">Shift Code</label>
                        <input type="text" class="form-control" id="shift_code" name="shift_code" placeholder="e.g. DAY" required>
                        <small class="text-muted d-block mt-1">Used for rotation patterns (e.g. DAY,NIGHT,OFF).</small>
                    </div>
                    <div class="form-group">
                        <label for="shift_name" class="col-sm-3 control-label">Name</label>
                        <input type="text" class="form-control" id="shift_name" name="name" placeholder="e.g. Morning" required>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-3 control-label">Rest day</label>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="shift_is_off" name="is_off" value="1">
                            <label class="form-check-label" for="shift_is_off">Mark this shift as OFF (rest day)</label>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="shift_time_in" class="col-sm-3 control-label">Time In</label>
                        <input type="time" class="form-control" id="shift_time_in" name="time_in" required>
                    </div>
                    <div class="form-group">
                        <label for="shift_time_out" class="col-sm-3 control-label">Time Out</label>
                        <input type="time" class="form-control" id="shift_time_out" name="time_out" required>
                        <small class="text-muted d-block mt-1">Overnight shifts are allowed (e.g. 22:00 – 07:00).</small>
                    </div>
                    <div class="form-group">
                        <label for="shift_grace" class="col-sm-3 control-label">Grace (minutes)</label>
                        <input type="number" min="0" max="120" class="form-control" id="shift_grace" name="grace_minutes" value="0">
                    </div>
                    <div class="form-group">
                        <label for="shift_break" class="col-sm-3 control-label">Break (minutes)</label>
                        <input type="number" min="0" max="600" class="form-control" id="shift_break" name="break_minutes"
                            value="{{ (int) ($schedule->break_minutes ?? 0) }}">
                        <small class="text-muted d-block mt-1">Leave empty to use the schedule default break.</small>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-3 control-label">Break window</label>
                        <div class="d-flex" style="gap: 10px;">
                            <input type="time" class="form-control" name="break_start" placeholder="Start">
                            <input type="time" class="form-control" name="break_end" placeholder="End">
                        </div>
                        <small class="text-muted d-block mt-1">If set, the break is defined by start/end times instead of total minutes.</small>
                    </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default btn-flat pull-left" data-dismiss="modal">
                    <i class="fa fa-close"></i> Close
                </button>
                <button type="submit" class="btn btn-primary btn-flat">
                    <i class="fa fa-save"></i> Save
                </button>
                </form>
            </div>
        </div>
    </div>
</div>

@foreach ($schedule->shifts as $shift)
    @php $b = $shift->breaks->first(); @endphp
    <!-- Edit Shift Modal -->
    <div class="modal fade" id="editShift{{ $shift->id }}">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <h4 class="modal-title"><b>Edit Shift</b></h4>
                <div class="modal-body text-left">
                    <form class="form-horizontal" method="POST" action="{{ route('schedule.shifts.update', [$schedule, $shift]) }}">
                        @csrf
                        @method('PUT')
                        <div class="form-group">
                            <label class="col-sm-3 control-label">Shift Code</label>
                            <input type="text" class="form-control" name="shift_code" value="{{ $shift->shift_code }}" required>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">Name</label>
                            <input type="text" class="form-control" name="name" value="{{ $shift->name }}" required>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">Rest day</label>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="edit_off_{{ $shift->id }}" name="is_off" value="1" {{ ($shift->is_off ?? false) ? 'checked' : '' }}>
                                <label class="form-check-label" for="edit_off_{{ $shift->id }}">OFF (rest day)</label>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">Time In</label>
                            <input type="time" class="form-control" name="time_in" value="{{ $shift->time_in }}" required>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">Time Out</label>
                            <input type="time" class="form-control" name="time_out" value="{{ $shift->time_out }}" required>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">Grace (minutes)</label>
                            <input type="number" min="0" max="120" class="form-control" name="grace_minutes" value="{{ (int) ($shift->grace_minutes ?? 0) }}">
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">Break (minutes)</label>
                            <input type="number" min="0" max="600" class="form-control" name="break_minutes"
                                value="{{ $shift->break_minutes }}">
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">Break window</label>
                            <div class="d-flex" style="gap: 10px;">
                                <input type="time" class="form-control" name="break_start" value="{{ $b?->break_start }}">
                                <input type="time" class="form-control" name="break_end" value="{{ $b?->break_end }}">
                            </div>
                        </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default btn-flat pull-left" data-dismiss="modal">
                        <i class="fa fa-close"></i> Close
                    </button>
                    <button type="submit" class="btn btn-success btn-flat">
                        <i class="fa fa-check-square-o"></i> Update
                    </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Shift Modal -->
    <div class="modal fade" id="deleteShift{{ $shift->id }}">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header" style="align-items: center">
                    <h4 class="modal-title">Delete Shift</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form class="form-horizontal" method="POST" action="{{ route('schedule.shifts.destroy', [$schedule, $shift]) }}">
                        @csrf
                        @method('DELETE')
                        <div class="text-center">
                            <h6>Are you sure you want to delete:</h6>
                            <h2 class="bold">{{ $shift->name }}</h2>
                        </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default btn-flat pull-left" data-dismiss="modal">
                        <i class="fa fa-close"></i> Close
                    </button>
                    <button type="submit" class="btn btn-danger btn-flat">
                        <i class="fa fa-trash"></i> Delete
                    </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endforeach

@endsection

