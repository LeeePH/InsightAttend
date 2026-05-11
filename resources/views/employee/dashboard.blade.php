@extends('layouts.master')

@section('css')
<style>
    :root {
        --employee-bg: #f8f1eb;
        --employee-card: #ffffff;
        --employee-text: #3e2412;
        --employee-muted: #7b5a45;
        --employee-border: #e2cdbd;
        --employee-accent: #8B4513;
        --employee-accent-dark: #6f330d;
        --employee-accent-soft: #f4e4d8;
    }

    body {
        background: var(--employee-bg);
    }

    .employee-dashboard .card {
        border: 1px solid var(--employee-border);
        box-shadow: 0 10px 26px rgba(62, 36, 18, 0.08);
    }

    .dashboard-hero {
        border-radius: 18px;
        padding: 1.75rem;
        background: linear-gradient(135deg, #4c2d17 0%, #8B4513 100%);
        color: #fff;
    }

    .dashboard-hero p {
        margin-bottom: 0;
        color: rgba(255, 245, 236, 0.82);
    }

    .metric-card {
        border-radius: 16px;
        color: #fff;
        min-height: 100%;
    }

    .metric-card.metric-present {
        background: linear-gradient(145deg, #8B4513 0%, #b66a33 100%);
    }

    .metric-card.metric-late {
        background: linear-gradient(145deg, #354b68 0%, #223245 100%);
    }

    .metric-card.metric-absence {
        background: linear-gradient(145deg, #6b342f 0%, #47201d 100%);
    }

    .metric-label {
        font-size: 0.82rem;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: rgba(255, 245, 236, 0.78);
    }

    .metric-value {
        font-size: 2rem;
        font-weight: 700;
        margin: 0.4rem 0 0;
    }

    .mini-status {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 0.55rem 0.85rem;
        border-radius: 999px;
        background: var(--employee-accent-soft);
        color: var(--employee-text);
        font-weight: 600;
    }

    .section-title {
        color: var(--employee-text);
        font-weight: 700;
        margin-bottom: 1rem;
    }

    .employee-dashboard .table thead th {
        background: #f5e8de;
        color: var(--employee-text);
        border-color: var(--employee-border);
        font-weight: 700;
    }

    .employee-dashboard .table td {
        border-color: var(--employee-border);
        color: var(--employee-text);
        vertical-align: middle;
    }

    .badge-soft {
        display: inline-block;
        padding: 0.4rem 0.75rem;
        border-radius: 999px;
        font-weight: 600;
        font-size: 0.78rem;
    }

    .badge-soft.badge-present,
    .badge-soft.badge-approved {
        background: #e4f4ea;
        color: #22633d;
    }

    .badge-soft.badge-late,
    .badge-soft.badge-pending {
        background: #fff2d9;
        color: #8c5a07;
    }

    .badge-soft.badge-absent,
    .badge-soft.badge-rejected {
        background: #fde6e2;
        color: #9b2f24;
    }

    .dashboard-note {
        color: var(--employee-muted);
        font-size: 0.88rem;
    }
</style>
@endsection

@section('breadcrumb')
<div class="col-sm-6 text-left">
    <h4 class="page-title">Employee Dashboard</h4>
</div>
@endsection

@section('content')
@include('includes.flash')

<div class="employee-dashboard">
    <div class="row">
        <div class="col-12">
            <div class="dashboard-hero mb-4">
                <h3 class="mb-2">Welcome back, {{ $employee->name }}</h3>
                <p>Your attendance overview, recent activity, and latest requests are all in one place.</p>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card metric-card metric-present">
                <div class="card-body">
                    <div class="metric-label">Present Days This Month</div>
                    <div class="metric-value">{{ $presentDays }}</div>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card metric-card metric-late">
                <div class="card-body">
                    <div class="metric-label">Late Records This Month</div>
                    <div class="metric-value">{{ $lateCount }}</div>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-md-12 mb-4">
            <div class="card metric-card metric-absence">
                <div class="card-body">
                    <div class="metric-label">Absences This Month</div>
                    <div class="metric-value">{{ $absenceCount }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-5 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="section-title">Today's Attendance</h5>
                    <div class="mb-3">
                        @php
                            $statusClass = $statusLabel === 'Present' ? 'badge-present' : ($statusLabel === 'Late' ? 'badge-late' : 'badge-absent');
                        @endphp
                        <span class="mini-status">
                            <span class="badge-soft {{ $statusClass }}">{{ $statusLabel }}</span>
                            <span>{{ now()->format('F d, Y') }}</span>
                        </span>
                    </div>
                    <div class="row">
                        <div class="col-sm-6 mb-3">
                            <div class="dashboard-note">Time In</div>
                            <div class="font-weight-bold">{{ $timeIn ? $timeIn->format('h:i A') : 'No record' }}</div>
                        </div>
                        <div class="col-sm-6 mb-3">
                            <div class="dashboard-note">Time Out</div>
                            <div class="font-weight-bold">{{ $timeOut ? $timeOut->format('h:i A') : 'No record' }}</div>
                        </div>
                        <div class="col-sm-6 mb-3">
                            <div class="dashboard-note">Expected Shift</div>
                            <div class="font-weight-bold">
                                @if ($expectedStart && $expectedEnd)
                                    {{ $expectedStart->format('h:i A') }} - {{ $expectedEnd->format('h:i A') }}
                                @else
                                    Not set
                                @endif
                            </div>
                        </div>
                        <div class="col-sm-6 mb-3">
                            <div class="dashboard-note">Worked Hours</div>
                            <div class="font-weight-bold">
                                {{ !is_null($workedSeconds) ? gmdate('H:i', (int) $workedSeconds) : 'No record' }}
                            </div>
                        </div>
                    </div>
                    <div class="mt-2">
                        <a href="{{ route('employee.attendance_logs') }}" class="btn btn-outline-secondary btn-sm">View Attendance Logs</a>
                        <a href="{{ route('profile') }}" class="btn btn-primary btn-sm">My Profile</a>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-7 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="section-title">Recent Attendance Activity</h5>
                    <div class="table-responsive">
                        <table class="table table-bordered mb-0">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Time In</th>
                                    <th>Time Out</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($recentAttendanceActivity as $activity)
                                    @php
                                        $activityClass = $activity['status'] === 'Present' ? 'badge-present' : ($activity['status'] === 'Late' ? 'badge-late' : 'badge-absent');
                                    @endphp
                                    <tr>
                                        <td>{{ $activity['date']->format('M d, Y') }}</td>
                                        <td><span class="badge-soft {{ $activityClass }}">{{ $activity['status'] }}</span></td>
                                        <td>{{ $activity['actual_in'] ? $activity['actual_in']->format('h:i A') : 'No record' }}</td>
                                        <td>{{ $activity['actual_out'] ? $activity['actual_out']->format('h:i A') : 'No record' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center">No attendance activity found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h5 class="section-title">Recent Requests</h5>
                    <div class="table-responsive">
                        <table class="table table-bordered mb-0">
                            <thead>
                                <tr>
                                    <th>Request</th>
                                    <th>Date Submitted</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($recentRequests as $requestItem)
                                    @php
                                        $requestClass = $requestItem['status'] === 1 ? 'badge-approved' : ($requestItem['status'] === 2 ? 'badge-rejected' : 'badge-pending');
                                        $requestLabel = $requestItem['status'] === 1 ? 'Approved' : ($requestItem['status'] === 2 ? 'Rejected' : 'Pending');
                                    @endphp
                                    <tr>
                                        <td>{{ $requestItem['label'] }}</td>
                                        <td>{{ optional($requestItem['submitted_at'])->format('M d, Y h:i A') }}</td>
                                        <td><span class="badge-soft {{ $requestClass }}">{{ $requestLabel }}</span></td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center">No requests found.</td>
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
@endsection

@section('script')
@endsection
