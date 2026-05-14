@extends('layouts.master')

@section('breadcrumb')
<div class="col-sm-6">
    <h4 class="page-title text-left">Department Management</h4>
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('admin') }}">Home</a></li>
        <li class="breadcrumb-item active">Departments</li>
    </ol>
</div>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-10 col-xl-8">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h4 class="header-title mb-0">Departments</h4>
                    <a href="{{ route('departments.report') }}" class="btn btn-sm btn-outline-secondary" style="border-radius:999px;">
                        <i class="fa fa-bar-chart mr-1"></i> Attendance Report
                    </a>
                </div>
                <p class="text-muted mb-4">This list is fixed for the attendance management system. Click <strong>View Employees</strong> to see attendance for that department.</p>

                <div class="table-responsive">
                    <table class="table table-striped table-bordered mb-0">
                        <thead>
                            <tr>
                                <th style="width: 50px;">#</th>
                                <th>Department Name</th>
                                <th style="width: 160px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($departments as $index => $department)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $department->name }}</td>
                                    <td>
                                        <a href="{{ route('departments.employees', $department) }}"
                                           class="btn btn-sm btn-primary"
                                           style="border-radius:999px;background:#8B4513;border-color:#8B4513;">
                                            <i class="fa fa-users mr-1"></i> View Employees
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
