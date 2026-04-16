@extends('layouts.master')

@section('css')
    <style>
        .admin-leaves-card .page-lead-title { font-size: 1.1rem; font-weight: 600; }
        .admin-leaves-table-wrap { border: 1px solid #e9ecef; border-radius: 6px; overflow: hidden; }
        .admin-leaves-table thead th {
            background: #f1f3f5; color: #343a40; font-weight: 600; font-size: 0.75rem;
            text-transform: uppercase; letter-spacing: 0.02em; border-bottom: 2px solid #dee2e6;
            white-space: nowrap; vertical-align: middle; padding: 0.65rem 0.75rem;
        }
        .admin-leaves-table tbody td {
            vertical-align: middle; padding: 0.65rem 0.75rem; font-size: 0.875rem; border-color: #eef0f2;
        }
        .admin-leaves-table tbody tr:hover { background-color: #f8fafb; }
        .leave-emp-name { font-weight: 500; color: #212529; }
        .leave-emp-meta { font-size: 0.75rem; color: #868e96; }
        .leave-date-block .primary { font-weight: 600; color: #212529; }
        .leave-date-block .sub { font-size: 0.75rem; color: #868e96; }
        .leave-reason-cell { max-width: 14rem; color: #495057; font-size: 0.8125rem; line-height: 1.35; }
        .leave-actions .btn { margin: 0.15rem 0.15rem 0.15rem 0; }
        .leave-empty { padding: 2.5rem 1rem; text-align: center; color: #868e96; }
        .modal-dl dt { font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.04em; color: #868e96; margin-bottom: 0.15rem; }
        .modal-dl dd { margin-bottom: 1rem; font-size: 0.9375rem; }
        .modal-dl dd:last-child { margin-bottom: 0; }
    </style>
@endsection

@section('breadcrumb')
    <div class="col-sm-6 text-left">
        <h4 class="page-title">Leave</h4>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin') }}">Home</a></li>
            <li class="breadcrumb-item active">Leave requests</li>
        </ol>
    </div>
@endsection

@section('content')
    @include('includes.flash')

    @php
        $leaveTypeMeta = [
            1 => ['label' => 'Sick Leave', 'class' => 'info'],
            2 => ['label' => 'Annual Leave', 'class' => 'primary'],
            3 => ['label' => 'Personal Leave', 'class' => 'secondary'],
            4 => ['label' => 'Unpaid Leave', 'class' => 'dark'],
            5 => ['label' => 'Maternity Leave', 'class' => 'warning'],
            6 => ['label' => 'Paternity Leave', 'class' => 'info'],
            7 => ['label' => 'Vacation Leave', 'class' => 'success'],
            8 => ['label' => 'Emergency Leave', 'class' => 'danger'],
            9 => ['label' => 'Other', 'class' => 'secondary'],
        ];
        $statusMeta = [
            0 => ['label' => 'Pending', 'class' => 'warning'],
            1 => ['label' => 'Approved', 'class' => 'success'],
            2 => ['label' => 'Rejected', 'class' => 'danger'],
        ];
    @endphp

    <div class="row">
        <div class="col-12">
            <div class="card admin-leaves-card border shadow-sm">
                <div class="card-body">
                    <div class="d-flex flex-wrap align-items-center justify-content-between mb-3">
                        <div>
                            <h4 class="page-lead-title mb-1">Leave requests</h4>
                            <p class="text-muted mb-0 small">Review applications, approve or reject, and print approval letters.</p>
                        </div>
                        @if ($leaves->count() > 0)
                            <span class="badge badge-primary badge-pill mt-2 mt-md-0 px-3 py-2">{{ $leaves->count() }} {{ Str::plural('request', $leaves->count()) }}</span>
                        @endif
                    </div>

                    @if ($leaves->isEmpty())
                        <div class="leave-empty border rounded bg-light">
                            <p class="mb-0">No leave requests yet.</p>
                        </div>
                    @else
                        <div class="table-responsive admin-leaves-table-wrap">
                            <table class="table table-hover table-sm mb-0 admin-leaves-table">
                                <thead>
                                    <tr>
                                        <th scope="col">Employee</th>
                                        <th scope="col">Type</th>
                                        <th scope="col">Dates</th>
                                        <th scope="col" class="text-center">Days</th>
                                        <th scope="col">Reason</th>
                                        <th scope="col">Status</th>
                                        <th scope="col">Applied</th>
                                        <th scope="col" class="text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($leaves as $leave)
                                        @php
                                            $emp = $leave->employee;
                                            $typeInfo = $leaveTypeMeta[$leave->type] ?? ['label' => 'Type #' . (int) $leave->type, 'class' => 'secondary'];
                                            $st = $statusMeta[$leave->status] ?? ['label' => '—', 'class' => 'light'];
                                            $start = \Carbon\Carbon::parse($leave->leave_date);
                                            $end = $leave->leave_date_end ? \Carbon\Carbon::parse($leave->leave_date_end) : null;
                                            $reason = $leave->reason ?? '';
                                        @endphp
                                        <tr>
                                            <td>
                                                <div class="leave-emp-name">{{ $emp?->name ?? '—' }}</div>
                                                <div class="leave-emp-meta">ID {{ $leave->emp_id }}</div>
                                            </td>
                                            <td>
                                                <span class="badge badge-{{ $typeInfo['class'] }}">{{ $typeInfo['label'] }}</span>
                                            </td>
                                            <td>
                                                <div class="leave-date-block">
                                                    <div class="primary">{{ $start->format('M j, Y') }}</div>
                                                    <div class="sub">
                                                        @if ($end)
                                                            to {{ $end->format('M j, Y') }}
                                                        @else
                                                            {{ $start->format('l') }}
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-center font-weight-bold">{{ $leave->leave_days ?? 1 }}</td>
                                            <td>
                                                <div class="leave-reason-cell text-truncate" title="{{ e($reason) }}">
                                                    {{ $reason !== '' ? Str::limit($reason, 72) : '—' }}
                                                </div>
                                            </td>
                                            <td><span class="badge badge-{{ $st['class'] }}">{{ $st['label'] }}</span></td>
                                            <td>
                                                <div class="leave-date-block">
                                                    <div class="primary">{{ $leave->created_at->format('M j, Y') }}</div>
                                                    <div class="sub">{{ $leave->created_at->format('g:i A') }}</div>
                                                </div>
                                            </td>
                                            <td class="text-right leave-actions text-nowrap">
                                                <button type="button" class="btn btn-outline-secondary btn-sm" data-toggle="modal" data-target="#viewModalLeave{{ $leave->id }}" title="Details">
                                                    <i class="fa fa-eye"></i>
                                                </button>
                                                @if ($leave->status == 0)
                                                    <button type="button" class="btn btn-outline-success btn-sm" data-toggle="modal" data-target="#approveModalLeave{{ $leave->id }}" title="Approve">
                                                        <i class="fa fa-check"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-outline-danger btn-sm" data-toggle="modal" data-target="#rejectModalLeave{{ $leave->id }}" title="Reject">
                                                        <i class="fa fa-times"></i>
                                                    </button>
                                                @else
                                                    @if ($leave->approved_at)
                                                        <small class="text-muted d-block d-lg-inline">{{ \Carbon\Carbon::parse($leave->approved_at)->format('M j, Y') }}</small>
                                                    @endif
                                                    @if ($leave->status == 1)
                                                        <a href="{{ route('leave.approvalLetter', $leave->id) }}" class="btn btn-outline-primary btn-sm" target="_blank" title="Print letter">
                                                            <i class="fa fa-print"></i>
                                                        </a>
                                                    @endif
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @foreach ($leaves as $leave)
        @php
            $emp = $leave->employee;
            $typeInfo = $leaveTypeMeta[$leave->type] ?? ['label' => 'Other', 'class' => 'secondary'];
        @endphp

        <div class="modal fade" id="viewModalLeave{{ $leave->id }}" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
                <div class="modal-content">
                    <div class="modal-header border-bottom">
                        <h5 class="modal-title">Leave request</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <dl class="modal-dl mb-0">
                                    <dt>Employee</dt>
                                    <dd>{{ $emp?->name ?? '—' }}</dd>
                                    <dt>Employee ID</dt>
                                    <dd>{{ $leave->emp_id }}</dd>
                                    <dt>Department</dt>
                                    <dd>{{ $emp?->department ?? '—' }}</dd>
                                    <dt>Position</dt>
                                    <dd>{{ $emp?->position ?? '—' }}</dd>
                                </dl>
                            </div>
                            <div class="col-md-6">
                                <dl class="modal-dl mb-0">
                                    <dt>Leave type</dt>
                                    <dd><span class="badge badge-{{ $typeInfo['class'] }}">{{ $typeInfo['label'] }}</span></dd>
                                    <dt>Start date</dt>
                                    <dd>{{ \Carbon\Carbon::parse($leave->leave_date)->format('l, F j, Y') }}</dd>
                                    <dt>End date</dt>
                                    <dd>{{ $leave->leave_date_end ? \Carbon\Carbon::parse($leave->leave_date_end)->format('l, F j, Y') : '—' }}</dd>
                                    <dt>Total days</dt>
                                    <dd>{{ $leave->leave_days ?? 1 }}</dd>
                                    <dt>Status</dt>
                                    <dd>
                                        @if ($leave->status == 0) <span class="badge badge-warning">Pending</span>
                                        @elseif ($leave->status == 1) <span class="badge badge-success">Approved</span>
                                        @else <span class="badge badge-danger">Rejected</span>
                                        @endif
                                    </dd>
                                </dl>
                            </div>
                            <div class="col-12 mt-3 pt-3 border-top">
                                <dl class="modal-dl mb-0">
                                    <dt>Reason</dt>
                                    <dd>{{ $leave->reason ?? 'No reason provided' }}</dd>
                                    @php $attachmentUrls = $leave->supportingDocumentUrls(); @endphp
                                    @if (count($attachmentUrls))
                                        <dt>Supporting documents</dt>
                                        <dd>
                                            @foreach ($attachmentUrls as $idx => $url)
                                                <a href="{{ $url }}" class="d-inline-block mr-2 mb-1" target="_blank" rel="noopener">Open file {{ $idx + 1 }}</a>
                                            @endforeach
                                        </dd>
                                    @endif
                                    @if ($leave->remarks)
                                        <dt>Admin remarks</dt>
                                        <dd>{{ $leave->remarks }}</dd>
                                    @endif
                                    <dt>Applied on</dt>
                                    <dd>{{ $leave->created_at->format('F j, Y') }}, {{ $leave->created_at->format('g:i A') }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-top bg-light">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="approveModalLeave{{ $leave->id }}" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header border-bottom">
                        <h5 class="modal-title">Approve leave</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <form action="{{ route('leave.approve', $leave->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="modal-body">
                            <p class="text-muted small mb-3">Approve this request for <strong>{{ $emp?->name ?? 'employee' }}</strong> ({{ $typeInfo['label'] }}, {{ \Carbon\Carbon::parse($leave->leave_date)->format('M j') }}@if($leave->leave_date_end) – {{ \Carbon\Carbon::parse($leave->leave_date_end)->format('M j, Y') }}@endif).</p>
                            <div class="form-group mb-0">
                                <label for="approve-remarks-{{ $leave->id }}" class="font-weight-bold">Remarks <span class="text-muted font-weight-normal">(optional)</span></label>
                                <textarea id="approve-remarks-{{ $leave->id }}" name="remarks" class="form-control" rows="3" placeholder="Notes for the employee file…"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer border-top bg-light">
                            <button type="button" class="btn btn-light border" data-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-success"><i class="fa fa-check mr-1"></i> Approve</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="modal fade" id="rejectModalLeave{{ $leave->id }}" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header border-bottom">
                        <h5 class="modal-title">Reject leave</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <form action="{{ route('leave.reject', $leave->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="modal-body">
                            <p class="text-muted small mb-3">The employee will see this message. Please be clear and professional.</p>
                            <div class="form-group mb-0">
                                <label for="reject-remarks-{{ $leave->id }}" class="font-weight-bold">Reason for rejection <span class="text-danger">*</span></label>
                                <textarea id="reject-remarks-{{ $leave->id }}" name="remarks" class="form-control" rows="3" placeholder="Explain why this request cannot be approved…" required></textarea>
                            </div>
                        </div>
                        <div class="modal-footer border-top bg-light">
                            <button type="button" class="btn btn-light border" data-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-danger"><i class="fa fa-times mr-1"></i> Reject</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endforeach
@endsection
