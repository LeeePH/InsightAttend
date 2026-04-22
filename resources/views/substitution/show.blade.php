@extends('layouts.master')

@section('css')
    @include('includes.employee_request_form_theme', ['pageWrapperClass' => 'substitution-page'])
@endsection

@section('content')
@include('includes.flash')

<div class="row substitution-page">
    <div class="col-12 px-1">
        <div class="card">
            <div class="card-body">
                <h4 class="page-header-title"><i class="fa fa-file-text-o mr-1"></i> Subsitution Form</h4>
                <p class="page-subtitle">Preview of submitted details.</p>

                <div class="table-responsive mt-3">
                    <table class="table table-bordered">
                        <tbody>
                            <tr><th style="width:240px;">Absent Teacher</th><td>{{ $requestItem->absent_teacher_name }}</td></tr>
                            <tr><th>Substitute Teacher</th><td>{{ $requestItem->substitute_teacher_name }}</td></tr>
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
                                <th>Subject/s</th>
                                <th>Year/Section</th>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Room</th>
                                <th>No. of Hours</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach((array) $requestItem->entries as $row)
                            <tr>
                                <td>{{ $row['subject'] ?? '' }}</td>
                                <td>{{ $row['year_section'] ?? '' }}</td>
                                <td>{{ !empty($row['date']) ? \Carbon\Carbon::parse($row['date'])->format('M d, Y') : '' }}</td>
                                <td>{{ $row['time'] ?? '' }}</td>
                                <td>{{ $row['room'] ?? '' }}</td>
                                <td>{{ $row['hours'] ?? '' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    @if((int)$requestItem->status === 1)
                    <a href="{{ route('substitution.approvalLetterPdf', $requestItem->id) }}" class="btn theme-btn"><i class="fa fa-download"></i> Download PDF</a>
                    @endif
                    <a href="{{ url()->previous() }}" class="btn theme-btn ml-2"><i class="fa fa-arrow-left"></i> Back</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
