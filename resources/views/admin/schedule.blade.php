@extends('layouts.master')

@section('css')
    <!-- Table css -->
    <link href="{{ URL::asset('plugins/RWD-Table-Patterns/dist/css/rwd-table.min.css') }}" rel="stylesheet"
        type="text/css" media="screen">
@endsection

@section('breadcrumb')
    <div class="col-sm-6">
        <h4 class="page-title text-left">Schedules</h4>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="javascript:void(0);">Home</a></li>
            <li class="breadcrumb-item"><a href="javascript:void(0);">Schedule</a></li>
 

        </ol>
    </div>
@endsection
@section('button')
    <a href="#addnew" data-toggle="modal" class="btn btn-primary btn-sm btn-flat"><i class="mdi mdi-plus mr-2"></i>Add</a>


@endsection

@section('content')
@include('includes.flash')

<!--Show Validation Errors here-->
@if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
<!--End showing Validation Errors here-->

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">

                    <div class="table-rep-plugin">
                        <div class="table-responsive mb-0" data-pattern="priority-columns">
                            <table id="datatable-buttons" class="table table-striped table-bordered dt-responsive nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                                        
                                <thead>
                                    <tr>
                                        <th data-priority="1">ID</th>
                                        <th data-priority="2">Schedule</th>
                                        <th data-priority="3">Type</th>
                                        <th data-priority="4">Time In</th>
                                        <th data-priority="5">Time Out</th>
                                        <th data-priority="6">Break</th>
                                        <th data-priority="5">Action</th>
                                     

                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($schedules as $schedule)
                                        <tr>
                                            <td> {{ $schedule->id }} </td>
                                            <td> {{ $schedule->slug }} </td>
                                            <td>
                                                @if (($schedule->schedule_type ?? 'fixed') === 'shifting')
                                                    <span class="badge badge-info">Shifting</span>
                                                @else
                                                    <span class="badge badge-secondary">Fixed</span>
                                                @endif
                                            </td>
                                            <td>
                                                {{ $schedule->time_in ? \Carbon\Carbon::parse($schedule->time_in)->format('g:i A') : '—' }}
                                            </td>
                                            <td>
                                                {{ $schedule->time_out ? \Carbon\Carbon::parse($schedule->time_out)->format('g:i A') : '—' }}
                                            </td>
                                            <td>
                                                {{ (int) ($schedule->break_minutes ?? 0) }} min
                                            </td>
                                            <td>

                                                @if (($schedule->schedule_type ?? 'fixed') === 'shifting')
                                                    <a href="{{ route('schedule.shifts', $schedule) }}"
                                                        class="btn btn-info btn-sm btn-flat">
                                                        <i class="fa fa-random"></i> Shifts
                                                    </a>
                                                @endif
                                                <a href="#edit{{ $schedule->slug }}" data-toggle="modal"
                                                    class="btn btn-success btn-sm edit btn-flat"><i class='fa fa-edit'></i>
                                                    Edit</a>
                                                <a href="#delete{{ $schedule->slug }}" data-toggle="modal"
                                                    class="btn btn-danger btn-sm delete btn-flat"><i
                                                        class='fa fa-trash'></i> Delete</a>

                                            </td>
                                        </tr>
                                    @endforeach


                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div> <!-- end col -->
    </div> <!-- end row -->

    @foreach ($schedules as $schedule)
        @include('includes.edit_delete_schedule')
    @endforeach

    @include('includes.add_schedule')

@endsection


@section('script')
    <!-- Responsive-table-->
    <script src="{{ URL::asset('plugins/RWD-Table-Patterns/dist/js/rwd-table.min.js') }}"></script>
@endsection

@section('script')
    <script>
        $(function() {
            $('.table-responsive').responsiveTable({
                addDisplayAllBtn: 'btn btn-secondary'
            });
        });
    </script>
@endsection
