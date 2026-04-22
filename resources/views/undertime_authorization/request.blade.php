@extends('layouts.master')

@section('css')
    @include('includes.employee_request_form_theme', ['pageWrapperClass' => 'ut-page'])
@endsection

@section('content')
@include('includes.flash')
@php $today = \Carbon\Carbon::now()->toDateString(); @endphp

<div class="row ut-page">
    <div class="col-12 px-1">
        <div class="card">
            <div class="card-body">
                <h4 class="page-header-title"><i class="fa fa-clock-o mr-1"></i> Undertime Authorization Form</h4>
                <p class="page-subtitle">Submit undertime schedule and reason for approval.</p>

                <div class="employee-panel mt-4">
                    <h6><i class="fa fa-user mr-1"></i> Employee Information</h6>
                    <p><strong>Name:</strong> {{ $currentEmployee->name }}</p>
                    <p><strong>Department:</strong> {{ $currentEmployee->department ?? 'N/A' }}</p>
                    <p><strong>Position:</strong> {{ $currentEmployee->position ?? 'N/A' }}</p>
                </div>

                <form method="POST" action="{{ route('undertime_authorization.storeRequest') }}" id="undertimeAuthForm">
                    @csrf
                    <input type="hidden" name="date_filed" value="{{ $today }}">

                    <div class="table-responsive">
                        <table class="table table-bordered" id="utRowsTable">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Work From</th>
                                    <th>Work To</th>
                                    <th>UT From</th>
                                    <th>UT To</th>
                                    <th>Total UT Hours</th>
                                    <th>Reason for Undertime</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $oldEntries = old('entries', [['date' => '', 'work_from' => '', 'work_to' => '', 'ut_from' => '', 'ut_to' => '', 'total_hours' => '', 'reason' => '']]); @endphp
                                @foreach($oldEntries as $idx => $entry)
                                <tr>
                                    <td><input type="date" class="form-control form-control-sm" name="entries[{{ $idx }}][date]" value="{{ $entry['date'] ?? '' }}" required></td>
                                    <td><input type="time" class="form-control form-control-sm" name="entries[{{ $idx }}][work_from]" value="{{ $entry['work_from'] ?? '' }}" required></td>
                                    <td><input type="time" class="form-control form-control-sm" name="entries[{{ $idx }}][work_to]" value="{{ $entry['work_to'] ?? '' }}" required></td>
                                    <td><input type="time" class="form-control form-control-sm" name="entries[{{ $idx }}][ut_from]" value="{{ $entry['ut_from'] ?? '' }}" required></td>
                                    <td><input type="time" class="form-control form-control-sm" name="entries[{{ $idx }}][ut_to]" value="{{ $entry['ut_to'] ?? '' }}" required></td>
                                    <td><input type="number" step="0.25" min="0" max="24" class="form-control form-control-sm" name="entries[{{ $idx }}][total_hours]" value="{{ $entry['total_hours'] ?? '' }}" required></td>
                                    <td><input type="text" class="form-control form-control-sm" name="entries[{{ $idx }}][reason]" value="{{ $entry['reason'] ?? '' }}" required></td>
                                    <td><button type="button" class="btn btn-sm btn-outline-danger remove-row">&times;</button></td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <button type="button" class="btn theme-btn mb-3" id="addUtRow"><i class="fa fa-plus"></i> Add Row</button>

                    <div class="form-group mb-0">
                        <button type="submit" class="btn theme-btn"><i class="fa fa-paper-plane"></i> Submit Undertime Authorization</button>
                        <a href="{{ route('employee.dashboard') }}" class="btn theme-btn ml-2"><i class="fa fa-home"></i> Back to Dashboard</a>
                    </div>
                </form>

                <div class="mt-4">
                    <h5 class="page-header-title mb-3">My Undertime Authorization Requests</h5>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Date Filed</th>
                                    <th>Rows</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($history as $item)
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($item->date_filed)->format('M d, Y') }}</td>
                                    <td>{{ is_array($item->entries) ? count($item->entries) : 0 }}</td>
                                    <td>
                                        @if((int)$item->status === 0)<span class="theme-badge">Pending</span>
                                        @elseif((int)$item->status === 1)<span class="theme-badge">Approved</span>
                                        @else <span class="theme-badge">Rejected</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('undertime_authorization.show', $item->id) }}" class="btn theme-btn btn-sm"><i class="fa fa-eye"></i> View</a>
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="4" class="text-center">No undertime authorization forms submitted yet</td></tr>
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
        var tableBody = document.querySelector('#utRowsTable tbody');
        var addBtn = document.getElementById('addUtRow');

        function rowCount() {
            return tableBody.querySelectorAll('tr').length;
        }

        function buildRow(index) {
            return '<tr>'
                + '<td><input type="date" class="form-control form-control-sm" name="entries[' + index + '][date]" required></td>'
                + '<td><input type="time" class="form-control form-control-sm" name="entries[' + index + '][work_from]" required></td>'
                + '<td><input type="time" class="form-control form-control-sm" name="entries[' + index + '][work_to]" required></td>'
                + '<td><input type="time" class="form-control form-control-sm" name="entries[' + index + '][ut_from]" required></td>'
                + '<td><input type="time" class="form-control form-control-sm" name="entries[' + index + '][ut_to]" required></td>'
                + '<td><input type="number" step="0.25" min="0" max="24" class="form-control form-control-sm" name="entries[' + index + '][total_hours]" required></td>'
                + '<td><input type="text" class="form-control form-control-sm" name="entries[' + index + '][reason]" required></td>'
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
                var idx = rowCount();
                tableBody.insertAdjacentHTML('beforeend', buildRow(idx));
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
