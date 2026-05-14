@extends('layouts.master')

@section('css')
<style>
    .dept-hero {
        background: linear-gradient(135deg, #4c2d17 0%, #8B4513 100%);
        border-radius: 14px;
        padding: 1.4rem 1.6rem;
        color: #fff;
        margin-bottom: 1.5rem;
    }
    .dept-hero h4 { color: #fff; margin: 0; font-weight: 700; }
    .dept-hero .sub { color: rgba(255,245,236,.78); font-size: .88rem; margin-top: .2rem; }

    .stat-pill {
        display: inline-flex;
        align-items: center;
        gap: .4rem;
        padding: .45rem .9rem;
        border-radius: 999px;
        font-weight: 700;
        font-size: .82rem;
    }
    .stat-pill.present { background: #e4f4ea; color: #22633d; }
    .stat-pill.late    { background: #fff2d9; color: #8c5a07; }
    .stat-pill.absent  { background: #fde6e2; color: #9b2f24; }
    .stat-pill.off     { background: #f0f0f0; color: #555; }

    .badge-soft {
        display: inline-block;
        padding: .3rem .75rem;
        border-radius: 999px;
        font-weight: 600;
        font-size: .78rem;
    }
    .badge-soft.present { background: #e4f4ea; color: #22633d; }
    .badge-soft.late    { background: #fff2d9; color: #8c5a07; }
    .badge-soft.absent  { background: #fde6e2; color: #9b2f24; }
    .badge-soft.off     { background: #f0f0f0; color: #555; }

    .emp-table thead th {
        background: #f5e8de;
        color: #3e2412;
        border-color: #e2cdbd !important;
        font-weight: 700;
        font-size: .83rem;
    }
    .emp-table td {
        border-color: #e2cdbd !important;
        vertical-align: middle;
        font-size: .88rem;
    }
    .emp-table tr.row-present { border-left: 3px solid #22633d; }
    .emp-table tr.row-late    { border-left: 3px solid #e8a838; }
    .emp-table tr.row-absent  { border-left: 3px solid #c0392b; }
    .emp-table tr.row-off     { border-left: 3px solid #aaa; }

    .filter-bar { background: #fff; border: 1px solid #e2cdbd; border-radius: 12px; padding: 1rem 1.2rem; margin-bottom: 1.25rem; }
    .filter-bar .form-control { border-radius: 8px; }

    .summary-bar { display: flex; gap: .6rem; flex-wrap: wrap; margin-bottom: 1.25rem; }

    .no-emp { text-align: center; padding: 3rem 1rem; color: #7b5a45; }
    .no-emp i { font-size: 2.5rem; display: block; margin-bottom: .75rem; opacity: .4; }
</style>
@endsection

@section('breadcrumb')
<div class="col-sm-6">
    <h4 class="page-title text-left">{{ $department->name }}</h4>
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('admin') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('departments.report') }}">Department Management</a></li>
        <li class="breadcrumb-item active">Employee Attendance</li>
    </ol>
</div>
@endsection

@section('content')
@include('includes.flash')

{{-- Hero --}}
<div class="dept-hero">
    <div class="d-flex align-items-center justify-content-between flex-wrap" style="gap:.75rem;">
        <div>
            <h4>{{ $department->name }}</h4>
            <div class="sub">Employee attendance for {{ $date->format('l, F d, Y') }}</div>
        </div>
        <a href="{{ route('departments.report') }}" class="btn btn-sm" style="background:rgba(255,255,255,.18);color:#fff;border-radius:999px;border:1px solid rgba(255,255,255,.3);">
            <i class="fa fa-arrow-left mr-1"></i> Back to Report
        </a>
    </div>
</div>

{{-- Date filter --}}
<div class="filter-bar">
    <form method="GET" action="{{ route('departments.employees', $department) }}" class="d-flex align-items-end flex-wrap" style="gap:.75rem;">
        <div>
            <label class="mb-1 d-block" style="font-size:.82rem;font-weight:600;color:#3e2412;">Date</label>
            <input type="date" name="date" class="form-control form-control-sm" value="{{ $date->toDateString() }}" style="min-width:160px;">
        </div>
        <div>
            <button type="submit" class="btn btn-sm btn-primary" style="border-radius:999px;background:#8B4513;border-color:#8B4513;">
                <i class="fa fa-filter mr-1"></i> Apply
            </button>
            <a href="{{ route('departments.employees', $department) }}" class="btn btn-sm btn-outline-secondary ml-1" style="border-radius:999px;">Today</a>
        </div>
    </form>
</div>

{{-- Summary pills --}}
<div class="summary-bar">
    <span class="stat-pill present"><i class="fa fa-check-circle"></i> Present: {{ $summary['present'] }}</span>
    <span class="stat-pill late"><i class="fa fa-clock-o"></i> Late: {{ $summary['late'] }}</span>
    <span class="stat-pill absent"><i class="fa fa-times-circle"></i> Absent: {{ $summary['absent'] }}</span>
    <span class="stat-pill off"><i class="fa fa-moon-o"></i> Off: {{ $summary['off'] }}</span>
    <span class="stat-pill" style="background:#f4e4d8;color:#5c2d0d;">
        <i class="fa fa-users"></i> Total: {{ $employeeRows->count() }}
    </span>
</div>

{{-- Employee table --}}
<div class="card" style="border:1px solid #e2cdbd;border-radius:14px;">
    <div class="card-body p-0">
        @if($employeeRows->isEmpty())
            <div class="no-emp">
                <i class="fa fa-users"></i>
                No employees are assigned to <strong>{{ $department->name }}</strong> yet.
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-bordered emp-table mb-0">
                    <thead>
                        <tr>
                            <th style="width:40px;">#</th>
                            <th>Employee</th>
                            <th>Position</th>
                            <th style="width:110px;">Status</th>
                            <th style="width:110px;">Time In</th>
                            <th style="width:110px;">Time Out</th>
                            <th style="width:110px;">Worked</th>
                            <th style="width:160px;">Expected Shift</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($employeeRows as $i => $row)
                            @php
                                $rowClass = match($row['status']) {
                                    'Present' => 'row-present',
                                    'Late'    => 'row-late',
                                    'Absent'  => 'row-absent',
                                    default   => 'row-off',
                                };
                                $badgeClass = strtolower($row['status']);
                                $worked = !is_null($row['worked_seconds'])
                                    ? gmdate('H:i', (int) $row['worked_seconds'])
                                    : '—';
                            @endphp
                            <tr class="{{ $rowClass }}">
                                <td class="text-muted">{{ $i + 1 }}</td>
                                <td>
                                    <strong>{{ $row['name'] }}</strong>
                                </td>
                                <td class="text-muted">{{ $row['position'] ?: '—' }}</td>
                                <td>
                                    <span class="badge-soft {{ $badgeClass }}">{{ $row['status'] }}</span>
                                </td>
                                <td>{{ $row['actual_in'] ? $row['actual_in']->format('h:i A') : '—' }}</td>
                                <td>{{ $row['actual_out'] ? $row['actual_out']->format('h:i A') : '—' }}</td>
                                <td>{{ $worked }}</td>
                                <td>
                                    @if($row['expected_start'] && $row['expected_end'])
                                        {{ $row['expected_start']->format('h:i A') }} – {{ $row['expected_end']->format('h:i A') }}
                                    @else
                                        <span class="text-muted">Not set</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection
