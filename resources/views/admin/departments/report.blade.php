@extends('layouts.master')

@section('breadcrumb')
    <div class="col-sm-6">
        <h4 class="page-title text-left">Department Attendance Report</h4>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin') }}">Home</a></li>
            <li class="breadcrumb-item active">Department Report</li>
        </ol>
    </div>
@endsection

@section('content')
@include('includes.flash')

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <form method="GET" action="{{ route('departments.report') }}" class="mb-3">
                    <div class="row">
                        <div class="col-md-4">
                            <label class="mb-1">Department</label>
                            <select class="form-control" name="department_id">
                                <option value="">All Departments</option>
                                @foreach($departments as $d)
                                    <option value="{{ $d->id }}" {{ (string) $departmentId === (string) $d->id ? 'selected="selected"' : '' }}>
                                        {{ $d->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="mb-1">Range</label>
                            <select class="form-control" name="range" id="deptReportRange">
                                <option value="daily" {{ $range === 'daily' ? 'selected="selected"' : '' }}>Daily</option>
                                <option value="weekly" {{ $range === 'weekly' ? 'selected="selected"' : '' }}>Weekly</option>
                                <option value="monthly" {{ $range === 'monthly' ? 'selected="selected"' : '' }}>Monthly</option>
                                <option value="custom" {{ $range === 'custom' ? 'selected="selected"' : '' }}>Custom</option>
                            </select>
                        </div>
                        <div class="col-md-2" data-range="daily" style="{{ $range === 'daily' ? '' : 'display:none;' }}">
                            <label class="mb-1">Date</label>
                            <input type="date" class="form-control" name="date" value="{{ $start->toDateString() }}">
                        </div>
                        <div class="col-md-3" data-range="custom" style="{{ $range === 'custom' ? '' : 'display:none;' }}">
                            <label class="mb-1">Start</label>
                            <input type="date" class="form-control" name="start" value="{{ $start->toDateString() }}">
                        </div>
                        <div class="col-md-3" data-range="custom" style="{{ $range === 'custom' ? '' : 'display:none;' }}">
                            <label class="mb-1">End</label>
                            <input type="date" class="form-control" name="end" value="{{ $end->toDateString() }}">
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button class="btn btn-primary btn-flat" type="submit">Apply</button>
                        </div>
                    </div>
                </form>

                <div class="mb-3">
                    <span class="badge badge-success">Present: {{ $summary['present'] ?? 0 }}</span>
                    <span class="badge badge-warning">Late: {{ $summary['late'] ?? 0 }}</span>
                    <span class="badge badge-danger">Absent: {{ $summary['absent'] ?? 0 }}</span>
                    <span class="badge badge-secondary">Off: {{ $summary['off'] ?? 0 }}</span>
                </div>

                <div class="table-responsive">
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Present</th>
                                <th>Late</th>
                                <th>Absent</th>
                                <th>Off</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($rows as $r)
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($r['date'])->format('M d, Y') }}</td>
                                    <td>{{ $r['present'] }}</td>
                                    <td>{{ $r['late'] }}</td>
                                    <td>{{ $r['absent'] }}</td>
                                    <td>{{ $r['off'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <script>
                    (function () {
                        var sel = document.getElementById('deptReportRange');
                        if (!sel) return;
                        var daily = document.querySelectorAll('[data-range="daily"]');
                        var custom = document.querySelectorAll('[data-range="custom"]');
                        function refresh() {
                            var v = sel.value;
                            daily.forEach(function (el) { el.style.display = (v === 'daily') ? '' : 'none'; });
                            custom.forEach(function (el) { el.style.display = (v === 'custom') ? '' : 'none'; });
                        }
                        sel.addEventListener('change', refresh);
                        refresh();
                    })();
                </script>
            </div>
        </div>
    </div>
</div>
@endsection

