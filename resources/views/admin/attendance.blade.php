@extends('layouts.master')

@section('css')
    <style>
        .attendance-logs-card .logs-title {
            font-size: 1.1rem;
            font-weight: 600;
        }
        .attendance-logs-table-wrap {
            border: 1px solid #e9ecef;
            border-radius: 6px;
            overflow: hidden;
        }
        .attendance-logs-table thead th {
            background: #f1f3f5;
            color: #343a40;
            font-weight: 600;
            font-size: 0.8125rem;
            text-transform: uppercase;
            letter-spacing: 0.02em;
            border-bottom: 2px solid #dee2e6;
            white-space: nowrap;
            vertical-align: middle;
            padding: 0.65rem 0.75rem;
        }
        .attendance-logs-table tbody td {
            vertical-align: middle;
            padding: 0.65rem 0.75rem;
            font-size: 0.875rem;
            border-color: #eef0f2;
        }
        .attendance-logs-table tbody tr:hover {
            background-color: #f8fafb;
        }
        .logs-date-primary {
            font-weight: 600;
            color: #212529;
        }
        .logs-date-sub {
            font-size: 0.75rem;
            color: #6c757d;
        }
        .logs-emp-name {
            font-weight: 500;
            color: #212529;
        }
        .logs-emp-id {
            font-size: 0.75rem;
            color: #868e96;
        }
        .logs-time {
            font-variant-numeric: tabular-nums;
            font-weight: 500;
        }
        .logs-schedule-label {
            font-size: 0.8125rem;
            font-weight: 500;
            color: #495057;
        }
        .logs-schedule-range {
            font-size: 0.75rem;
            color: #868e96;
        }
        .logs-empty {
            padding: 2.5rem 1rem;
            text-align: center;
            color: #868e96;
        }
        .dept-header-row td {
            background: #f1f3f5;
            font-weight: 600;
            color: #343a40;
            text-transform: none;
            letter-spacing: 0;
        }
        .logs-controls {
            display: flex;
            gap: 0.75rem;
            align-items: center;
            flex-wrap: wrap;
        }
        .logs-controls .form-inline {
            margin: 0;
        }
        .logs-controls label {
            white-space: nowrap;
        }
    </style>
@endsection

@section('breadcrumb')
    <div class="col-sm-6 text-left">
        <h4 class="page-title">Attendance logs</h4>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin') }}">Home</a></li>
            <li class="breadcrumb-item active">Attendance logs</li>
        </ol>
    </div>
@endsection

@section('content')
    @include('includes.flash')

    <div class="row">
        <div class="col-12">
            <div class="card attendance-logs-card border shadow-sm">
                <div class="card-body">
                    <div class="d-flex flex-wrap align-items-center justify-content-between mb-3">
                        <div>
                            <h4 class="logs-title mb-1">Daily clock records</h4>
                            <p class="text-muted mb-0 small">
                                Showing employees who recorded attendance on
                                <strong>{{ $selectedDate->format('M j, Y') }}</strong>.
                            </p>
                        </div>
                        <div class="logs-controls mt-2 mt-md-0">
                            <form method="GET" action="{{ route('attendance') }}" class="form-inline">
                                <label for="attendanceDate" class="mr-2 small text-muted mb-0">Select date</label>
                                <input
                                    type="date"
                                    id="attendanceDate"
                                    name="date"
                                    class="form-control form-control-sm"
                                    value="{{ $selectedDate->toDateString() }}"
                                >
                            </form>
                            @if (($attendancesCount ?? 0) > 0)
                                <span class="badge badge-primary badge-pill px-3 py-2">
                                    {{ $attendancesCount }} {{ Str::plural('employee', $attendancesCount) }}
                                </span>
                            @endif
                        </div>
                    </div>

                    @if (($attendancesCount ?? 0) === 0)
                        <div class="logs-empty border rounded bg-light">
                            <p class="mb-0">No attendance records for this date.</p>
                        </div>
                    @else
                        <div class="table-responsive attendance-logs-table-wrap">
                            <table class="table table-hover table-sm mb-0 attendance-logs-table">
                                <thead>
                                    <tr>
                                        <th scope="col">Employee</th>
                                        <th scope="col">Time in</th>
                                        <th scope="col">Time out</th>
                                        <th scope="col">Schedule</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($attendancesByDept as $dept => $deptAttendances)
                                        <tr class="dept-header-row">
                                            <td colspan="4">{{ $dept }}</td>
                                        </tr>
                                        @foreach ($deptAttendances as $attendance)
                                            @php
                                                $rawIn = $attendance->time_in;
                                                $rawOut = $attendance->time_out;
                                                $timeIn = $rawIn ? \Carbon\Carbon::parse(explode(',', (string) $rawIn)[0])->format('g:i A') : null;
                                                $timeOut = $rawOut ? \Carbon\Carbon::parse(explode(',', (string) $rawOut)[0])->format('g:i A') : null;
                                                $employee = $attendance->employee;
                                                $sched = $employee ? $employee->schedules()->first() : null;
                                            @endphp
                                            <tr>
                                                <td>
                                                    <div class="logs-emp-name">{{ $employee?->name ?? '—' }}</div>
                                                    <div class="logs-date-sub">{{ $employee?->position ?? '—' }}</div>
                                                </td>
                                                <td>
                                                    @if ($timeIn)
                                                        <span class="logs-time text-success">{{ $timeIn }}</span>
                                                    @else
                                                        <span class="text-muted">—</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if ($timeOut)
                                                        <span class="logs-time text-primary">{{ $timeOut }}</span>
                                                    @else
                                                        <span class="badge badge-light border text-muted">No checkout</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if ($sched instanceof \App\Models\Schedule)
                                                        <div class="logs-schedule-label">{{ $sched->slug }}</div>
                                                        <div class="logs-schedule-range">
                                                            {{ \Carbon\Carbon::parse($sched->time_in)->format('g:i A') }}
                                                            – {{ \Carbon\Carbon::parse($sched->time_out)->format('g:i A') }}
                                                        </div>
                                                    @else
                                                        <span class="text-muted">—</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        (function () {
            var input = document.getElementById('attendanceDate');
            if (!input) return;
            input.addEventListener('change', function () {
                if (!input.value) return;
                var url = new URL(window.location.href);
                url.searchParams.set('date', input.value);
                window.location.href = url.toString();
            });
        })();
    </script>
@endsection
