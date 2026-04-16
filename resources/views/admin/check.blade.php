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

    <div class="card attendance-sheet-card border shadow-sm">
        <div class="card-body pb-2">
            <div class="d-flex flex-wrap align-items-center justify-content-between mb-3">
                <div>
                    <h4 class="card-title mb-1">Monthly attendance</h4>
                    <p class="text-muted mb-0 small">
                        Automatically recorded from employee Time In/Time Out and approved leave requests.
                        <span class="d-none d-md-inline">Scroll horizontally to see all days.</span>
                    </p>
                </div>
            </div>
            <div class="attendance-sheet-legend mb-3">
                <span><span class="badge-dot" style="background:#0acf97;"></span> Present</span>
                <span><span class="badge-dot" style="background:#f9bc0b;"></span> Leave (Approved)</span>
                <span class="text-muted">{{ $today->format('F Y') }}</span>
            </div>

            <div class="attendance-sheet-scroll">
                <table class="table table-sm table-bordered attendance-sheet-table">
                    <thead>
                        <tr>
                            <th class="col-employee">Employee</th>
                            <th class="col-meta">Position</th>
                            @foreach ($dates as $d)
                                <th class="col-day {{ $d->isWeekend() ? 'weekend' : '' }}" title="{{ $d->format('l, M j, Y') }}">
                                    <span class="day-num">{{ $d->day }}</span>
                                    {{ $d->format('D') }}
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($employeesByDept as $dept => $deptEmployees)
                            <tr>
                                <td class="col-employee" colspan="{{ 2 + count($dates) }}" style="background:#f1f3f5; font-weight:600;">
                                    {{ $dept }}
                                </td>
                            </tr>
                            @foreach ($deptEmployees as $employee)
                                <tr>
                                    <td class="col-employee">{{ $employee->name }}</td>
                                    <td class="col-meta text-muted">{{ $employee->position ?? '—' }}</td>
                                    @foreach ($dates as $d)
                                        @php
                                            $ymd = $d->format('Y-m-d');
                                            $isLeave = !empty($leaveByEmp[$employee->id][$ymd]);
                                            $isPresent = !empty($attendanceByEmp[$employee->id][$ymd]);
                                        @endphp
                                        <td class="col-day-cell {{ $d->isWeekend() ? 'weekend' : '' }}">
                                            @if ($isLeave)
                                                <span class="badge badge-warning">L</span>
                                            @elseif ($isPresent)
                                                <span class="badge badge-success">P</span>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
