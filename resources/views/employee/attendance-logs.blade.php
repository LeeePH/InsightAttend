@extends('layouts.master')

@section('css')
    <style>
        .emp-att-hero {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 1rem;
            flex-wrap: wrap;
        }
        .emp-att-hero .title {
            font-size: 1.15rem;
            font-weight: 600;
            margin-bottom: 0.25rem;
        }
        .emp-att-hero .subtitle {
            margin: 0;
            color: #6c757d;
            font-size: 0.875rem;
        }
        .emp-att-controls {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            flex-wrap: wrap;
        }
        .emp-att-controls label {
            margin: 0;
            color: #6c757d;
            font-size: 0.8125rem;
            white-space: nowrap;
        }
        .emp-att-card-title {
            font-size: 0.95rem;
            font-weight: 600;
            margin: 0;
        }
        .emp-att-badge {
            font-variant-numeric: tabular-nums;
            font-weight: 600;
        }
        .emp-att-mini-table thead th {
            background: #f1f3f5;
            font-weight: 600;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.02em;
            border-bottom: 2px solid #dee2e6;
            white-space: nowrap;
        }
        .emp-att-mini-table td {
            vertical-align: middle;
            font-size: 0.9rem;
        }
        .emp-month-grid {
            display: grid;
            grid-template-columns: repeat(7, minmax(0, 1fr));
            gap: 8px;
        }
        .emp-month-day {
            border: 1px solid #eef0f2;
            border-radius: 8px;
            padding: 10px 8px;
            background: #fff;
            min-height: 64px;
        }
        .emp-month-day .num {
            font-weight: 700;
            font-size: 0.9rem;
            color: #343a40;
        }
        .emp-month-day .dow {
            font-size: 0.7rem;
            color: #868e96;
        }
        .emp-month-day.present {
            border-color: rgba(2, 164, 153, 0.35);
            background: rgba(2, 164, 153, 0.06);
        }
        .emp-month-day.present .status {
            display: inline-block;
            margin-top: 6px;
            font-size: 0.75rem;
            padding: 2px 8px;
            border-radius: 999px;
            background: rgba(2, 164, 153, 0.16);
            color: #027a71;
            font-weight: 600;
        }
        .emp-month-day.empty .status {
            display: inline-block;
            margin-top: 6px;
            font-size: 0.75rem;
            padding: 2px 8px;
            border-radius: 999px;
            background: rgba(134, 142, 150, 0.12);
            color: #6c757d;
            font-weight: 600;
        }
        .emp-month-weekday {
            font-size: 0.72rem;
            font-weight: 600;
            color: #6c757d;
            text-transform: uppercase;
            letter-spacing: 0.02em;
            padding: 0 2px 6px;
        }
    </style>
@endsection

@section('breadcrumb')
    <div class="col-sm-6 text-left">
        <h4 class="page-title">Attendance logs</h4>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('employee.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Attendance logs</li>
        </ol>
    </div>
@endsection

@section('content')
    @include('includes.flash')

    @php
        $selectedDate = $selected->copy();
        $monthStart = $selected->copy()->startOfMonth();
        $daysInMonth = $selected->daysInMonth;

        $rawIn = $daily?->time_in;
        $rawOut = $daily?->time_out;
        $timeIn = $rawIn ? \Carbon\Carbon::parse(explode(',', (string) $rawIn)[0])->format('g:i A') : null;
        $timeOut = $rawOut ? \Carbon\Carbon::parse(explode(',', (string) $rawOut)[0])->format('g:i A') : null;
    @endphp

    <div class="row">
        <div class="col-12">
            <div class="card border shadow-sm">
                <div class="card-body">
                    <div class="emp-att-hero mb-3">
                        <div>
                            <div class="title">My attendance</div>
                            <p class="subtitle">
                                Viewing <strong>{{ $selectedDate->format('M j, Y') }}</strong> and {{ $selectedDate->format('F Y') }} summary.
                            </p>
                        </div>
                        <div class="emp-att-controls">
                            <label for="empAttendanceDate">Select date</label>
                            <input type="date" id="empAttendanceDate" class="form-control form-control-sm" value="{{ $selectedDate->toDateString() }}">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-4 mb-3">
                            <div class="border rounded p-3 h-100">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <h6 class="emp-att-card-title">Daily</h6>
                                    <span class="badge badge-light border">{{ $selectedDate->format('D') }}</span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <div class="text-muted small">Time in</div>
                                        <div class="emp-att-badge text-success">{{ $timeIn ?? '—' }}</div>
                                    </div>
                                    <div>
                                        <div class="text-muted small">Time out</div>
                                        <div class="emp-att-badge text-primary">{{ $timeOut ?? '—' }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-8 mb-3">
                            <div class="border rounded p-3 h-100">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <h6 class="emp-att-card-title">Recent</h6>
                                    <span class="text-muted small">Last {{ $recent->count() }} days with records</span>
                                </div>
                                @if ($recent->isEmpty())
                                    <div class="text-muted">No recent attendance records.</div>
                                @else
                                    <div class="table-responsive">
                                        <table class="table table-sm mb-0 emp-att-mini-table">
                                            <thead>
                                                <tr>
                                                    <th>Date</th>
                                                    <th>Time in</th>
                                                    <th>Time out</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($recent as $row)
                                                    @php
                                                        $d = \Carbon\Carbon::parse($row->attendance_date);
                                                        $rIn = $row->time_in ? \Carbon\Carbon::parse(explode(',', (string) $row->time_in)[0])->format('g:i A') : null;
                                                        $rOut = $row->time_out ? \Carbon\Carbon::parse(explode(',', (string) $row->time_out)[0])->format('g:i A') : null;
                                                    @endphp
                                                    <tr>
                                                        <td>
                                                            <div class="font-weight-600">{{ $d->format('M j, Y') }}</div>
                                                            <div class="text-muted small">{{ $d->format('l') }}</div>
                                                        </td>
                                                        <td class="text-success font-weight-600">{{ $rIn ?? '—' }}</td>
                                                        <td class="text-primary font-weight-600">{{ $rOut ?? '—' }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="border rounded p-3">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <h6 class="emp-att-card-title mb-0">Monthly</h6>
                            <span class="text-muted small">{{ $selectedDate->format('F Y') }}</span>
                        </div>

                        <div class="emp-month-grid mb-2">
                            @foreach (['Sun','Mon','Tue','Wed','Thu','Fri','Sat'] as $wd)
                                <div class="emp-month-weekday">{{ $wd }}</div>
                            @endforeach
                        </div>

                        @php
                            $firstDow = (int) $monthStart->dayOfWeek; // 0=Sun
                            $totalCells = $firstDow + $daysInMonth;
                            $rows = (int) ceil($totalCells / 7);
                            $cells = $rows * 7;
                        @endphp

                        <div class="emp-month-grid">
                            @for ($i = 0; $i < $cells; $i++)
                                @php
                                    $dayNum = $i - $firstDow + 1;
                                @endphp
                                @if ($dayNum < 1 || $dayNum > $daysInMonth)
                                    <div></div>
                                @else
                                    @php
                                        $d = $monthStart->copy()->day($dayNum);
                                        $ymd = $d->toDateString();
                                        $present = !empty($presentDays[$ymd]);
                                    @endphp
                                    <div class="emp-month-day {{ $present ? 'present' : 'empty' }}">
                                        <div class="d-flex align-items-baseline justify-content-between">
                                            <div class="num">{{ $dayNum }}</div>
                                            <div class="dow">{{ $d->format('D') }}</div>
                                        </div>
                                        @if ($present)
                                            <span class="status">Present</span>
                                        @else
                                            <span class="status">—</span>
                                        @endif
                                    </div>
                                @endif
                            @endfor
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        (function () {
            var input = document.getElementById('empAttendanceDate');
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

