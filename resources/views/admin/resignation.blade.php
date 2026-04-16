@extends('layouts.master')

@section('css')
    <style>
        .admin-resign-card .page-lead-title { font-size: 1.1rem; font-weight: 600; }
        .admin-resign-table-wrap { border: 1px solid #e9ecef; border-radius: 6px; overflow: hidden; }
        .admin-resign-table thead th {
            background: #f1f3f5; color: #343a40; font-weight: 600; font-size: 0.75rem;
            text-transform: uppercase; letter-spacing: 0.02em; border-bottom: 2px solid #dee2e6;
            white-space: nowrap; vertical-align: middle; padding: 0.65rem 0.75rem;
        }
        .admin-resign-table tbody td {
            vertical-align: middle; padding: 0.65rem 0.75rem; font-size: 0.875rem; border-color: #eef0f2;
        }
        .admin-resign-table tbody tr:hover { background-color: #f8fafb; }
        .resign-emp-name { font-weight: 500; color: #212529; }
        .resign-emp-meta { font-size: 0.75rem; color: #868e96; }
        .resign-date-block .primary { font-weight: 600; color: #212529; }
        .resign-date-block .sub { font-size: 0.75rem; color: #868e96; }
        .resign-snippet { max-width: 16rem; color: #495057; font-size: 0.8125rem; line-height: 1.35; }
        .resign-actions .btn { margin: 0.15rem 0.15rem 0.15rem 0; }
        .resign-empty { padding: 2.5rem 1rem; text-align: center; color: #868e96; }
        .modal-dl dt { font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.04em; color: #868e96; margin-bottom: 0.15rem; }
        .modal-dl dd { margin-bottom: 1rem; font-size: 0.9375rem; }
        .modal-dl dd:last-child { margin-bottom: 0; }
    </style>
@endsection

@section('breadcrumb')
    <div class="col-sm-6 text-left">
        <h4 class="page-title">Resignation</h4>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin') }}">Home</a></li>
            <li class="breadcrumb-item active">Resignation requests</li>
        </ol>
    </div>
@endsection

@section('content')
    @include('includes.flash')

    @php
        $statusMeta = [
            0 => ['label' => 'Pending', 'class' => 'warning'],
            1 => ['label' => 'Approved', 'class' => 'success'],
            2 => ['label' => 'Rejected', 'class' => 'danger'],
        ];
    @endphp

    <div class="row">
        <div class="col-12">
            <div class="card admin-resign-card border shadow-sm">
                <div class="card-body">
                    <div class="d-flex flex-wrap align-items-center justify-content-between mb-3">
                        <div>
                            <h4 class="page-lead-title mb-1">Resignation requests</h4>
                            <p class="text-muted mb-0 small">Review notices, last working days, and handover notes. Approve or reject in one place.</p>
                        </div>
                        @if ($requests->count() > 0)
                            <span class="badge badge-primary badge-pill mt-2 mt-md-0 px-3 py-2">{{ $requests->count() }} {{ Str::plural('request', $requests->count()) }}</span>
                        @endif
                    </div>

                    @if ($requests->isEmpty())
                        <div class="resign-empty border rounded bg-light">
                            <p class="mb-0">No resignation requests yet.</p>
                        </div>
                    @else
                        <div class="table-responsive admin-resign-table-wrap">
                            <table class="table table-hover table-sm mb-0 admin-resign-table">
                                <thead>
                                    <tr>
                                        <th scope="col">Employee</th>
                                        <th scope="col">Last working day</th>
                                        <th scope="col">Reason</th>
                                        <th scope="col">Handover</th>
                                        <th scope="col">Status</th>
                                        <th scope="col">Requested</th>
                                        <th scope="col" class="text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($requests as $item)
                                        @php
                                            $emp = $item->employee;
                                            $st = $statusMeta[$item->status] ?? ['label' => '—', 'class' => 'light'];
                                            $lwd = \Carbon\Carbon::parse($item->last_working_day);
                                            $reason = (string) ($item->reason ?? '');
                                            $handover = (string) ($item->handover_notes ?? '');
                                        @endphp
                                        <tr>
                                            <td>
                                                <div class="resign-emp-name">{{ $emp?->name ?? '—' }}</div>
                                                <div class="resign-emp-meta">ID {{ $item->emp_id }}</div>
                                            </td>
                                            <td>
                                                <div class="resign-date-block">
                                                    <div class="primary">{{ $lwd->format('M j, Y') }}</div>
                                                    <div class="sub">{{ $lwd->format('l') }}</div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="resign-snippet text-truncate" title="{{ e($reason) }}">
                                                    {{ $reason !== '' ? Str::limit($reason, 64) : '—' }}
                                                </div>
                                            </td>
                                            <td>
                                                <div class="resign-snippet text-truncate" title="{{ e($handover) }}">
                                                    {{ $handover !== '' ? Str::limit($handover, 48) : '—' }}
                                                </div>
                                            </td>
                                            <td><span class="badge badge-{{ $st['class'] }}">{{ $st['label'] }}</span></td>
                                            <td>
                                                <div class="resign-date-block">
                                                    <div class="primary">{{ $item->created_at->format('M j, Y') }}</div>
                                                    <div class="sub">{{ $item->created_at->format('g:i A') }}</div>
                                                </div>
                                            </td>
                                            <td class="text-right resign-actions text-nowrap">
                                                <button type="button" class="btn btn-outline-secondary btn-sm" data-toggle="modal" data-target="#viewModalResign{{ $item->id }}" title="Details">
                                                    <i class="fa fa-eye"></i>
                                                </button>
                                                @if ($item->status == 0)
                                                    <button type="button" class="btn btn-outline-success btn-sm" data-toggle="modal" data-target="#approveModalResign{{ $item->id }}" title="Approve">
                                                        <i class="fa fa-check"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-outline-danger btn-sm" data-toggle="modal" data-target="#rejectModalResign{{ $item->id }}" title="Reject">
                                                        <i class="fa fa-times"></i>
                                                    </button>
                                                @elseif ($item->status == 1)
                                                    <a href="{{ route('resignation.approvalLetter', $item->id) }}" class="btn btn-outline-primary btn-sm" target="_blank" title="Print letter">
                                                        <i class="fa fa-print"></i>
                                                    </a>
                                                @else
                                                    <small class="text-muted">{{ $item->reviewed_at ? \Carbon\Carbon::parse($item->reviewed_at)->format('M j, Y') : '—' }}</small>
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

    @foreach ($requests as $item)
        @php $emp = $item->employee; @endphp

        <div class="modal fade" id="viewModalResign{{ $item->id }}" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
                <div class="modal-content">
                    <div class="modal-header border-bottom">
                        <h5 class="modal-title">Resignation request</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <dl class="modal-dl mb-0">
                                    <dt>Employee</dt>
                                    <dd>{{ $emp?->name ?? '—' }}</dd>
                                    <dt>Employee ID</dt>
                                    <dd>{{ $item->emp_id }}</dd>
                                    <dt>Department</dt>
                                    <dd>{{ $emp?->department ?? '—' }}</dd>
                                    <dt>Position</dt>
                                    <dd>{{ $emp?->position ?? '—' }}</dd>
                                </dl>
                            </div>
                            <div class="col-md-6">
                                <dl class="modal-dl mb-0">
                                    <dt>Last working day</dt>
                                    <dd>{{ \Carbon\Carbon::parse($item->last_working_day)->format('l, F j, Y') }}</dd>
                                    <dt>Status</dt>
                                    <dd>
                                        @if ($item->status == 0) <span class="badge badge-warning">Pending</span>
                                        @elseif ($item->status == 1) <span class="badge badge-success">Approved</span>
                                        @else <span class="badge badge-danger">Rejected</span>
                                        @endif
                                    </dd>
                                    <dt>Requested on</dt>
                                    <dd>{{ $item->created_at->format('F j, Y') }}, {{ $item->created_at->format('g:i A') }}</dd>
                                </dl>
                            </div>
                            <div class="col-12 mt-3 pt-3 border-top">
                                <dl class="modal-dl mb-0">
                                    <dt>Reason</dt>
                                    <dd class="text-break">{{ $item->reason ?: '—' }}</dd>
                                    <dt>Handover notes</dt>
                                    <dd class="text-break">{{ $item->handover_notes ?: '—' }}</dd>
                                    @if ($item->remarks)
                                        <dt>Admin remarks</dt>
                                        <dd class="text-break">{{ $item->remarks }}</dd>
                                    @endif
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

        <div class="modal fade" id="approveModalResign{{ $item->id }}" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header border-bottom">
                        <h5 class="modal-title">Approve resignation</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <form action="{{ route('resignation.approve', $item->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="modal-body">
                            <p class="text-muted small mb-3">Confirm approval for <strong>{{ $emp?->name ?? 'employee' }}</strong>. Last working day: <strong>{{ \Carbon\Carbon::parse($item->last_working_day)->format('M j, Y') }}</strong>.</p>
                            <div class="form-group mb-0">
                                <label for="approve-resign-remarks-{{ $item->id }}" class="font-weight-bold">Remarks <span class="text-muted font-weight-normal">(optional)</span></label>
                                <textarea id="approve-resign-remarks-{{ $item->id }}" name="remarks" class="form-control" rows="3" placeholder="Internal notes…"></textarea>
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

        <div class="modal fade" id="rejectModalResign{{ $item->id }}" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header border-bottom">
                        <h5 class="modal-title">Reject resignation</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <form action="{{ route('resignation.reject', $item->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="modal-body">
                            <p class="text-muted small mb-3">Provide a clear reason. This may be referenced in follow-up with the employee.</p>
                            <div class="form-group mb-0">
                                <label for="reject-resign-remarks-{{ $item->id }}" class="font-weight-bold">Reason for rejection <span class="text-danger">*</span></label>
                                <textarea id="reject-resign-remarks-{{ $item->id }}" name="remarks" class="form-control" rows="3" required placeholder="Explain the decision…"></textarea>
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
