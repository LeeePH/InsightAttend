@extends('layouts.master')

@section('breadcrumb')
    <div class="col-sm-6">
        <h4 class="page-title text-left">Maintenance</h4>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="javascript:void(0);">Home</a></li>
            <li class="breadcrumb-item"><a href="javascript:void(0);">Maintenance</a></li>
        </ol>
    </div>
@endsection

@section('content')
    @include('includes.flash')

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="mt-0 header-title mb-4">Maintenance Forms</h4>
                    <form method="POST" action="{{ route('admin.maintenance.store') }}">
                        @csrf
                        <div class="form-row">
                            <div class="form-group col-md-3">
                                <label>Employee</label>
                                <select name="employee_id" class="form-control" required>
                                    <option value="">Select employee</option>
                                    @foreach($employees as $employee)
                                        <option value="{{ $employee->id }}">
                                            {{ $employee->name }} - {{ $employee->position }} ({{ strtoupper($employee->employment_status ?? 'active') }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group col-md-2">
                                <label>Form Type</label>
                                <select name="form_type" class="form-control" required>
                                    <option value="status_change">Status Change</option>
                                    <option value="departure">Departure Processing</option>
                                </select>
                            </div>
                            <div class="form-group col-md-2">
                                <label>New Status</label>
                                <select name="to_status" class="form-control" required>
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                    <option value="on_leave">On Leave</option>
                                    <option value="suspended">Suspended</option>
                                    <option value="resigned">Resigned</option>
                                    <option value="terminated">Terminated</option>
                                    <option value="retired">Retired</option>
                                </select>
                            </div>
                            <div class="form-group col-md-2">
                                <label>Effective Date</label>
                                <input type="date" name="effective_date" class="form-control">
                            </div>
                            <div class="form-group col-md-3">
                                <label>Reason</label>
                                <input type="text" name="reason" class="form-control" placeholder="Reason for adjustment">
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Admin Notes</label>
                            <textarea name="notes" rows="3" class="form-control" placeholder="Optional notes for this maintenance form"></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-file-alt"></i> Submit Maintenance Form
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="mt-0 header-title mb-4">Maintenance History</h4>
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered mb-0">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Employee</th>
                                    <th>Type</th>
                                    <th>Status Change</th>
                                    <th>Effective</th>
                                    <th>Reason</th>
                                    <th>Processed By</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($records as $record)
                                    <tr>
                                        <td>{{ $record->created_at ? $record->created_at->format('M d, Y h:i A') : '-' }}</td>
                                        <td>{{ optional($record->employee)->name ?? 'N/A' }}</td>
                                        <td>
                                            @if($record->form_type === 'departure')
                                                <span class="badge badge-danger">Departure</span>
                                            @else
                                                <span class="badge badge-info">Status Change</span>
                                            @endif
                                        </td>
                                        <td>{{ strtoupper($record->from_status ?? '-') }} -> {{ strtoupper($record->to_status ?? '-') }}</td>
                                        <td>{{ $record->effective_date ?? '-' }}</td>
                                        <td>{{ $record->reason ?? '-' }}</td>
                                        <td>{{ optional($record->processor)->name ?? 'N/A' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted">No maintenance records found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">
                        {{ $records->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
