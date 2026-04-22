@extends('layouts.master')

@section('css')
    @include('includes.employee_request_form_theme', ['pageWrapperClass' => 'ot-page'])
@endsection

@section('content')
@include('includes.flash')

<div class="row ot-page">
    <div class="col-12 px-1">
        <div class="card">
            <div class="card-body">
                <h4 class="page-header-title"><i class="fa fa-file-text-o mr-1"></i> Overtime Authorization Form</h4>
                <p class="page-subtitle">Preview of submitted details.</p>

                <div class="table-responsive mt-3">
                    <table class="table table-bordered">
                        <tbody>
                            <tr><th style="width:220px;">Name</th><td>{{ $requestItem->employee_name }}</td></tr>
                            <tr><th>Date Filed</th><td>{{ \Carbon\Carbon::parse($requestItem->date_filed)->format('M d, Y') }}</td></tr>
                            <tr><th>Position</th><td>{{ $requestItem->employee_position }}</td></tr>
                            <tr><th>Department</th><td>{{ $requestItem->employee_department }}</td></tr>
                            <tr>
                                <th>Status</th>
                                <td>
                                    @if((int)$requestItem->status === 0)<span class="theme-badge">Pending</span>
                                    @elseif((int)$requestItem->status === 1)<span class="theme-badge">Approved</span>
                                    @else <span class="theme-badge">Rejected</span>
                                    @endif
                                </td>
                            </tr>
                            @if($requestItem->remarks)<tr><th>Remarks</th><td>{{ $requestItem->remarks }}</td></tr>@endif
                        </tbody>
                    </table>
                </div>

                <div class="table-responsive mt-3">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Work From</th>
                                <th>Work To</th>
                                <th>OT From</th>
                                <th>OT To</th>
                                <th>Total OT Hours</th>
                                <th>Reason for Overtime</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach((array) $requestItem->entries as $row)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($row['date'])->format('M d, Y') }}</td>
                                <td>{{ \Carbon\Carbon::createFromFormat('H:i', $row['work_from'])->format('h:i A') }}</td>
                                <td>{{ \Carbon\Carbon::createFromFormat('H:i', $row['work_to'])->format('h:i A') }}</td>
                                <td>{{ \Carbon\Carbon::createFromFormat('H:i', $row['ot_from'])->format('h:i A') }}</td>
                                <td>{{ \Carbon\Carbon::createFromFormat('H:i', $row['ot_to'])->format('h:i A') }}</td>
                                <td>{{ $row['total_hours'] }}</td>
                                <td>{{ $row['reason'] }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    @if((int)$requestItem->status === 1)
                    <a href="{{ route('overtime_authorization.approvalLetterPdf', $requestItem->id) }}" class="btn theme-btn"><i class="fa fa-download"></i> Download PDF</a>
                    @endif
                    <a href="{{ url()->previous() }}" class="btn theme-btn ml-2"><i class="fa fa-arrow-left"></i> Back</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

