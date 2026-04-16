@extends('layouts.master')

@section('css')
    <style>
        .user-mgmt-table th,
        .user-mgmt-table td {
            vertical-align: middle;
            font-size: 0.92rem;
        }
        .summary-card .card-body {
            padding: 1rem 1.1rem;
        }
        .summary-count {
            font-size: 1.4rem;
            font-weight: 700;
            line-height: 1.1;
        }
    </style>
@endsection

@section('breadcrumb')
    <div class="col-sm-6">
        <h4 class="page-title text-left">User Management</h4>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="javascript:void(0);">Home</a></li>
            <li class="breadcrumb-item"><a href="javascript:void(0);">User Management</a></li>
        </ol>
    </div>
@endsection

@section('content')
    @include('includes.flash')

    <div class="row">
        <div class="col-md-3">
            <div class="card summary-card">
                <div class="card-body">
                    <div class="text-muted">Total Users</div>
                    <div class="summary-count">{{ $roleSummary['total'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card summary-card">
                <div class="card-body">
                    <div class="text-muted">Administrators</div>
                    <div class="summary-count">{{ $roleSummary['admin'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card summary-card">
                <div class="card-body">
                    <div class="text-muted">Staff</div>
                    <div class="summary-count">{{ $roleSummary['staff'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card summary-card">
                <div class="card-body">
                    <div class="text-muted">Employees</div>
                    <div class="summary-count">{{ $roleSummary['employee'] }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="mt-0 header-title mb-4">Users & Access Roles</h4>
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered mb-0 user-mgmt-table">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Created</th>
                                    <th style="width: 120px;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($users as $user)
                                    @php $currentRole = $user->roles->first(); @endphp
                                    <tr>
                                        <td>{{ $user->name }}</td>
                                        <td>{{ $user->email }}</td>
                                        <td>
                                            @if($currentRole)
                                                <span class="badge badge-info">{{ $currentRole->name }} ({{ $currentRole->slug }})</span>
                                            @else
                                                <span class="badge badge-secondary">No role</span>
                                            @endif
                                        </td>
                                        <td>{{ $user->created_at ? $user->created_at->format('M d, Y') : '-' }}</td>
                                        <td>
                                            <button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#editAccessModal{{ $user->id }}">
                                                Edit Access
                                            </button>
                                        </td>
                                    </tr>

                                    <div class="modal fade" id="editAccessModal{{ $user->id }}" tabindex="-1" role="dialog" aria-hidden="true">
                                        <div class="modal-dialog" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Edit User Access</h5>
                                                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                </div>
                                                <form action="{{ route('admin.user_management.update', $user->id) }}" method="POST">
                                                    @csrf
                                                    @method('PUT')
                                                    <div class="modal-body">
                                                        <div class="form-group">
                                                            <label>Name</label>
                                                            <input type="text" name="name" class="form-control" value="{{ $user->name }}" required>
                                                        </div>
                                                        <div class="form-group">
                                                            <label>Email</label>
                                                            <input type="email" name="email" class="form-control" value="{{ $user->email }}" required>
                                                        </div>
                                                        <div class="form-group">
                                                            <label>Role</label>
                                                            <select name="role_id" class="form-control" required>
                                                                @foreach($roles as $role)
                                                                    <option value="{{ $role->id }}" {{ $currentRole && $currentRole->id === $role->id ? 'selected' : '' }}>
                                                                        {{ $role->name }} ({{ $role->slug }})
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="form-group mb-0">
                                                            <label>New Password (Optional)</label>
                                                            <input type="password" name="password" class="form-control" placeholder="Leave blank to keep current password">
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                                        <button type="submit" class="btn btn-success">Save Changes</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted">No users found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
