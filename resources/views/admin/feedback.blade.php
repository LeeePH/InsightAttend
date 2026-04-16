@extends('layouts.master')

@section('css')
    <!-- Table css -->
    <link href="{{ URL::asset('plugins/RWD-Table-Patterns/dist/css/rwd-table.min.css') }}" rel="stylesheet"
        type="text/css" media="screen">
@endsection

@section('breadcrumb')
    <div class="col-sm-6">
        <h4 class="page-title text-left">Feedback Management</h4>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="javascript:void(0);">Home</a></li>
            <li class="breadcrumb-item"><a href="javascript:void(0);">Feedback</a></li>
        </ol>
    </div>
@endsection

@section('content')
@include('includes.flash')

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-body">
                <h4 class="mt-0 header-title mb-4">Employee Feedback</h4>
                
                <div class="table-rep-plugin">
                    <div class="table-responsive mb-0" data-pattern="priority-columns">
                        <table id="datatable-buttons" class="table table-striped table-bordered dt-responsive nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                            <thead>
                                <tr>
                                    <th data-priority="1">Employee</th>
                                    <th data-priority="2">Subject</th>
                                    <th data-priority="3">Message</th>
                                    <th data-priority="4">Date</th>
                                    <th data-priority="5">Status</th>
                                    <th data-priority="6">Actions</th>
                                </tr>
                            </thead>
                            
                            <tbody>
                                @foreach($feedbacks as $feedback)
                                <tr>
                                    <td>
                                        <strong>{{ $feedback->employee->name ?? 'N/A' }}</strong><br>
                                        <small class="text-muted">{{ $feedback->employee->department ?? '' }}</small>
                                    </td>
                                    <td>{{ $feedback->subject }}</td>
                                    <td>{{ Str::limit($feedback->message, 80) }}</td>
                                    <td>{{ \Carbon\Carbon::parse($feedback->created_at)->format('M d, Y') }}</td>
                                    <td>
                                        @if($feedback->status == 0)
                                            <span class="badge badge-warning">Pending</span>
                                        @elseif($feedback->status == 1)
                                            <span class="badge badge-info">Read</span>
                                        @else
                                            <span class="badge badge-success">Resolved</span>
                                        @endif
                                    </td>
                                    <td>
                                        <button class="btn btn-info btn-sm" data-toggle="modal" data-target="#viewModal{{ $feedback->id }}">
                                            <i class="fa fa-eye"></i> View
                                        </button>
                                        
                                        @if($feedback->status == 0)
                                            <a href="{{ route('admin.feedback.read', $feedback->id) }}" class="btn btn-primary btn-sm">
                                                <i class="fa fa-check"></i> Mark Read
                                            </a>
                                        @endif
                                        
                                        @if($feedback->status != 2)
                                            <a href="{{ route('admin.feedback.resolve', $feedback->id) }}" class="btn btn-success btn-sm">
                                                <i class="fa fa-check-circle"></i> Resolve
                                            </a>
                                        @endif
                                    </td>
                                </tr>

                                <!-- View Modal -->
                                <div class="modal fade" id="viewModal{{ $feedback->id }}" tabindex="-1" role="dialog">
                                    <div class="modal-dialog" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Feedback Details</h5>
                                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                                            </div>
                                            <div class="modal-body">
                                                <p><strong>From:</strong> {{ $feedback->employee->name ?? 'N/A' }}</p>
                                                <p><strong>Department:</strong> {{ $feedback->employee->department ?? 'N/A' }}</p>
                                                <p><strong>Subject:</strong> {{ $feedback->subject }}</p>
                                                <p><strong>Date:</strong> {{ \Carbon\Carbon::parse($feedback->created_at)->format('M d, Y h:i A') }}</p>
                                                <hr>
                                                <p><strong>Message:</strong></p>
                                                <p>{{ $feedback->message }}</p>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                                @if($feedback->status != 2)
                                                <a href="{{ route('admin.feedback.resolve', $feedback->id) }}" class="btn btn-success">
                                                    <i class="fa fa-check-circle"></i> Mark as Resolved
                                                </a>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
