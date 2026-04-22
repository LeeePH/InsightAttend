@extends('layouts.master')

@section('content')
@include('includes.flash')

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <h4 class="mt-0 header-title">Application for Discount</h4>
                <p class="text-muted mb-3">Review discount applications from employees.</p>

                <div class="table-responsive">
                    <table class="table table-bordered dt-responsive nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Employee</th>
                                <th>Date Request</th>
                                <th>Student</th>
                                <th>Discount Applied</th>
                                <th>HR Discount (%)</th>
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
                                <td>{{ \Carbon\Carbon::parse($req->date_request)->format('M d, Y') }}</td>
                                <td>{{ $req->student_name }}</td>
                                <td>{{ $req->discount_applied === 'family_relative' ? 'Family Relative' : 'Employee Privileges' }}</td>
                                <td>{{ $req->tuition_fee_discount_percent !== null ? number_format((float)$req->tuition_fee_discount_percent, 2) . '%' : '—' }}</td>
                                <td>
                                    @if((int)$req->status === 0)<span class="badge badge-warning">Pending</span>
                                    @elseif((int)$req->status === 1)<span class="badge badge-success">Approved</span>
                                    @else <span class="badge badge-danger">Rejected</span>
                                    @endif
                                </td>
                                <td class="text-nowrap">
                                    @php $paths = is_array($req->supporting_documents) ? $req->supporting_documents : []; @endphp
                                    @if(count($paths))
                                        @foreach($paths as $i => $p)
                                            <a href="{{ route('discount.attachment', ['id' => $req->id, 'index' => $i]) }}" target="_blank" rel="noopener">View {{ $i + 1 }}</a>@if(!$loop->last)<br>@endif
                                        @endforeach
                                    @else — @endif
                                </td>
                                <td class="text-nowrap">
                                    <a href="{{ route('discount.show', $req->id) }}" class="btn btn-outline-secondary btn-sm mb-2" title="View">
                                        <i class="fa fa-eye"></i>
                                    </a>

                                    @if((int)$req->status === 0)
                                    <form action="{{ route('discount.approve', $req->id) }}" method="POST" style="display:inline-block;">
                                        @csrf
                                        @method('PUT')
                                        <input type="number" step="0.01" min="0" max="100" name="tuition_fee_discount_percent" class="form-control form-control-sm mb-2" placeholder="HR discount %" required>
                                        <input type="text" name="remarks" class="form-control form-control-sm mb-2" placeholder="Remarks (optional)">
                                        <button type="submit" class="btn btn-success btn-sm">Approve</button>
                                    </form>

                                    <form action="{{ route('discount.reject', $req->id) }}" method="POST" style="display:inline-block;" class="ml-1">
                                        @csrf
                                        @method('PUT')
                                        <input type="text" name="remarks" class="form-control form-control-sm mb-2" placeholder="Rejection reason" required>
                                        <button type="submit" class="btn btn-danger btn-sm">Reject</button>
                                    </form>
                                    @endif

                                    <form action="{{ route('discount.destroy', $req->id) }}" method="POST" style="display:inline-block;" class="ml-1" onsubmit="return confirm('Delete this discount application?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-secondary btn-sm">Delete</button>
                                    </form>

                                    @if((int)$req->status === 1)
                                    <a href="{{ route('discount.approvalLetterPdf', $req->id) }}" class="btn btn-outline-primary btn-sm ml-1" title="Download PDF">
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

