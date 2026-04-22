@extends('layouts.master')

@section('content')
@include('includes.flash')

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <h4 class="mt-0 header-title">Loan Applications</h4>
                <p class="text-muted mb-3">Review submitted company loan applications.</p>

                <div class="table-responsive">
                    <table class="table table-bordered dt-responsive nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Employee</th>
                                <th>Department</th>
                                <th>Date Filed</th>
                                <th>Amount Requested</th>
                                <th>Amount Approved</th>
                                <th>Status</th>
                                <th>Attachments</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($requests as $req)
                                <tr>
                                    <td>{{ $req->id }}</td>
                                    <td>{{ $req->employee->name ?? ('Employee #' . $req->emp_id) }}</td>
                                    <td>{{ $req->employee->department ?? '—' }}</td>
                                    <td>{{ \Carbon\Carbon::parse($req->date_filed)->format('M d, Y') }}</td>
                                    <td>₱{{ number_format((float) $req->amount_requested, 2) }}</td>
                                    <td>{{ $req->amount_approved !== null ? ('₱' . number_format((float) $req->amount_approved, 2)) : '—' }}</td>
                                    <td>
                                        @if((int)$req->status === 0)
                                            <span class="badge badge-warning">Pending</span>
                                        @elseif((int)$req->status === 1)
                                            <span class="badge badge-success">Approved</span>
                                        @else
                                            <span class="badge badge-danger">Rejected</span>
                                        @endif
                                    </td>
                                    <td class="text-nowrap">
                                        @php $paths = is_array($req->supporting_documents) ? $req->supporting_documents : []; @endphp
                                        @if(count($paths))
                                            @foreach($paths as $i => $p)
                                                <a href="{{ route('loan.attachment', ['id' => $req->id, 'index' => $i]) }}" target="_blank" rel="noopener">View {{ $i + 1 }}</a>@if(!$loop->last)<br>@endif
                                            @endforeach
                                        @else — @endif
                                    </td>
                                    <td class="text-nowrap">
                                        <a href="{{ route('loan.show', $req->id) }}" class="btn btn-outline-secondary btn-sm mb-2" title="View">
                                            <i class="fa fa-eye"></i>
                                        </a>

                                        @if((int)$req->status === 0)
                                            <form action="{{ route('loan.approve', $req->id) }}" method="POST" style="display:inline-block;">
                                                @csrf
                                                @method('PUT')
                                                <input type="number" step="0.01" min="0" name="amount_approved" class="form-control form-control-sm mb-2" placeholder="Approved amount (optional)">
                                                <input type="text" name="remarks" class="form-control form-control-sm mb-2" placeholder="Remarks (optional)">
                                                <button type="submit" class="btn btn-success btn-sm">Approve</button>
                                            </form>

                                            <form action="{{ route('loan.reject', $req->id) }}" method="POST" style="display:inline-block;" class="ml-1">
                                                @csrf
                                                @method('PUT')
                                                <input type="text" name="remarks" class="form-control form-control-sm mb-2" placeholder="Rejection remarks (optional)">
                                                <button type="submit" class="btn btn-danger btn-sm">Reject</button>
                                            </form>
                                        @endif

                                        <form action="{{ route('loan.destroy', $req->id) }}" method="POST" style="display:inline-block;" class="ml-1" onsubmit="return confirm('Delete this loan application?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-secondary btn-sm">Delete</button>
                                        </form>

                                        @if((int)$req->status === 1)
                                            <a href="{{ route('loan.approvalLetterPdf', $req->id) }}" class="btn btn-outline-primary btn-sm ml-1" title="Download PDF">
                                                <i class="fa fa-download"></i>
                                            </a>
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

