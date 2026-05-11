@extends('layouts.master')

@section('content')
<div class="row">
    <div class="col-lg-10 col-xl-8">
        <div class="card">
            <div class="card-body">
                <h4 class="header-title mb-3">Departments</h4>
                <p class="text-muted mb-4">This list is fixed for the attendance management system.</p>

                <div class="table-responsive">
                    <table class="table table-striped table-bordered mb-0">
                        <thead>
                            <tr>
                                <th style="width: 80px;">#</th>
                                <th>Department Name</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($departments as $index => $department)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $department->name }}</td>
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
