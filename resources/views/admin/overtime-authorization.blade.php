@extends('layouts.master')

@section('content')
@include('includes.flash')

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <h4 class="mt-0 header-title">Overtime Authorization Requests</h4>
                <p class="text-muted mb-3">Review employee overtime authorization forms.</p>

                <div class="table-responsive">
                    <table class="table table-bordered dt-responsive nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Employee</th>
                                <th>Date Filed</th>
                                <th>Rows</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($requests as $req)
                            <tr>
                                <td>{{ $req->id }}</td>
                                <td>{{ $req->employee_name }}</td>
                                <td>{{ \Carbon\Carbon::parse($req->date_filed)->format('M d, Y') }}</td>
                                <td>{{ is_array($req->entries) ? count($req->entries) : 0 }}</td>
                                <td>
                                    @if((int)$req->status === 0)<span class="badge badge-warning">Pending</span>
                                    @elseif((int)$req->status === 1)<span class="badge badge-success">Approved</span>
                                    @else <span class="badge badge-danger">Rejected</span>
                                    @endif
                                </td>
                                <td class="text-nowrap">
                                    <a href="{{ route('overtime_authorization.show', $req->id) }}" class="btn btn-outline-secondary btn-sm mb-2"><i class="fa fa-eye"></i></a>

                                    @if((int)$req->status === 0)
                                    <form action="{{ route('overtime_authorization.approve', $req->id) }}" method="POST" style="display:inline-block;">
                                        @csrf
                                        @method('PUT')
                                        <input type="text" name="remarks" class="form-control form-control-sm mb-2" placeholder="Remarks (optional)">
                                        <button type="submit" class="btn btn-success btn-sm">Approve</button>
                                    </form>
                                    <form action="{{ route('overtime_authorization.reject', $req->id) }}" method="POST" style="display:inline-block;" class="ml-1">
                                        @csrf
                                        @method('PUT')
                                        <input type="text" name="remarks" class="form-control form-control-sm mb-2" placeholder="Rejection reason" required>
                                        <button type="submit" class="btn btn-danger btn-sm">Reject</button>
                                    </form>
                                    @endif

                                    <form action="{{ route('overtime_authorization.destroy', $req->id) }}" method="POST" style="display:inline-block;" class="ml-1" onsubmit="return confirm('Delete this overtime authorization request?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-secondary btn-sm">Delete</button>
                                    </form>

                                    @if((int)$req->status === 1)
                                    <a href="{{ route('overtime_authorization.approvalLetterPdf', $req->id) }}" class="btn btn-outline-primary btn-sm ml-1"><i class="fa fa-download"></i></a>
                                    @endif
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

