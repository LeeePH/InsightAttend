@extends('layouts.master')

@section('css')
    <style>
        .attendance-sheet-card .card-title {
            font-size: 1.1rem;
            font-weight: 600;
        }
        .attendance-sheet-legend {
            font-size: 0.8125rem;
            color: #6c757d;
        }
        .attendance-sheet-legend span {
            display: inline-flex;
            align-items: center;
            margin-right: 1.25rem;
        }
        .attendance-sheet-legend .badge-dot {
            width: 10px;
            height: 10px;
            border-radius: 2px;
            margin-right: 6px;
        }
        .attendance-sheet-scroll {
            overflow-x: auto;
            overflow-y: auto;
            max-height: calc(100vh - 220px);
            border: 1px solid #e9ecef;
            border-radius: 6px;
            -webkit-overflow-scrolling: touch;
        }
        .attendance-sheet-table {
            margin-bottom: 0;
            font-size: 0.8125rem;
            border-collapse: separate;
            border-spacing: 0;
        }
        .attendance-sheet-table thead th {
            position: sticky;
            top: 0;
            z-index: 5;
            background: #f1f3f5;
            color: #343a40;
            font-weight: 600;
            text-align: center;
            vertical-align: middle;
            padding: 0.5rem 0.35rem;
            border-bottom: 2px solid #dee2e6;
            white-space: nowrap;
            box-shadow: 0 1px 0 #dee2e6;
        }
        .attendance-sheet-table thead th.col-employee,
        .attendance-sheet-table tbody td.col-employee {
            position: sticky;
            left: 0;
            min-width: 9rem;
            max-width: 12rem;
            box-shadow: 2px 0 6px rgba(0, 0, 0, 0.04);
        }
        .attendance-sheet-table thead th.col-employee {
            text-align: left;
            z-index: 7;
        }
        .attendance-sheet-table tbody td.col-employee {
            z-index: 3;
        }
        .attendance-sheet-table thead th.col-meta {
            text-align: left;
            min-width: 6rem;
        }
        .attendance-sheet-table thead th.col-day {
            min-width: 3.25rem;
            font-size: 0.7rem;
            line-height: 1.2;
        }
        .attendance-sheet-table thead th.col-day .day-num {
            display: block;
            font-size: 0.85rem;
            font-weight: 700;
        }
        .attendance-sheet-table thead th.col-day.weekend {
            background: #e9ecef;
        }
        .attendance-sheet-table tbody td {
            vertical-align: middle;
            padding: 0.35rem 0.4rem;
            border-color: #e9ecef;
        }
        .attendance-sheet-table tbody td.col-employee {
            font-weight: 500;
            background: #fff;
        }
        .attendance-sheet-table tbody tr:nth-child(even) td.col-employee,
        .attendance-sheet-table tbody tr:nth-child(even) td.col-meta {
            background: #f8f9fa;
        }
        .attendance-sheet-table tbody td.col-day-cell {
            text-align: center;
            padding: 0.25rem;
            background: #fff;
        }
        .attendance-sheet-table tbody tr:nth-child(even) td.col-day-cell {
            background: #fafbfc;
        }
        .attendance-sheet-table tbody td.col-day-cell.weekend {
            background: #f1f3f5;
        }
        .attendance-sheet-table tbody tr:nth-child(even) td.col-day-cell.weekend {
            background: #e9ecef;
        }
        .sheet-cell-checks {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 2px;
        }
        .sheet-cell-checks .custom-control {
            min-height: 1rem;
            padding-left: 1.1rem;
            margin: 0;
        }
        .sheet-cell-checks .custom-control-label {
            font-size: 0.65rem;
            line-height: 1rem;
            padding-top: 1px;
            color: #495057;
        }
        .sheet-cell-checks .custom-control-label::before,
        .sheet-cell-checks .custom-control-label::after {
            width: 0.85rem;
            height: 0.85rem;
            top: 0.1rem;
            left: -1.1rem;
        }
    </style>
@endsection

@section('breadcrumb')
    <div class="col-sm-6 text-left">
        <h4 class="page-title">Attendance Sheet</h4>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin') }}">Home</a></li>
            <li class="breadcrumb-item active">Attendance Sheet</li>
        </ol>
    </div>
@endsection

@section('content')
    @php
        $today = today();
        $dates = [];
        for ($i = 1; $i < $today->daysInMonth + 1; ++$i) {
            $dates[] = \Carbon\Carbon::createFromDate($today->year, $today->month, $i);
        }
    @endphp

    <form action="{{ route('check_store') }}" method="post">
        @csrf
        <div class="card attendance-sheet-card border shadow-sm">
            <div class="card-body pb-2">
                <div class="d-flex flex-wrap align-items-center justify-content-between mb-3">
                    <div>
                        <h4 class="card-title mb-1">Monthly attendance</h4>
                        <p class="text-muted mb-0 small">
                            Mark <strong>Present</strong> or <strong>Leave</strong> per day for each employee, then save.
                            <span class="d-none d-md-inline">Scroll horizontally to see all days.</span>
                        </p>
                    </div>
                    <button type="submit" class="btn btn-success mt-2 mt-md-0">
                        <i class="mdi mdi-content-save-outline mr-1"></i> Save sheet
                    </button>
                </div>
                <div class="attendance-sheet-legend mb-3">
                    <span><span class="badge-dot" style="background:#0acf97;"></span> Present</span>
                    <span><span class="badge-dot" style="background:#f9bc0b;"></span> Leave</span>
                    <span class="text-muted">{{ $today->format('F Y') }}</span>
                </div>

                <div class="attendance-sheet-scroll">
                    <table class="table table-sm table-bordered attendance-sheet-table">
                        <thead>
                            <tr>
                                <th class="col-employee">Employee</th>
                                <th class="col-meta">Role</th>
                                <th class="col-meta">ID</th>
                                @foreach ($dates as $d)
                                    @php $ymd = $d->format('Y-m-d'); @endphp
                                    <th class="col-day {{ $d->isWeekend() ? 'weekend' : '' }}" title="{{ $d->format('l, M j, Y') }}">
                                        <span class="day-num">{{ $d->day }}</span>
                                        {{ $d->format('D') }}
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($employees as $employee)
                                <input type="hidden" name="emp_id" value="{{ $employee->id }}">
                                <tr>
                                    <td class="col-employee">{{ $employee->name }}</td>
                                    <td class="col-meta text-muted">{{ $employee->position ?? '—' }}</td>
                                    <td class="col-meta"><small class="text-muted">{{ $employee->id }}</small></td>
                                    @foreach ($dates as $d)
                                        @php
                                            $date_picker = $d->format('Y-m-d');
                                            $check_attd = \App\Models\Attendance::query()
                                                ->where('emp_id', $employee->id)
                                                ->where('attendance_date', $date_picker)
                                                ->first();
                                            $check_leave = \App\Models\Leave::query()
                                                ->where('emp_id', $employee->id)
                                                ->where('leave_date', $date_picker)
                                                ->first();
                                        @endphp
                                        <td class="col-day-cell {{ $d->isWeekend() ? 'weekend' : '' }}">
                                            <div class="sheet-cell-checks">
                                                <div class="custom-control custom-checkbox">
                                                    <input
                                                        class="custom-control-input"
                                                        name="attd[{{ $date_picker }}][{{ $employee->id }}]"
                                                        type="checkbox"
                                                        id="attd-{{ $employee->id }}-{{ $date_picker }}"
                                                        value="1"
                                                        @if (isset($check_attd)) checked @endif
                                                    >
                                                    <label class="custom-control-label" for="attd-{{ $employee->id }}-{{ $date_picker }}">P</label>
                                                </div>
                                                <div class="custom-control custom-checkbox">
                                                    <input
                                                        class="custom-control-input"
                                                        name="leave[{{ $date_picker }}][{{ $employee->id }}]"
                                                        type="checkbox"
                                                        id="leave-{{ $employee->id }}-{{ $date_picker }}"
                                                        value="1"
                                                        @if (isset($check_leave)) checked @endif
                                                    >
                                                    <label class="custom-control-label" for="leave-{{ $employee->id }}-{{ $date_picker }}">L</label>
                                                </div>
                                            </div>
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </form>
@endsection
