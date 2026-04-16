@extends('layouts.master')

@section('css')
    <style>
        .audit-log-card .page-lead-title { font-size: 1.1rem; font-weight: 600; }
        .audit-log-card .table-wrap {
            border: 1px solid #e9ecef;
            border-radius: 6px;
            overflow: hidden;
        }
        .audit-log-card .table thead th {
            background: #f1f3f5;
            color: #343a40;
            font-weight: 600;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.02em;
            border-bottom: 2px solid #dee2e6;
            white-space: nowrap;
            vertical-align: middle;
            padding: 0.65rem 0.75rem;
        }
        .audit-log-card .table tbody td {
            vertical-align: middle;
            padding: 0.65rem 0.75rem;
            font-size: 0.875rem;
            border-color: #eef0f2;
        }
        .audit-log-card .table tbody tr:hover { background-color: #f8fafb; }
        .audit-log-card .activity-url {
            word-break: break-word;
            max-width: 28rem;
        }
        /* Pagination: Bootstrap 4 nav; keep arrows and numbers aligned */
        .audit-pagination nav { display: block; }
        .audit-pagination .pagination {
            flex-wrap: wrap;
            margin-bottom: 0;
            justify-content: center;
        }
        .audit-pagination .page-link {
            position: relative;
            display: block;
            padding: 0.375rem 0.75rem;
            margin-left: -1px;
            line-height: 1.25;
            color: #2e3f5c;
            background-color: #fff;
            border: 1px solid #dee2e6;
        }
        .audit-pagination .page-item.active .page-link {
            z-index: 1;
            color: #fff;
            background-color: #2e3f5c;
            border-color: #2e3f5c;
        }
        .audit-pagination .page-item.disabled .page-link {
            color: #6c757d;
            pointer-events: none;
            background-color: #fff;
            border-color: #dee2e6;
        }
    </style>
@endsection

@section('breadcrumb')
    <div class="col-sm-6 text-left">
        <h4 class="page-title">Audit log</h4>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin') }}">Home</a></li>
            <li class="breadcrumb-item active">Audit log</li>
        </ol>
    </div>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="card audit-log-card border shadow-sm">
                <div class="card-body">
                    <h4 class="page-lead-title mb-1">Audit trail</h4>
                    <p class="text-muted small mb-4">HTTP requests logged for review. Use filters to narrow results.</p>

                    <form method="GET" action="{{ route('admin.audit_logs') }}" class="mb-4">
                        <div class="form-row">
                            <div class="col-md-3 mb-2">
                                <input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="Search user, route, url…">
                            </div>
                            <div class="col-md-2 mb-2">
                                <select name="user_id" class="form-control">
                                    <option value="">All users</option>
                                    @foreach ($users as $user)
                                        <option value="{{ $user->id }}" {{ (string) request('user_id') === (string) $user->id ? 'selected' : '' }}>
                                            {{ $user->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2 mb-2">
                                <select name="role" class="form-control">
                                    <option value="">All roles</option>
                                    @foreach ($roles as $role)
                                        <option value="{{ $role }}" {{ request('role') === $role ? 'selected' : '' }}>
                                            {{ strtoupper($role) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2 mb-2">
                                <select name="method" class="form-control">
                                    <option value="">All methods</option>
                                    @foreach (['GET', 'POST', 'PUT', 'PATCH', 'DELETE'] as $method)
                                        <option value="{{ $method }}" {{ request('method') === $method ? 'selected' : '' }}>
                                            {{ $method }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3 mb-2 d-flex align-items-end flex-wrap">
                                <button type="submit" class="btn btn-primary btn-sm mr-2 mb-1">Filter</button>
                                <a href="{{ route('admin.audit_logs') }}" class="btn btn-outline-secondary btn-sm mb-1">Reset</a>
                            </div>
                        </div>
                    </form>

                    <div class="table-responsive table-wrap">
                        <table class="table table-hover table-sm mb-0">
                            <thead>
                                <tr>
                                    <th scope="col">Date / time</th>
                                    <th scope="col">User</th>
                                    <th scope="col">Role</th>
                                    <th scope="col">Method</th>
                                    <th scope="col">Activity</th>
                                    <th scope="col">Status</th>
                                    <th scope="col">IP</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($logs as $log)
                                    <tr>
                                        <td>{{ $log->created_at ? $log->created_at->format('M d, Y g:i:s A') : '—' }}</td>
                                        <td>{{ $log->user_name ?? 'System' }}</td>
                                        <td>{{ $log->role_slug ? strtoupper($log->role_slug) : '—' }}</td>
                                        <td><span class="badge badge-secondary">{{ $log->method }}</span></td>
                                        <td>
                                            <div><strong>{{ $log->description }}</strong></div>
                                            <small class="text-muted activity-url d-block">{{ $log->url }}</small>
                                        </td>
                                        <td>{{ $log->status_code ?? '—' }}</td>
                                        <td><code class="small">{{ $log->ip_address ?? '—' }}</code></td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-4">No audit records found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3 audit-pagination">
                        {{ $logs->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
