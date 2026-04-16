@extends('layouts.master')

@php
use Illuminate\Support\Str;
@endphp

@section('css')
<style>
    :root {
        --theme-bg: #f2f5fa;
        --theme-card: #ffffff;
        --theme-text: #1f2a3d;
        --theme-muted: #6b7587;
        --theme-border: #dde3ed;
        --theme-accent: #2e3f5c;
        --theme-accent-soft: #e8edf6;
        --theme-chip: #eef2f8;
    }

    body {
        background-color: var(--theme-bg);
    }

    .dashboard-shell .card {
        border: 1px solid var(--theme-border);
        box-shadow: 0 8px 20px rgba(19, 28, 43, 0.06);
    }

    .dashboard-shell .card-title,
    .dashboard-shell .header-title,
    .dashboard-shell .page-title {
        color: var(--theme-text);
    }

    .summary-card {
        background: linear-gradient(160deg, #2f3f5b 0%, #233148 100%);
        color: #fff;
        border-radius: 12px;
        border: 1px solid rgba(255, 255, 255, 0.08);
    }

    .summary-card .summary-label {
        font-size: 13px;
        letter-spacing: 0.03em;
        text-transform: uppercase;
        opacity: 0.82;
        margin-bottom: 4px;
    }

    .summary-card .summary-value {
        margin: 0;
        font-weight: 700;
        font-size: 26px;
    }

    .dashboard-shell .table {
        color: var(--theme-text);
    }

    .dashboard-shell .table thead th {
        background: var(--theme-accent-soft);
        color: var(--theme-text);
        border-color: var(--theme-border);
        font-weight: 600;
    }

    .dashboard-shell .table td {
        border-color: var(--theme-border);
    }

    .theme-btn {
        display: block;
        border: 1px solid #c5cede;
        background: #f4f7fc;
        color: var(--theme-text);
        border-radius: 10px;
        padding: 10px 14px;
        font-weight: 600;
        text-align: left;
        transition: all 0.2s ease;
    }

    .theme-btn:hover {
        background: #e8edf6;
        color: var(--theme-text);
        text-decoration: none;
    }

    .theme-badge {
        background: var(--theme-chip);
        color: var(--theme-text);
        border: 1px solid #cfd7e6;
        font-weight: 600;
        padding: 5px 10px;
        border-radius: 999px;
        display: inline-block;
    }

    .schedule-item {
        margin-bottom: 8px;
        color: var(--theme-muted);
    }

    .schedule-item strong {
        color: var(--theme-text);
    }

    .profile-card {
        border: 1px solid var(--theme-border);
        border-radius: 12px;
        background: var(--theme-card);
    }
    .profile-card .profile-title {
        font-size: 0.95rem;
        font-weight: 700;
        margin-bottom: 0.75rem;
        color: var(--theme-text);
    }
    .profile-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px 18px;
    }
    @media (max-width: 575.98px) {
        .profile-grid {
            grid-template-columns: 1fr;
        }
    }
    .profile-field .label {
        font-size: 0.72rem;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: var(--theme-muted);
        margin-bottom: 2px;
    }
    .profile-field .value {
        font-weight: 600;
        color: var(--theme-text);
    }
</style>
@endsection

@section('breadcrumb')
<div class="col-sm-6 text-left">
    <h4 class="page-title">Employee dashboard</h4>
    <ol class="breadcrumb">
        <li class="breadcrumb-item active">Dashboard</li>
    </ol>
</div>
@endsection

@section('content')
@include('includes.flash')

<div class="row">
    <div class="col-xl-12">
        <div class="card dashboard-shell">
            <div class="card-body">
                <h4 class="mt-0 header-title mb-4">Welcome, {{ $employee->name ?? 'Employee' }}!</h4>
                
                <div class="row mt-2">
                    <div class="col-lg-6 mb-3">
                        <div class="card profile-card">
                            <div class="card-body">
                                <div class="profile-title">My profile</div>
                                <div class="profile-grid">
                                    <div class="profile-field">
                                        <div class="label">Department</div>
                                        <div class="value">{{ $employee->department ?? '—' }}</div>
                                    </div>
                                    <div class="profile-field">
                                        <div class="label">Position</div>
                                        <div class="value">{{ $employee->position ?? '—' }}</div>
                                    </div>
                                    <div class="profile-field">
                                        <div class="label">Schedule</div>
                                        <div class="value">
                                            @if ($sched)
                                                {{ \Carbon\Carbon::parse($sched->time_in)->format('h:i A') }}
                                                – {{ \Carbon\Carbon::parse($sched->time_out)->format('h:i A') }}
                                            @else
                                                —
                                            @endif
                                        </div>
                                    </div>
                                    <div class="profile-field">
                                        <div class="label">Subject</div>
                                        <div class="value">{{ $sched?->slug ?? '—' }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6 mb-3">
                        <div class="card profile-card">
                            <div class="card-body">
                                <div class="profile-title">Today's attendance</div>
                                <p class="mb-2">
                                    <strong>Status:</strong>
                                    @if ($statusLabel === 'Present')
                                        <span class="badge badge-success">Present</span>
                                    @elseif ($statusLabel === 'Late')
                                        <span class="badge badge-warning">Late</span>
                                    @else
                                        <span class="badge badge-secondary">Absent</span>
                                    @endif
                                </p>
                                <div class="row">
                                    <div class="col-sm-4">
                                        <div class="text-muted small">Time In</div>
                                        <div class="font-weight-600">{{ $timeIn ? $timeIn->format('h:i A') : '—' }}</div>
                                    </div>
                                    <div class="col-sm-4">
                                        <div class="text-muted small">Time Out</div>
                                        <div class="font-weight-600">{{ $timeOut ? $timeOut->format('h:i A') : '—' }}</div>
                                    </div>
                                    <div class="col-sm-4">
                                        <div class="text-muted small">Hours worked</div>
                                        <div class="font-weight-600">
                                            @if (!is_null($workedSeconds))
                                                {{ gmdate('H:i', (int) $workedSeconds) }}
                                            @else
                                                —
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mt-2">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Quick actions</h5>
                                <div class="d-grid gap-2">
                                    <a href="{{ route('timein.index') }}" class="theme-btn mb-2">
                                        <i class="fa fa-clock-o"></i> Time In
                                    </a>
                                    <a href="{{ route('timeout.index') }}" class="theme-btn mb-2">
                                        <i class="fa fa-clock-o"></i> Time Out
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Recent Attendance</h5>
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th>Time</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($attendances as $attendance)
                                            <tr>
                                                <td>{{ $attendance->attendance_date }}</td>
                                                <td>
                                                    @if($attendance->attendance_time)
                                                        {{ \Carbon\Carbon::parse($attendance->attendance_time)->format('h:i A') }}
                                                    @else
                                                        -
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($attendance->type == 0)
                                                        <span class="theme-badge">Time In</span>
                                                    @else
                                                        <span class="theme-badge">Time Out</span>
                                                    @endif
                                                </td>
                                            </tr>
                                            @empty
                                            <tr>
                                                <td colspan="3" class="text-center">No attendance records found</td>
                                            </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">My Leave Requests</h5>
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th>Type</th>
                                                <th>Days</th>
                                                <th>Reason</th>
                                                <th>Files</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($leaves as $leave)
                                            <tr>
                                                <td>{{ \Carbon\Carbon::parse($leave->leave_date)->format('M d, Y') }}</td>
                                                <td>
                                                    @switch($leave->type)
                                                        @case(1) Sick Leave @break
                                                        @case(2) Annual Leave @break
                                                        @case(3) Personal Leave @break
                                                        @case(4) Maternity Leave @break
                                                        @case(5) Paternity Leave @break
                                                        @case(6) Vacation Leave @break
                                                        @case(7) Emergency Leave @break
                                                        @default Other @break
                                                    @endswitch
                                                </td>
                                                <td>{{ $leave->leave_days ?? 1 }}</td>
                                                <td>{{ Str::limit($leave->reason, 30) }}</td>
                                                <td class="text-nowrap small">
                                                    @php $urls = $leave->supportingDocumentUrls(); @endphp
                                                    @if(count($urls))
                                                        @foreach($urls as $i => $u)
                                                            <a href="{{ $u }}" target="_blank" rel="noopener">{{ $i + 1 }}</a>@if(!$loop->last) · @endif
                                                        @endforeach
                                                    @else
                                                        —
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($leave->status == 0)
                                                        <span class="theme-badge">Pending</span>
                                                    @elseif($leave->status == 1)
                                                        <span class="theme-badge">Approved</span>
                                                    @else
                                                        <span class="theme-badge">Rejected</span>
                                                    @endif
                                                </td>
                                            </tr>
                                            @empty
                                            <tr>
                                                <td colspan="6" class="text-center">No leave requests found</td>
                                            </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
@endsection
