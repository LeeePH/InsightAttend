@extends('layouts.master')

@section('css')
    <style>
        .timetable-table th {
            background: #f4ede6;
            color: #3d2814;
            font-weight: 700;
            text-align: center;
            vertical-align: middle !important;
            border-color: #c4a57a !important;
        }
        .timetable-table td {
            vertical-align: middle !important;
            border-color: #d9c4a8 !important;
        }
        .timetable-table .col-emp { text-align: left; }
        .timetable-table .col-center { text-align: center; }
        .timetable-row-today {
            background: rgba(139, 69, 19, 0.12) !important;
            box-shadow: inset 3px 0 0 #8b4513;
        }
        .timetable-upcoming-pill {
            font-size: 0.72rem;
            font-weight: 600;
            color: #8b4513;
            border: 1px solid #c4a57a;
            border-radius: 999px;
            padding: 0.1rem 0.45rem;
            margin-left: 0.35rem;
            white-space: nowrap;
        }
        .today-strip {
            border: 1px solid #c4a57a;
            border-radius: 6px;
            background: #fffdf9;
            padding: 0.75rem 1rem;
            margin-bottom: 1rem;
        }
        .today-strip strong { color: #8b4513; }
        .schedule-entry-modal .modal-dialog {
            max-width: 920px;
        }
        .schedule-entry-modal .modal-body {
            max-height: 70vh;
            overflow-y: auto;
        }
    </style>
@endsection

@section('breadcrumb')
    <div class="col-sm-6">
        <h4 class="page-title text-left">Schedule Management</h4>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="javascript:void(0);">Home</a></li>
            <li class="breadcrumb-item active">Schedule Management</li>
        </ol>
    </div>
@endsection

@section('content')
    @include('includes.flash')

    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>Could not save.</strong>
            <ul class="mb-0 pl-3">
                @foreach ($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row mb-3">
        <div class="col-md-8">
            <div class="today-strip">
                <strong>Today ({{ $dayShort($todayDow) }})</strong>
                @if($todayEntries->isEmpty())
                    <span class="text-muted ml-1">No entries scheduled for today.</span>
                @else
                    <ul class="mb-0 mt-2 pl-3">
                        @foreach($todayEntries as $te)
                            <li>
                                {{ $te->employee->name }} -
                                {{ collect($te->resolvedTimeBlocks())->map(fn ($block) => \Carbon\Carbon::parse($block['time_start'])->format('g:i A') . '-' . \Carbon\Carbon::parse($block['time_end'])->format('g:i A'))->implode(', ') }},
                                {{ $te->room }} ({{ $te->department_key }})
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
        <div class="col-md-4 text-md-right">
            @if(auth()->user()->hasAnyRole(['admin','hr']))
                <form method="get" action="{{ route('employee_timetable.index') }}" class="form-inline d-inline-block mb-2">
                    <select name="department_key" class="form-control form-control-sm" onchange="this.form.submit()">
                        <option value="">All departments</option>
                        @foreach(\App\Services\SchedulingDepartmentService::KEYS as $k)
                            <option value="{{ $k }}" {{ request('department_key') === $k ? 'selected' : '' }}>{{ $departmentLabels[$k] ?? $k }}</option>
                        @endforeach
                    </select>
                </form>
            @endif
            <a href="{{ route('employee_timetable.pdf', request()->only('department_key')) }}" class="btn btn-sm btn-primary mb-2" style="background:#8B4513;border-color:#6b3410;">
                Download PDF
            </a>
            <button type="button" class="btn btn-sm btn-primary mb-2" style="background:#a0522d;border-color:#6b3410;" data-toggle="modal" data-target="#addTimetableModal">
                Add entry
            </button>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered timetable-table mb-0">
                    <thead>
                        <tr>
                            <th style="width:110px;">Code</th>
                            <th style="min-width:220px;">Course Description</th>
                            <th style="width:80px;">Day/s</th>
                            <th style="min-width:150px;">Time</th>
                            <th style="min-width:90px;">Room</th>
                            <th style="min-width:170px;">Faculty</th>
                            <th style="width:120px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($entries as $row)
                            @php $isToday = (int) $row->day_of_week === $todayDow; @endphp
                            <tr class="{{ $isToday ? 'timetable-row-today' : '' }}">
                                <td class="col-center">{{ $row->course?->code ?: '-' }}</td>
                                <td class="col-emp">{{ $row->course?->name ?: '-' }}</td>
                                <td class="col-center">
                                    {{ $dayShort((int) $row->day_of_week) }}
                                    @if($isToday)
                                        <span class="timetable-upcoming-pill">Today</span>
                                    @endif
                                </td>
                                <td class="col-center">
                                    @foreach($row->resolvedTimeBlocks() as $block)
                                        <div>{{ \Carbon\Carbon::parse($block['time_start'])->format('g:i A') }} - {{ \Carbon\Carbon::parse($block['time_end'])->format('g:i A') }}</div>
                                    @endforeach
                                </td>
                                <td class="col-center">{{ $row->room }}</td>
                                <td class="col-emp">{{ $row->employee->name }}</td>
                                <td class="col-center">
                                    <button type="button" class="btn btn-sm btn-primary" data-toggle="modal" data-target="#editTimetableModal{{ $row->id }}">Edit</button>
                                    <form action="{{ route('employee_timetable.destroy', ['entry' => $row->id]) }}" method="post" class="d-inline" onsubmit="return confirm('Delete this schedule entry?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">No timetable entries yet. Use "Add entry" to create the first row.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade schedule-entry-modal" id="addTimetableModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
            <div class="modal-content">
                <form method="post" action="{{ route('employee_timetable.store') }}">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Add schedule entry</h5>
                        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                    </div>
                    <div class="modal-body">
                        @include('admin.employee_timetable._form_fields', ['employees' => $employees, 'courses' => $courses])
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @foreach($entries as $row)
        <div class="modal fade schedule-entry-modal" id="editTimetableModal{{ $row->id }}" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
                <div class="modal-content">
                    <form method="post" action="{{ route('employee_timetable.update', ['entry' => $row->id]) }}">
                        @csrf
                        @method('PUT')
                        <div class="modal-header">
                            <h5 class="modal-title">Edit schedule entry</h5>
                            <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                        </div>
                        <div class="modal-body">
                            @include('admin.employee_timetable._form_fields', [
                                'employees' => $employees,
                                'courses' => $courses,
                                'entry' => $row,
                            ])
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Update</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endforeach
@endsection

@section('script')
<script>
    (function () {
        function bindContainer(container) {
            var addBtn = container.querySelector('.js-add-time-block');
            var list = container.querySelector('.js-time-blocks');
            if (!addBtn || !list) return;

            addBtn.addEventListener('click', function () {
                var rows = list.querySelectorAll('.js-time-block-row');
                var template = rows[rows.length - 1];
                var clone = template.cloneNode(true);
                clone.querySelectorAll('input').forEach(function (input) {
                    input.value = '';
                });
                var removeBtn = clone.querySelector('.js-remove-time-block');
                if (removeBtn) {
                    removeBtn.disabled = false;
                }
                list.appendChild(clone);
                refreshRemoveButtons(list);
            });

            list.addEventListener('click', function (event) {
                var removeBtn = event.target.closest('.js-remove-time-block');
                if (!removeBtn) return;
                var rows = list.querySelectorAll('.js-time-block-row');
                if (rows.length <= 1) return;
                removeBtn.closest('.js-time-block-row').remove();
                refreshRemoveButtons(list);
            });

            refreshRemoveButtons(list);
        }

        function refreshRemoveButtons(list) {
            var rows = list.querySelectorAll('.js-time-block-row');
            rows.forEach(function (row, index) {
                var btn = row.querySelector('.js-remove-time-block');
                if (!btn) return;
                btn.disabled = rows.length === 1 && index === 0;
            });
        }

        document.querySelectorAll('.schedule-entry-modal .modal-content').forEach(bindContainer);
    })();
</script>
@endsection
