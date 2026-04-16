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
                
                <div class="row">
                    <div class="col-md-4">
                        <div class="card summary-card">
                            <div class="card-body">
                                <p class="summary-label">Employee ID</p>
                                <h3 class="summary-value">{{ $employee->id }}</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card summary-card">
                            <div class="card-body">
                                <p class="summary-label">Department</p>
                                <h3 class="summary-value">{{ $employee->department ?? 'N/A' }}</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card summary-card">
                            <div class="card-body">
                                <p class="summary-label">Position</p>
                                <h3 class="summary-value">{{ $employee->position ?? 'N/A' }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row mt-4">
                    <div class="col-lg-5 mb-4 mb-lg-0">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Change your password</h5>
                                <p class="text-muted small">Use at least 8 characters. You will stay signed in after updating.</p>
                                <form method="POST" action="{{ route('employee.password.update') }}" autocomplete="off">
                                    @csrf
                                    <div class="form-group">
                                        <label for="current_password">Current password</label>
                                        <input type="password" name="current_password" id="current_password" class="form-control @error('current_password') is-invalid @enderror" required autocomplete="current-password">
                                        @error('current_password')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="form-group">
                                        <label for="password">New password</label>
                                        <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror" required minlength="8" autocomplete="new-password">
                                        @error('password')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="form-group">
                                        <label for="password_confirmation">Confirm new password</label>
                                        <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" required minlength="8" autocomplete="new-password">
                                    </div>
                                    <button type="submit" class="btn btn-primary btn-sm">Update password</button>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-7">
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
                                    <a href="{{ route('leave.request') }}" class="theme-btn mb-2">
                                        <i class="fa fa-calendar"></i> Request Leave
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mt-2">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">My schedule</h5>
                                @php
                                    $sched = $employee ? $employee->schedules()->first() : null;
                                @endphp
                                @if ($sched)
                                    <p class="schedule-item"><strong>Shift:</strong> {{ $sched->slug }}</p>
                                    <p class="schedule-item"><strong>Time in:</strong> {{ \Carbon\Carbon::parse($sched->time_in)->format('h:i A') }}</p>
                                    <p class="schedule-item"><strong>Time out:</strong> {{ \Carbon\Carbon::parse($sched->time_out)->format('h:i A') }}</p>
                                @else
                                    <p class="text-muted">No schedule assigned</p>
                                @endif
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
