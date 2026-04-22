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
                <p class="page-subtitle">Submit class subsitution details for approval.</p>

                <div class="employee-panel mt-4">
                    <h6><i class="fa fa-user mr-1"></i> Employee Information</h6>
                    <p><strong>Name:</strong> {{ $currentEmployee->name }}</p>
                    <p><strong>Department:</strong> {{ $currentEmployee->department ?? 'N/A' }}</p>
                    <p><strong>Position:</strong> {{ $currentEmployee->position ?? 'N/A' }}</p>
                </div>

                <form method="POST" action="{{ route('substitution.storeRequest') }}" id="substitutionForm">
                    @csrf

                    <div class="row mt-3">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Name of Absent Teacher</label>
                                <input type="text" class="form-control" name="absent_teacher_name" value="{{ old('absent_teacher_name', $currentEmployee->name ?? '') }}" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Name of Substitute Teacher</label>
                                <input type="text" class="form-control" name="substitute_teacher_name" value="{{ old('substitute_teacher_name') }}" required>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered" id="substitutionRowsTable">
                            <thead>
                                <tr>
                                    <th>Subject/s</th>
                                    <th>Year/Section</th>
                                    <th>Date</th>
                                    <th>Time</th>
                                    <th>Room</th>
                                    <th>No. of Hours</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $oldEntries = old('entries', [['subject' => '', 'year_section' => '', 'date' => '', 'time' => '', 'room' => '', 'hours' => '']]); @endphp
                                @foreach($oldEntries as $idx => $entry)
                                <tr>
                                    <td><input type="text" class="form-control form-control-sm" name="entries[{{ $idx }}][subject]" value="{{ $entry['subject'] ?? '' }}"></td>
                                    <td><input type="text" class="form-control form-control-sm" name="entries[{{ $idx }}][year_section]" value="{{ $entry['year_section'] ?? '' }}"></td>
                                    <td><input type="date" class="form-control form-control-sm" name="entries[{{ $idx }}][date]" value="{{ $entry['date'] ?? '' }}"></td>
                                    <td><input type="text" class="form-control form-control-sm" name="entries[{{ $idx }}][time]" value="{{ $entry['time'] ?? '' }}"></td>
                                    <td><input type="text" class="form-control form-control-sm" name="entries[{{ $idx }}][room]" value="{{ $entry['room'] ?? '' }}"></td>
                                    <td><input type="text" class="form-control form-control-sm" name="entries[{{ $idx }}][hours]" value="{{ $entry['hours'] ?? '' }}"></td>
                                    <td><button type="button" class="btn btn-sm btn-outline-danger remove-row">&times;</button></td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <button type="button" class="btn theme-btn mb-3" id="addSubstitutionRow"><i class="fa fa-plus"></i> Add Row</button>

                    <div class="form-group mb-0">
                        <button type="submit" class="btn theme-btn"><i class="fa fa-paper-plane"></i> Submit Subsitution Form</button>
                        <a href="{{ route('employee.dashboard') }}" class="btn theme-btn ml-2"><i class="fa fa-home"></i> Back to Dashboard</a>
                    </div>
                </form>

                <div class="mt-4">
                    <h5 class="page-header-title mb-3">My Subsitution Requests</h5>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Submitted</th>
                                    <th>Substitute Teacher</th>
                                    <th>Rows</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($history as $item)
                                <tr>
                                    <td>{{ optional($item->created_at)->format('M d, Y') }}</td>
                                    <td>{{ $item->substitute_teacher_name }}</td>
                                    <td>{{ is_array($item->entries) ? count($item->entries) : 0 }}</td>
                                    <td>
                                        @if((int)$item->status === 0)<span class="theme-badge">Pending</span>
                                        @elseif((int)$item->status === 1)<span class="theme-badge">Approved</span>
                                        @else <span class="theme-badge">Rejected</span>
                                        @endif
                                    </td>
                                    <td><a href="{{ route('substitution.show', $item->id) }}" class="btn theme-btn btn-sm"><i class="fa fa-eye"></i> View</a></td>
                                </tr>
                                @empty
                                <tr><td colspan="5" class="text-center">No subsitution forms submitted yet</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
    (function () {
        var tableBody = document.querySelector('#substitutionRowsTable tbody');
        var addBtn = document.getElementById('addSubstitutionRow');

        function rowCount() {
            return tableBody.querySelectorAll('tr').length;
        }

        function buildRow(index) {
            return '<tr>'
                + '<td><input type="text" class="form-control form-control-sm" name="entries[' + index + '][subject]"></td>'
                + '<td><input type="text" class="form-control form-control-sm" name="entries[' + index + '][year_section]"></td>'
                + '<td><input type="date" class="form-control form-control-sm" name="entries[' + index + '][date]"></td>'
                + '<td><input type="text" class="form-control form-control-sm" name="entries[' + index + '][time]"></td>'
                + '<td><input type="text" class="form-control form-control-sm" name="entries[' + index + '][room]"></td>'
                + '<td><input type="text" class="form-control form-control-sm" name="entries[' + index + '][hours]"></td>'
                + '<td><button type="button" class="btn btn-sm btn-outline-danger remove-row">&times;</button></td>'
                + '</tr>';
        }

        function reindexRows() {
            var rows = tableBody.querySelectorAll('tr');
            rows.forEach(function (row, idx) {
                row.querySelectorAll('input').forEach(function (inp) {
                    inp.name = inp.name.replace(/entries\[\d+\]/, 'entries[' + idx + ']');
                });
            });
        }

        if (addBtn) {
            addBtn.addEventListener('click', function () {
                tableBody.insertAdjacentHTML('beforeend', buildRow(rowCount()));
            });
        }

        tableBody.addEventListener('click', function (e) {
            if (!e.target.classList.contains('remove-row')) return;
            var rows = tableBody.querySelectorAll('tr');
            if (rows.length <= 1) return;
            e.target.closest('tr').remove();
            reindexRows();
        });
    })();
</script>
@endsection
