@extends('layouts.master')

@section('breadcrumb')
    <div class="col-sm-6 text-left">
        <h4 class="page-title">Class sections</h4>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="javascript:void(0);">Home</a></li>
            <li class="breadcrumb-item active">Class sections</li>
        </ol>
    </div>
@endsection

@section('content')
    @include('includes.flash')

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0 pl-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row mb-3">
        <div class="col-md-8">
            <p class="text-muted mb-0">Define faculty class sections (e.g. BSIT 1-1) by scheduling department and year level. These are used when assigning timetable entries.</p>
        </div>
        <div class="col-md-4 text-md-right">
            @if(auth()->user()->hasAnyRole(['admin','hr']))
                <form method="get" action="{{ route('class_sections.index') }}" class="form-inline d-inline-block mb-2">
                    <select name="department_key" class="form-control form-control-sm" onchange="this.form.submit()">
                        <option value="">All departments</option>
                        @foreach(\App\Services\SchedulingDepartmentService::KEYS as $k)
                            <option value="{{ $k }}" {{ request('department_key') === $k ? 'selected' : '' }}>{{ $departmentLabels[$k] ?? $k }}</option>
                        @endforeach
                    </select>
                </form>
            @endif
            <button type="button" class="btn btn-sm btn-primary mb-2" data-toggle="modal" data-target="#addSectionModal">Add section</button>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-bordered mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>Department</th>
                            <th>Year level</th>
                            <th>Section label</th>
                            <th style="width: 160px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($sections as $section)
                            <tr>
                                <td>{{ $departmentLabels[$section->department_key] ?? $section->department_key }}</td>
                                <td>{{ $section->year_level ? 'Year '.$section->year_level : '—' }}</td>
                                <td class="font-weight-bold">{{ $section->section_label }}</td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-success" data-toggle="modal" data-target="#editSectionModal{{ $section->id }}">Edit</button>
                                    <form action="{{ route('class_sections.destroy', $section) }}" method="post" class="d-inline" onsubmit="return confirm('Delete this section?');">
                                        @csrf
                                        @method('DELETE')
                                        <input type="hidden" name="return_department_key" value="{{ request('department_key') }}">
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">No sections yet. Add BSIT 1-1, BSED 2-2, etc.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade" id="addSectionModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form method="post" action="{{ route('class_sections.store') }}">
                    @csrf
                    <input type="hidden" name="return_department_key" value="{{ request('department_key') }}">
                    <div class="modal-header">
                        <h5 class="modal-title">Add class section</h5>
                        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Scheduling department</label>
                            @if(auth()->user()->hasRole('secretary'))
                                @php $sk = strtoupper((string) auth()->user()->managed_schedule_department); @endphp
                                <input type="hidden" name="department_key" value="{{ $sk }}">
                                <input type="text" class="form-control" disabled value="{{ $departmentLabels[$sk] ?? $sk }}">
                            @else
                                <select name="department_key" class="form-control" required>
                                    @foreach(\App\Services\SchedulingDepartmentService::KEYS as $k)
                                        <option value="{{ $k }}" {{ request('department_key') === $k ? 'selected' : '' }}>
                                            {{ $departmentLabels[$k] ?? $k }}
                                        </option>
                                    @endforeach
                                </select>
                            @endif
                        </div>
                        <div class="form-group">
                            <label>Year level (optional)</label>
                            <select name="year_level" class="form-control">
                                <option value="">—</option>
                                @for ($y = 1; $y <= 6; $y++)
                                    <option value="{{ $y }}">Year {{ $y }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="form-group mb-0">
                            <label>Section label</label>
                            <input type="text" name="section_label" class="form-control" placeholder="e.g. BSIT 1-1" required maxlength="64">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @foreach ($sections as $section)
        <div class="modal fade" id="editSectionModal{{ $section->id }}" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <form method="post" action="{{ route('class_sections.update', $section) }}">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="return_department_key" value="{{ request('department_key') }}">
                        <div class="modal-header">
                            <h5 class="modal-title">Edit section</h5>
                            <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                        </div>
                        <div class="modal-body">
                            <div class="form-group">
                                <label>Scheduling department</label>
                                @if(auth()->user()->hasRole('secretary'))
                                    <input type="hidden" name="department_key" value="{{ $section->department_key }}">
                                    <input type="text" class="form-control" disabled value="{{ $departmentLabels[$section->department_key] ?? $section->department_key }}">
                                @else
                                    <select name="department_key" class="form-control" required>
                                        @foreach(\App\Services\SchedulingDepartmentService::KEYS as $k)
                                            <option value="{{ $k }}" {{ $section->department_key === $k ? 'selected' : '' }}>{{ $departmentLabels[$k] ?? $k }}</option>
                                        @endforeach
                                    </select>
                                @endif
                            </div>
                            <div class="form-group">
                                <label>Year level (optional)</label>
                                <select name="year_level" class="form-control">
                                    <option value="">—</option>
                                    @for ($y = 1; $y <= 6; $y++)
                                        <option value="{{ $y }}" {{ (int) ($section->year_level ?? 0) === $y ? 'selected' : '' }}>Year {{ $y }}</option>
                                    @endfor
                                </select>
                            </div>
                            <div class="form-group mb-0">
                                <label>Section label</label>
                                <input type="text" name="section_label" class="form-control" value="{{ $section->section_label }}" required maxlength="64">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-success">Update</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endforeach
@endsection
