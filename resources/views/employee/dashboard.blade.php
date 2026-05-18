@extends('layouts.master')

@section('css')
<style>
    :root {
        --emp-bg:           #f8f1eb;
        --emp-card:         #ffffff;
        --emp-text:         #3e2412;
        --emp-muted:        #7b5a45;
        --emp-border:       #e2cdbd;
        --emp-accent:       #8B4513;
        --emp-accent-dark:  #6f330d;
        --emp-accent-soft:  #f4e4d8;
    }

    body { background: var(--emp-bg); }

    /* ── Hero ── */
    .dash-hero {
        border-radius: 18px;
        padding: 1.6rem 1.8rem;
        background: linear-gradient(135deg, #4c2d17 0%, #8B4513 100%);
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 1rem;
    }
    .dash-hero h3 { margin: 0; font-weight: 700; color: #fff !important; }
    .dash-hero .hero-sub { color: rgba(255,245,236,.78); font-size: .9rem; margin-top: .25rem; }
    .dash-hero .hero-date {
        background: rgba(255,255,255,.15);
        border-radius: 10px;
        padding: .55rem 1rem;
        font-size: .85rem;
        color: #fff;
        white-space: nowrap;
    }
    .dash-hero .hero-clock {
        font-size: 1.05rem;
        font-weight: 700;
        color: #fff;
        letter-spacing: .03em;
    }
    .dash-hero .hero-tz {
        font-size: .72rem;
        color: rgba(255,245,236,.65);
        margin-top: .1rem;
    }

    /* ── Cards ── */
    .emp-card {
        border: 1px solid var(--emp-border);
        border-radius: 14px;
        background: var(--emp-card);
        box-shadow: 0 6px 20px rgba(62,36,18,.07);
    }
    .emp-card .card-body { padding: 1.25rem 1.4rem; }
    .section-title {
        color: var(--emp-text);
        font-weight: 700;
        font-size: 1rem;
        margin-bottom: 1rem;
    }

    /* ── Donut stat cards ── */
    .stat-ring-card {
        border-radius: 14px;
        padding: 1.1rem 1.25rem;
        display: flex;
        align-items: center;
        gap: 1rem;
        color: #fff;
    }
    .stat-ring-card.present  { background: linear-gradient(145deg,#8B4513,#b66a33); }
    .stat-ring-card.late     { background: linear-gradient(145deg,#354b68,#223245); }
    .stat-ring-card.absent   { background: linear-gradient(145deg,#6b342f,#47201d); }
    .stat-ring-card .ring-wrap { position: relative; flex-shrink: 0; }
    .stat-ring-card canvas   { display: block; }
    .stat-ring-card .ring-num {
        position: absolute; inset: 0;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.15rem; font-weight: 700;
    }
    .stat-ring-card .stat-info .stat-label {
        font-size: .75rem; text-transform: uppercase;
        letter-spacing: .07em; opacity: .8;
    }
    .stat-ring-card .stat-info .stat-val {
        font-size: 1.7rem; font-weight: 700; line-height: 1.1;
    }
    .stat-ring-card .stat-info .stat-sub {
        font-size: .78rem; opacity: .75; margin-top: .15rem;
    }

    /* ── Today card ── */
    .today-grid { display: grid; grid-template-columns: 1fr 1fr; gap: .75rem; }
    .today-item .ti-label { font-size: .78rem; color: var(--emp-muted); text-transform: uppercase; letter-spacing: .06em; }
    .today-item .ti-val   { font-weight: 700; color: var(--emp-text); font-size: .97rem; margin-top: .15rem; }

    /* ── Status badge ── */
    .status-pill {
        display: inline-flex; align-items: center; gap: .45rem;
        padding: .4rem .9rem; border-radius: 999px;
        font-weight: 600; font-size: .82rem; margin-bottom: 1rem;
    }
    .status-pill.present { background:#e4f4ea; color:#22633d; }
    .status-pill.late    { background:#fff2d9; color:#8c5a07; }
    .status-pill.absent  { background:#fde6e2; color:#9b2f24; }
    .status-pill .dot    { width:8px; height:8px; border-radius:50%; background:currentColor; }

    /* ── Schedule table ── */
    .sched-table th {
        background: #f5e8de; color: var(--emp-text);
        border-color: var(--emp-border) !important; font-weight: 700;
    }
    .sched-table td {
        border-color: var(--emp-border) !important;
        color: var(--emp-text); vertical-align: middle;
    }
    .sched-row-today { background: rgba(139,69,19,.08) !important; box-shadow: inset 3px 0 0 #8B4513; }
    .day-badge {
        display: inline-block; padding: .25rem .6rem;
        border-radius: 6px; font-size: .78rem; font-weight: 700;
        background: var(--emp-accent-soft); color: var(--emp-accent-dark);
    }
    .day-badge.today-day { background: #8B4513; color: #fff; }

    /* ── Attendance log table ── */
    .log-table th {
        background: #f5e8de; color: var(--emp-text);
        border-color: var(--emp-border) !important; font-weight: 700;
        font-size: .82rem;
    }
    .log-table td {
        border-color: var(--emp-border) !important;
        color: var(--emp-text); vertical-align: middle; font-size: .88rem;
    }
    .badge-soft {
        display: inline-block; padding: .3rem .7rem;
        border-radius: 999px; font-weight: 600; font-size: .76rem;
    }
    .badge-soft.badge-present, .badge-soft.badge-approved { background:#e4f4ea; color:#22633d; }
    .badge-soft.badge-late,    .badge-soft.badge-pending  { background:#fff2d9; color:#8c5a07; }
    .badge-soft.badge-absent,  .badge-soft.badge-rejected { background:#fde6e2; color:#9b2f24; }

    /* ── Bar chart ── */
    .bar-chart-wrap { display: flex; align-items: flex-end; gap: 4px; height: 56px; }
    .bar-col { flex: 1; border-radius: 4px 4px 0 0; min-width: 6px; transition: opacity .2s; }
    .bar-col:hover { opacity: .75; }

    /* ── Requests ── */
    .req-table th {
        background: #f5e8de; color: var(--emp-text);
        border-color: var(--emp-border) !important; font-weight: 700; font-size: .82rem;
    }
    .req-table td {
        border-color: var(--emp-border) !important;
        color: var(--emp-text); vertical-align: middle; font-size: .88rem;
    }

    /* ── Schedule action buttons ── */
    .sched-actions { display: flex; gap: .5rem; flex-wrap: wrap; }
    .sched-actions .btn { border-radius: 999px; font-size: .82rem; font-weight: 600; padding: .35rem .9rem; }
</style>
@endsection

@section('breadcrumb')
<div class="col-sm-6 text-left">
    <h4 class="page-title">Dashboard</h4>
</div>
@endsection

@section('content')
@include('includes.flash')

<div class="employee-dashboard">

    {{-- ── Hero ── --}}
    <div class="dash-hero mb-4">
        <div>
            <h3>Welcome back, {{ $employee->name }}</h3>
            <div class="hero-sub" id="heroPHDate">{{ now()->setTimezone('Asia/Manila')->format('l, F d, Y') }}</div>
        </div>
        <div class="hero-date text-center">
            <div class="hero-clock" id="heroPHTime">--:-- --</div>
            <div class="hero-tz">Philippine Standard Time</div>
            @php
                $statusClass = $statusLabel === 'Present' ? 'present' : ($statusLabel === 'Late' ? 'late' : 'absent');
            @endphp
            <div class="mt-2">
                <span class="status-pill {{ $statusClass }}" style="margin:0;background:rgba(255,255,255,.18);color:#fff;">
                    <span class="dot" style="background:#fff;"></span>
                    Today: {{ $statusLabel }}
                </span>
            </div>
        </div>
    </div>

    {{-- ── Stat ring cards ── --}}
    <div class="row mb-4">
        <div class="col-xl-4 col-md-4 mb-3">
            <div class="stat-ring-card present">
                <div class="ring-wrap">
                    <canvas id="ringPresent" width="64" height="64"></canvas>
                    <div class="ring-num">{{ $presentDays }}</div>
                </div>
                <div class="stat-info">
                    <div class="stat-label">Present Days</div>
                    <div class="stat-val">{{ $presentDays }}</div>
                    <div class="stat-sub">This month</div>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-md-4 mb-3">
            <div class="stat-ring-card late">
                <div class="ring-wrap">
                    <canvas id="ringLate" width="64" height="64"></canvas>
                    <div class="ring-num">{{ $lateCount }}</div>
                </div>
                <div class="stat-info">
                    <div class="stat-label">Late Records</div>
                    <div class="stat-val">{{ $lateCount }}</div>
                    <div class="stat-sub">This month</div>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-md-4 mb-3">
            <div class="stat-ring-card absent">
                <div class="ring-wrap">
                    <canvas id="ringAbsent" width="64" height="64"></canvas>
                    <div class="ring-num">{{ $absenceCount }}</div>
                </div>
                <div class="stat-info">
                    <div class="stat-label">Absences</div>
                    <div class="stat-val">{{ $absenceCount }}</div>
                    <div class="stat-sub">This month</div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Today + Schedule ── --}}
    <div class="row mb-4">

        {{-- Today's Attendance ── --}}
        <div class="col-xl-4 mb-4">
            <div class="emp-card h-100">
                <div class="card-body">
                    <h5 class="section-title">Today's Attendance</h5>

                    @php
                        $todayStatusClass = $statusLabel === 'Present' ? 'present' : ($statusLabel === 'Late' ? 'late' : 'absent');
                    @endphp
                    <span class="status-pill {{ $todayStatusClass }}">
                        <span class="dot"></span>{{ $statusLabel }}
                        <span style="opacity:.6;font-weight:400;">· {{ now()->format('M d, Y') }}</span>
                    </span>

                    <div class="today-grid">
                        <div class="today-item">
                            <div class="ti-label">Time In</div>
                            <div class="ti-val">{{ $timeIn ? $timeIn->format('h:i A') : '—' }}</div>
                        </div>
                        <div class="today-item">
                            <div class="ti-label">Time Out</div>
                            <div class="ti-val">{{ $timeOut ? $timeOut->format('h:i A') : '—' }}</div>
                        </div>
                        <div class="today-item">
                            <div class="ti-label">Worked Hours</div>
                            <div class="ti-val">{{ !is_null($workedSeconds) ? gmdate('H:i', (int)$workedSeconds) : '—' }}</div>
                        </div>
                    </div>

                    {{-- ── Face Recognition Time In / Time Out ── --}}
                    <div class="mt-3 mb-3" id="attendanceActionWrap">
                        @if(!$timeIn)
                            {{-- ── Not timed in yet ── --}}
                            @if($timeInWindowOpen)
                                {{-- Window is open: show face scanner ── --}}
                                @if($employee->face_descriptor)
                                    <div id="faceTimeinWrap">
                                        <div style="font-size:.78rem;color:var(--emp-muted);margin-bottom:.4rem;text-align:center;">
                                            <i class="fa fa-camera mr-1"></i> Face verification required to Time In
                                        </div>
                                        @if($expectedStart)
                                            <div style="font-size:.75rem;color:var(--emp-muted);text-align:center;margin-bottom:.5rem;">
                                                Schedule: {{ $expectedStart->format('g:i A') }}
                                                @if($expectedEnd) – {{ $expectedEnd->format('g:i A') }} @endif
                                                &nbsp;·&nbsp; Grace until {{ $expectedStart->copy()->addMinutes(15)->format('g:i A') }}
                                            </div>
                                        @endif
                                        <div style="position:relative;width:100%;max-width:260px;height:195px;margin:0 auto;border-radius:10px;overflow:hidden;background:#000;border:2px solid var(--emp-border);">
                                            <video id="dashVideo" autoplay playsinline style="width:100%;height:100%;object-fit:cover;"></video>
                                            <canvas id="dashCanvas" style="position:absolute;top:0;left:0;width:100%;height:100%;"></canvas>
                                        </div>
                                        <div id="dashFaceStatus" class="text-center mt-2" style="font-size:.78rem;color:var(--emp-muted);min-height:1.2em;"></div>
                                        <form method="POST" action="{{ route('employee.timein') }}" id="dashTimeInForm" style="display:none;">
                                            @csrf
                                        </form>
                                    </div>
                                @else
                                    <div class="alert alert-warning py-2 px-3 mb-2" style="font-size:.8rem;border-radius:8px;">
                                        <i class="fa fa-exclamation-triangle mr-1"></i> No face registered. Register your face in your profile to use facial attendance.
                                    </div>
                                    <form method="POST" action="{{ route('employee.timein') }}">
                                        @csrf
                                        <button type="submit" class="btn btn-success btn-block font-weight-bold"
                                                style="border-radius:10px;padding:.6rem 1rem;font-size:.95rem;"
                                                onclick="return confirmAction(this, 'Time In', 'Record your time in now?')">
                                            <i class="fa fa-sign-in mr-1"></i> Time In
                                        </button>
                                    </form>
                                @endif
                            @else
                                {{-- Window not open: info + early time-in option ── --}}
                                <div class="text-center py-2" style="color:var(--emp-muted);font-size:.85rem;">
                                    <i class="fa fa-clock-o mr-1"></i>
                                    @if($expectedStart)
                                        Time In opens at {{ $expectedStart->copy()->subMinutes(5)->format('g:i A') }}
                                        <div style="font-size:.75rem;margin-top:.25rem;">
                                            Schedule: {{ $expectedStart->format('g:i A') }}
                                            @if($expectedEnd) – {{ $expectedEnd->format('g:i A') }} @endif
                                        </div>
                                    @else
                                        No schedule assigned for today.
                                    @endif
                                </div>

                                {{-- Early Time In option ── --}}
                                <div class="text-center mt-2">
                                    <button type="button" class="btn btn-sm btn-outline-secondary"
                                            style="border-radius:999px;font-size:.75rem;"
                                            onclick="document.getElementById('earlyTimeinWrap').style.display='block';this.style.display='none';">
                                        <i class="fa fa-sign-in mr-1"></i> Early Time In
                                    </button>
                                </div>
                                <div id="earlyTimeinWrap" style="display:none;margin-top:.75rem;">
                                    @if($employee->face_descriptor)
                                        <div style="font-size:.75rem;color:var(--emp-muted);text-align:center;margin-bottom:.4rem;">
                                            <i class="fa fa-camera mr-1"></i> Face verification — Early Time In
                                        </div>
                                        <div style="position:relative;width:100%;max-width:260px;height:195px;margin:0 auto;border-radius:10px;overflow:hidden;background:#000;border:2px solid var(--emp-border);">
                                            <video id="dashVideo" autoplay playsinline style="width:100%;height:100%;object-fit:cover;"></video>
                                            <canvas id="dashCanvas" style="position:absolute;top:0;left:0;width:100%;height:100%;"></canvas>
                                        </div>
                                        <div id="dashFaceStatus" class="text-center mt-2" style="font-size:.78rem;color:var(--emp-muted);min-height:1.2em;"></div>
                                        <form method="POST" action="{{ route('employee.timein') }}" id="dashTimeInForm" style="display:none;">
                                            @csrf
                                        </form>
                                    @else
                                        <form method="POST" action="{{ route('employee.timein') }}">
                                            @csrf
                                            <button type="submit" class="btn btn-success btn-block font-weight-bold"
                                                    style="border-radius:10px;padding:.5rem 1rem;font-size:.9rem;"
                                                    onclick="return confirmAction(this, 'Early Time In', 'Record an early time in now?')">
                                                <i class="fa fa-sign-in mr-1"></i> Confirm Early Time In
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            @endif

                        @elseif(!$timeOut)
                            {{-- ── Timed in, not out yet ── --}}
                            <div class="mb-2 p-2 text-center" style="background:#e4f4ea;border-radius:10px;border:1px solid #b7dfc5;">
                                <div style="font-size:.72rem;color:#22633d;font-weight:600;text-transform:uppercase;letter-spacing:.05em;">Timed In</div>
                                <div style="font-size:1.1rem;font-weight:700;color:#22633d;">{{ $timeIn->format('h:i A') }}</div>
                            </div>
                            @if($timeOutWindowOpen)
                                {{-- Time out window is open ── --}}
                                @if($employee->face_descriptor)
                                    <div id="faceTimeoutWrap">
                                        <div style="font-size:.78rem;color:var(--emp-muted);margin-bottom:.4rem;text-align:center;">
                                            <i class="fa fa-camera mr-1"></i> Face verification required to Time Out
                                        </div>
                                        @if($expectedEnd)
                                            <div style="font-size:.75rem;color:var(--emp-muted);text-align:center;margin-bottom:.5rem;">
                                                Scheduled end: {{ $expectedEnd->format('g:i A') }}
                                            </div>
                                        @endif
                                        <div style="position:relative;width:100%;max-width:260px;height:195px;margin:0 auto;border-radius:10px;overflow:hidden;background:#000;border:2px solid var(--emp-border);">
                                            <video id="dashVideo" autoplay playsinline style="width:100%;height:100%;object-fit:cover;"></video>
                                            <canvas id="dashCanvas" style="position:absolute;top:0;left:0;width:100%;height:100%;"></canvas>
                                        </div>
                                        <div id="dashFaceStatus" class="text-center mt-2" style="font-size:.78rem;color:var(--emp-muted);min-height:1.2em;"></div>
                                        <form method="POST" action="{{ route('employee.timeout') }}" id="dashTimeOutForm" style="display:none;">
                                            @csrf
                                        </form>
                                    </div>
                                @else
                                    <form method="POST" action="{{ route('employee.timeout') }}">
                                        @csrf
                                        <button type="submit" class="btn btn-danger btn-block font-weight-bold"
                                                style="border-radius:10px;font-size:.95rem;"
                                                onclick="return confirmAction(this, 'Time Out', 'Record your time out now?')">
                                            <i class="fa fa-sign-out mr-1"></i> Time Out
                                        </button>
                                    </form>
                                @endif
                            @else
                                {{-- Time out window not open: info + early time-out option ── --}}
                                <div class="text-center py-2" style="color:var(--emp-muted);font-size:.85rem;">
                                    <i class="fa fa-clock-o mr-1"></i>
                                    @if($expectedEnd)
                                        Time Out available from {{ $expectedEnd->format('g:i A') }}
                                    @else
                                        Time Out will be available after your scheduled end time.
                                    @endif
                                </div>

                                {{-- Early Time Out option ── --}}
                                <div class="text-center mt-2">
                                    <button type="button" class="btn btn-sm btn-outline-danger"
                                            style="border-radius:999px;font-size:.75rem;"
                                            onclick="document.getElementById('earlyTimeoutWrap').style.display='block';this.style.display='none';">
                                        <i class="fa fa-sign-out mr-1"></i> Early Time Out
                                    </button>
                                </div>
                                <div id="earlyTimeoutWrap" style="display:none;margin-top:.75rem;">
                                    @if($employee->face_descriptor)
                                        <div style="font-size:.75rem;color:#9b2f24;text-align:center;margin-bottom:.4rem;">
                                            <i class="fa fa-camera mr-1"></i> Face verification — Early Time Out
                                        </div>
                                        <div style="position:relative;width:100%;max-width:260px;height:195px;margin:0 auto;border-radius:10px;overflow:hidden;background:#000;border:2px solid #f0b8b0;">
                                            <video id="dashVideo" autoplay playsinline style="width:100%;height:100%;object-fit:cover;"></video>
                                            <canvas id="dashCanvas" style="position:absolute;top:0;left:0;width:100%;height:100%;"></canvas>
                                        </div>
                                        <div id="dashFaceStatus" class="text-center mt-2" style="font-size:.78rem;color:var(--emp-muted);min-height:1.2em;"></div>
                                        <form method="POST" action="{{ route('employee.timeout') }}" id="dashTimeOutForm" style="display:none;">
                                            @csrf
                                        </form>
                                    @else
                                        <form method="POST" action="{{ route('employee.timeout') }}">
                                            @csrf
                                            <button type="submit" class="btn btn-danger btn-block font-weight-bold"
                                                    style="border-radius:10px;font-size:.9rem;"
                                                    onclick="return confirmAction(this, 'Early Time Out', 'Record an early time out now?')">
                                                <i class="fa fa-sign-out mr-1"></i> Confirm Early Time Out
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            @endif

                        @else
                            {{-- ── Both recorded ── --}}
                            <div class="d-flex" style="gap:.5rem;">
                                <div class="flex-fill p-2 text-center" style="background:#e4f4ea;border-radius:10px;border:1px solid #b7dfc5;">
                                    <div style="font-size:.72rem;color:#22633d;font-weight:600;text-transform:uppercase;letter-spacing:.05em;">Time In</div>
                                    <div style="font-size:1rem;font-weight:700;color:#22633d;">{{ $timeIn->format('h:i A') }}</div>
                                </div>
                                <div class="flex-fill p-2 text-center" style="background:#fde6e2;border-radius:10px;border:1px solid #f0b8b0;">
                                    <div style="font-size:.72rem;color:#9b2f24;font-weight:600;text-transform:uppercase;letter-spacing:.05em;">Time Out</div>
                                    <div style="font-size:1rem;font-weight:700;color:#9b2f24;">{{ $timeOut->format('h:i A') }}</div>
                                </div>
                            </div>
                            <div class="text-center mt-2" style="font-size:.8rem;color:var(--emp-muted);">
                                <i class="fa fa-check-circle text-success mr-1"></i> Attendance complete for today
                            </div>
                        @endif
                    </div>

                    <div class="d-flex flex-wrap" style="gap:.5rem;">
                        <a href="{{ route('employee.attendance_logs') }}" class="btn btn-sm btn-outline-secondary" style="border-radius:999px;">Attendance Logs</a>
                        <a href="{{ route('profile') }}" class="btn btn-sm btn-primary" style="border-radius:999px;background:#8B4513;border-color:#8B4513;">My Profile</a>
                    </div>
                </div>
            </div>
        </div>

        {{-- My Schedule ── --}}
        <div class="col-xl-8 mb-4">
            <div class="emp-card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap" style="gap:.5rem;">
                        <h5 class="section-title mb-0">My Schedule</h5>
                        <div class="sched-actions">
                            <a href="{{ route('employee.my_schedule.preview') }}" class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-eye mr-1"></i> Preview
                            </a>
                            <a href="{{ route('employee.my_schedule.pdf') }}" class="btn btn-sm btn-primary" style="background:#8B4513;border-color:#8B4513;">
                                <i class="fas fa-file-pdf mr-1"></i> Export PDF
                            </a>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered sched-table mb-0">
                            <thead>
                                <tr>
                                    <th>Code</th>
                                    <th>Course</th>
                                    <th>Day</th>
                                    <th>Time</th>
                                    <th>Room</th>
                                    <th>Section</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($scheduleRows as $row)
                                    @php $isToday = (int)$row['day_of_week'] === $todayDow; @endphp
                                    <tr class="{{ $isToday ? 'sched-row-today' : '' }}">
                                        <td><strong>{{ $row['course_code'] ?: '—' }}</strong></td>
                                        <td>{{ $row['course_name'] ?: '—' }}</td>
                                        <td>
                                            <span class="day-badge {{ $isToday ? 'today-day' : '' }}">
                                                {{ $dayShort((int)$row['day_of_week']) }}
                                            </span>
                                        </td>
                                        <td>
                                            @foreach($row['times'] as $t)
                                                <div style="white-space:nowrap;">{{ $t['display'] }}</div>
                                            @endforeach
                                        </td>
                                        <td>{{ $row['room'] }}</td>
                                        <td>{{ $row['section_label'] ?: '—' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">No timetable entries published for your account yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Attendance Logs + Requests ── --}}
    <div class="row mb-4">

        {{-- Your Attendance Logs ── --}}
        <div class="col-xl-7 mb-4">
            <div class="emp-card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h5 class="section-title mb-0">Your Attendance Logs</h5>
                        <a href="{{ route('employee.attendance_logs') }}" class="btn btn-sm btn-outline-secondary" style="border-radius:999px;font-size:.78rem;">View All</a>
                    </div>

                    {{-- Mini bar chart ── --}}
                    @php
                        $barData = $recentAttendanceActivity->reverse()->values();
                        $barColors = $barData->map(fn($a) => $a['status'] === 'Present' ? '#8B4513' : ($a['status'] === 'Late' ? '#e8a838' : '#c0392b'));
                    @endphp
                    @if($barData->count())
                    <div class="mb-3">
                        <div class="bar-chart-wrap" id="attendanceBarChart">
                            @foreach($barData as $i => $bar)
                                @php
                                    $color = $bar['status'] === 'Present' ? '#8B4513' : ($bar['status'] === 'Late' ? '#e8a838' : '#c0392b');
                                    $pct   = $bar['status'] === 'Absent' ? 30 : ($bar['status'] === 'Late' ? 65 : 100);
                                @endphp
                                <div class="bar-col"
                                     style="height:{{ $pct }}%;background:{{ $color }};"
                                     title="{{ $bar['date']->format('M d') }}: {{ $bar['status'] }}">
                                </div>
                            @endforeach
                        </div>
                        <div class="d-flex justify-content-between mt-1" style="font-size:.7rem;color:var(--emp-muted);">
                            <span>{{ $barData->first()['date']->format('M d') }}</span>
                            <span>{{ $barData->last()['date']->format('M d') }}</span>
                        </div>
                        <div class="d-flex gap-3 mt-2" style="gap:.75rem;font-size:.75rem;color:var(--emp-muted);">
                            <span><span style="display:inline-block;width:10px;height:10px;border-radius:2px;background:#8B4513;margin-right:3px;"></span>Present</span>
                            <span><span style="display:inline-block;width:10px;height:10px;border-radius:2px;background:#e8a838;margin-right:3px;"></span>Late</span>
                            <span><span style="display:inline-block;width:10px;height:10px;border-radius:2px;background:#c0392b;margin-right:3px;"></span>Absent</span>
                        </div>
                    </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-bordered log-table mb-0">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Time In</th>
                                    <th>Time Out</th>
                                    <th>Hours</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentAttendanceActivity as $activity)
                                    @php
                                        $ac = $activity['status'] === 'Present' ? 'badge-present' : ($activity['status'] === 'Late' ? 'badge-late' : 'badge-absent');
                                        $hrs = !is_null($activity['worked_seconds']) ? gmdate('H:i', (int)$activity['worked_seconds']) : '—';
                                    @endphp
                                    <tr>
                                        <td>{{ $activity['date']->format('M d, Y') }}</td>
                                        <td><span class="badge-soft {{ $ac }}">{{ $activity['status'] }}</span></td>
                                        <td>{{ $activity['actual_in'] ? $activity['actual_in']->format('h:i A') : '—' }}</td>
                                        <td>{{ $activity['actual_out'] ? $activity['actual_out']->format('h:i A') : '—' }}</td>
                                        <td>{{ $hrs }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="text-center text-muted py-3">No attendance records found.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Recent Requests ── --}}
        <div class="col-xl-5 mb-4">
            <div class="emp-card h-100">
                <div class="card-body">
                    <h5 class="section-title">Recent Requests</h5>
                    <div class="table-responsive">
                        <table class="table table-bordered req-table mb-0">
                            <thead>
                                <tr>
                                    <th>Request</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentRequests as $req)
                                    @php
                                        $rc = $req['status'] === 1 ? 'badge-approved' : ($req['status'] === 2 ? 'badge-rejected' : 'badge-pending');
                                        $rl = $req['status'] === 1 ? 'Approved' : ($req['status'] === 2 ? 'Rejected' : 'Pending');
                                    @endphp
                                    <tr>
                                        <td>{{ $req['label'] }}</td>
                                        <td style="white-space:nowrap;">{{ optional($req['submitted_at'])->format('M d, Y') }}</td>
                                        <td><span class="badge-soft {{ $rc }}">{{ $rl }}</span></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" class="text-center text-muted py-3">No requests found.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection

@section('script-bottom')
<script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>
<script>
(function () {
    /* ── Confirmation helper (fallback for no-face employees) ── */
    window.confirmAction = function (btn, title, message) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: title, text: message, icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#8B4513', cancelButtonColor: '#aaa',
                confirmButtonText: 'Yes, ' + title, cancelButtonText: 'Cancel',
            }).then(function (result) { if (result.isConfirmed) btn.closest('form').submit(); });
            return false;
        }
        return confirm(message);
    };

    /* ── Live Philippine time clock ── */
    function updatePHClock() {
        var now = new Date();
        var ph  = new Date(now.toLocaleString('en-US', { timeZone: 'Asia/Manila' }));
        var h = ph.getHours(), m = ph.getMinutes(), s = ph.getSeconds();
        var ampm = h >= 12 ? 'PM' : 'AM';
        h = h % 12 || 12;
        var timeStr = (h<10?'0'+h:h)+':'+(m<10?'0'+m:m)+':'+(s<10?'0'+s:s)+' '+ampm;
        var days   = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
        var months = ['January','February','March','April','May','June','July','August','September','October','November','December'];
        var dateStr = days[ph.getDay()]+', '+months[ph.getMonth()]+' '+ph.getDate()+', '+ph.getFullYear();
        var timeEl = document.getElementById('heroPHTime');
        var dateEl = document.getElementById('heroPHDate');
        if (timeEl) timeEl.textContent = timeStr;
        if (dateEl) dateEl.textContent = dateStr;
    }
    updatePHClock();
    setInterval(updatePHClock, 1000);

    /* ── Donut ring helper ── */
    function drawRing(canvasId, value, max) {
        var canvas = document.getElementById(canvasId);
        if (!canvas) return;
        var ctx = canvas.getContext('2d');
        var cx = 32, cy = 32, r = 26, lw = 6;
        var pct = max > 0 ? Math.min(value / max, 1) : 0;
        ctx.clearRect(0, 0, 64, 64);
        ctx.beginPath(); ctx.arc(cx, cy, r, 0, Math.PI*2);
        ctx.strokeStyle = 'rgba(255,255,255,.2)'; ctx.lineWidth = lw; ctx.stroke();
        if (pct > 0) {
            ctx.beginPath();
            ctx.arc(cx, cy, r, -Math.PI/2, -Math.PI/2 + Math.PI*2*pct);
            ctx.strokeStyle = 'rgba(255,255,255,.9)'; ctx.lineWidth = lw;
            ctx.lineCap = 'round'; ctx.stroke();
        }
    }
    var workingDays = {{ now()->day }};
    drawRing('ringPresent', {{ $presentDays }}, workingDays);
    drawRing('ringLate',    {{ $lateCount }},   workingDays);
    drawRing('ringAbsent',  {{ $absenceCount }}, workingDays);
})();
</script>

@php
    $hasFace = !empty($employee->face_descriptor);
    $needsTimein  = !$timeIn  && ($timeInWindowOpen  || true); // always init if face present; window controls visibility
    $needsTimeout = $timeIn && !$timeOut && ($timeOutWindowOpen || true);
    // Show the face widget JS whenever the camera elements exist on the page
    $showFaceWidget = $hasFace && (!$timeIn || (!$timeOut));
@endphp

@if($showFaceWidget)
<script>
(function () {
    var video      = document.getElementById('dashVideo');
    var canvas     = document.getElementById('dashCanvas');
    var statusEl   = document.getElementById('dashFaceStatus');
    var actionForm = document.getElementById('{{ (!$timeIn) ? 'dashTimeInForm' : 'dashTimeOutForm' }}');
    var actionLabel = '{{ (!$timeIn) ? 'Time In' : 'Time Out' }}';

    if (!video || !canvas || !actionForm) return;

    // The logged-in employee's own face descriptor (already verified by auth)
    var ownDescriptorRaw = @json($employee->face_descriptor);
    var MATCH_THRESHOLD  = 0.45; // slightly more lenient than kiosk (same person, different lighting)
    var SCORE_THRESHOLD  = 0.75;
    var modelsLoaded     = false;
    var faceMatcher      = null;
    var isSubmitting     = false;
    var scanInterval     = null;

    function setStatus(msg, type) {
        if (!statusEl) return;
        var colors = { processing: '#7b5a45', success: '#22633d', error: '#9b2f24' };
        statusEl.textContent = msg;
        statusEl.style.color = colors[type] || '#7b5a45';
    }

    // Load face-api models then build a matcher for just this employee
    Promise.all([
        faceapi.nets.tinyFaceDetector.loadFromUri('https://cdn.jsdelivr.net/npm/@vladmandic/face-api/model/'),
        faceapi.nets.faceLandmark68Net.loadFromUri('https://cdn.jsdelivr.net/npm/@vladmandic/face-api/model/'),
        faceapi.nets.faceRecognitionNet.loadFromUri('https://cdn.jsdelivr.net/npm/@vladmandic/face-api/model/')
    ]).then(function () {
        modelsLoaded = true;
        try {
            var descriptor = new Float32Array(JSON.parse(ownDescriptorRaw));
            var labeled = new faceapi.LabeledFaceDescriptors('self', [descriptor]);
            faceMatcher = new faceapi.FaceMatcher([labeled], MATCH_THRESHOLD);
        } catch (e) {
            setStatus('Could not load your face data. Please re-register your face in your profile.', 'error');
            return;
        }
        startCamera();
    }).catch(function () {
        setStatus('Could not load face recognition models. Check your connection.', 'error');
    });

    function startCamera() {
        navigator.mediaDevices.getUserMedia({ video: { facingMode: 'user' } })
            .then(function (stream) {
                video.srcObject = stream;
                video.play();
                setStatus('Camera ready — look at the camera to ' + actionLabel + '.', 'processing');

                video.addEventListener('play', function () {
                    var size = { width: 260, height: 195 };
                    faceapi.matchDimensions(canvas, size);
                    // Draw landmarks overlay
                    setInterval(async function () {
                        var dets = await faceapi.detectAllFaces(video, new faceapi.TinyFaceDetectorOptions())
                            .withFaceLandmarks().withFaceDescriptors();
                        var resized = faceapi.resizeResults(dets, size);
                        canvas.getContext('2d').clearRect(0, 0, canvas.width, canvas.height);
                        faceapi.draw.drawDetections(canvas, resized);
                        faceapi.draw.drawFaceLandmarks(canvas, resized);
                    }, 100);
                });

                scanInterval = setInterval(scanFace, 1200);
            })
            .catch(function () {
                setStatus('Camera access denied. Please allow camera permissions.', 'error');
            });
    }

    async function scanFace() {
        if (isSubmitting || !modelsLoaded || !faceMatcher) return;

        var detection = await faceapi.detectSingleFace(video, new faceapi.TinyFaceDetectorOptions())
            .withFaceLandmarks().withFaceDescriptor();

        if (!detection || detection.detection.score < SCORE_THRESHOLD) {
            setStatus('Position your face in the frame…', 'processing');
            return;
        }

        var match = faceMatcher.findBestMatch(detection.descriptor);

        if (match.label === 'self') {
            setStatus('✓ Face verified! Recording ' + actionLabel + '…', 'success');
            isSubmitting = true;
            clearInterval(scanInterval);
            // Stop camera
            if (video.srcObject) {
                video.srcObject.getTracks().forEach(function (t) { t.stop(); });
            }
            setTimeout(function () { actionForm.submit(); }, 1200);
        } else {
            setStatus('Face not recognized. Please look directly at the camera.', 'error');
        }
    }
})();
</script>
@endif
@endsection
