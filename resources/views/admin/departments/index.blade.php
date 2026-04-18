@extends('layouts.master')

@section('breadcrumb')
    <div class="col-sm-6">
        <h4 class="page-title text-left">Departments</h4>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin') }}">Home</a></li>
            <li class="breadcrumb-item active">Departments</li>
        </ol>
    </div>
@endsection

@section('button')
    <a href="#addDept" data-toggle="modal" class="btn btn-primary btn-sm btn-flat">
        <i class="mdi mdi-plus mr-2"></i>Add
    </a>
@endsection

@section('content')
@include('includes.flash')

@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Description</th>
                                <th>Status</th>
                                <th style="width: 200px;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($departments as $d)
                                <tr>
                                    <td>{{ $d->name }}</td>
                                    <td class="text-muted">{{ $d->description ?: '—' }}</td>
                                    <td>
                                        @if($d->is_active)
                                            <span class="badge badge-success">Active</span>
                                        @else
                                            <span class="badge badge-secondary">Inactive</span>
                                        @endif
                                    </td>
                                    <td class="text-nowrap">
                                        <a href="#editDept{{ $d->id }}" data-toggle="modal" class="btn btn-success btn-sm btn-flat">
                                            <i class="fa fa-edit"></i> Edit
                                        </a>
                                        @if($d->is_active)
                                            <a href="#deactivateDept{{ $d->id }}" data-toggle="modal" class="btn btn-danger btn-sm btn-flat">
                                                <i class="fa fa-ban"></i> Deactivate
                                            </a>
                                        @else
                                            —
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted">No departments yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add -->
<div class="modal fade" id="addDept">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <h4 class="modal-title"><b>Add Department</b></h4>
            <div class="modal-body text-left">
                <form method="POST" action="{{ route('departments.store') }}">
                    @csrf
                    <div class="form-group">
                        <label>Name</label>
                        <input type="text" class="form-control" name="name" required maxlength="128">
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <textarea class="form-control" name="description" rows="3" maxlength="500"></textarea>
                    </div>
                    <div class="form-group">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="dept_active" name="is_active" value="1" checked>
                            <label class="form-check-label" for="dept_active">Active</label>
                        </div>
                    </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default btn-flat pull-left" data-dismiss="modal">
                    <i class="fa fa-close"></i> Close
                </button>
                <button type="submit" class="btn btn-primary btn-flat">
                    <i class="fa fa-save"></i> Save
                </button>
                </form>
            </div>
        </div>
    </div>
</div>

@foreach($departments as $d)
    <!-- Edit -->
    <div class="modal fade" id="editDept{{ $d->id }}">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <h4 class="modal-title"><b>Edit Department</b></h4>
                <div class="modal-body text-left">
                    <form method="POST" action="{{ route('departments.update', $d->id) }}">
                        @csrf
                        @method('PUT')
                        <div class="form-group">
                            <label>Name</label>
                            <input type="text" class="form-control" name="name" required maxlength="128" value="{{ $d->name }}">
                        </div>
                        <div class="form-group">
                            <label>Description</label>
                            <textarea class="form-control" name="description" rows="3" maxlength="500">{{ $d->description }}</textarea>
                        </div>
                        <div class="form-group">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="dept_active_{{ $d->id }}" name="is_active" value="1" {{ $d->is_active ? 'checked' : '' }}>
                                <label class="form-check-label" for="dept_active_{{ $d->id }}">Active</label>
                            </div>
                        </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default btn-flat pull-left" data-dismiss="modal">
                        <i class="fa fa-close"></i> Close
                    </button>
                    <button type="submit" class="btn btn-success btn-flat">
                        <i class="fa fa-check-square-o"></i> Update
                    </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Deactivate -->
    <div class="modal fade" id="deactivateDept{{ $d->id }}">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header" style="align-items: center">
                    <h4 class="modal-title">Deactivate Department</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form method="POST" action="{{ route('departments.destroy', $d->id) }}">
                        @csrf
                        @method('DELETE')
                        <div class="text-center">
                            <h6>Are you sure you want to deactivate:</h6>
                            <h2 class="bold">{{ $d->name }}</h2>
                        </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default btn-flat pull-left" data-dismiss="modal">
                        <i class="fa fa-close"></i> Close
                    </button>
                    <button type="submit" class="btn btn-danger btn-flat">
                        <i class="fa fa-ban"></i> Deactivate
                    </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endforeach

@endsection

