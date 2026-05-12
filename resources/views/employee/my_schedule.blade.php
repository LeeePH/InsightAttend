@extends('layouts.master')

@section('css')
    <style>
        .mysched-banner {
            background: linear-gradient(135deg, #8b4513 0%, #5c2d0d 100%);
            color: #fff;
            border-radius: 10px;
            padding: 1.25rem 1.4rem;
            margin-bottom: 1rem;
        }
        .mysched-banner h4 { color: #fff; margin: 0; font-weight: 700; }
        .mysched-banner p { opacity: 0.95; }
        .mysched-actions {
            display: flex;
            gap: 0.75rem;
            flex-wrap: wrap;
            margin-top: 1rem;
        }
        .mysched-actions .btn {
            border-radius: 999px;
            padding-inline: 1rem;
            font-weight: 600;
        }
        .mysched-actions .btn-primary {
            background: #fff3ea;
            color: #5c2d0d;
            border-color: #fff3ea;
        }
        .mysched-actions .btn-outline-light {
            border-color: rgba(255,255,255,0.45);
        }
        .mysched-table th {
            background: #f4ede6;
            color: #3d2814;
            font-weight: 700;
            border-color: #c4a57a !important;
        }
        .mysched-table td { border-color: #d9c4a8 !important; vertical-align: middle !important; }
        .mysched-row-today { background: rgba(139, 69, 19, 0.1) !important; box-shadow: inset 3px 0 0 #8b4513; }
    </style>
@endsection

@section('breadcrumb')
    <div class="col-sm-6">
        <h4 class="page-title text-left">My schedule</h4>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('employee.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">My schedule</li>
        </ol>
    </div>
@endsection

@section('content')
    <div class="mysched-banner">
        <h4>{{ $employee->name }}</h4>
        <p class="mb-0 small">View your weekly timetable, then open the export preview to print or download it as PDF.</p>
        <div class="mysched-actions">
            <a href="{{ route('employee.my_schedule.preview') }}" class="btn btn-primary">Preview Schedule</a>
            <a href="{{ route('employee.my_schedule.pdf') }}" class="btn btn-outline-light">Export PDF</a>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered mysched-table mb-0">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Course</th>
                            <th>Day</th>
                            <th>Time</th>
                            <th>Room</th>
                            <th>Section</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($scheduleRows as $row)
                            <tr class="{{ (int) $row['day_of_week'] === $todayDow ? 'mysched-row-today' : '' }}">
                                <td>{{ $row['course_code'] ?: '-' }}</td>
                                <td>{{ $row['course_name'] ?: '-' }}</td>
                                <td>{{ $dayShort((int) $row['day_of_week']) }}</td>
                                <td>
                                    @foreach($row['times'] as $time)
                                        <div>{{ $time['display'] }}</div>
                                    @endforeach
                                </td>
                                <td>{{ $row['room'] }}</td>
                                <td>{{ $row['section_label'] ?: '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">No timetable entries have been published for your account yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
