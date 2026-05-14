@extends('layouts.master')

@section('breadcrumb')
    <div class="col-sm-6 text-left">
        <h4 class="page-title">Class sections</h4>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="javascript:void(0);">Home</a></li>
            <li class="breadcrumb-item active">Class sections</li>
        </ol>
    </div>
@endsection

@section('content')
    @include('includes.flash')

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0 pl-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row mb-3">
        <div class="col-md-8">
            <p class="text-muted mb-0">Define faculty class sections (e.g. BSIT 1-1) by scheduling department and year level. These are used when assigning timetable entries.</p>
        </div>
        <div class="col-md-4 text-md-right">
            @if(auth()->user()->hasAnyRole(['admin','hr']))
                <form method="get" action="{{ route('class_sections.index') }}" class="form-inline d-inline-block mb-2">
                    <select name="department_key" class="form-control form-control-sm" onchange="this.form.submit()">
                        <option value="">All departments</option>
                        @foreach(\App\Services\SchedulingDepartmentService::KEYS as $k)
                            <option value="{{ $k }}" {{ request('department_key') === $k ? 'selected' : '' }}>{{ $departmentLabels[$k] ?? $k }}</option>
                        @endforeach
                    </select>
                </form>
            @endif
            <button type="button" class="btn btn-sm btn-primary mb-2" data-toggle="modal" data-target="#addSectionModal">Add section</button>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-bordered mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>Department</th>
                            <th>Year level</th>
                            <th>Section label</th>
                            <th style="width: 160px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($sections as $section)
                            <tr>
                                <td>{{ $departmentLabels[$section->department_key] ?? $section->department_key }}</td>
                                <td>{{ $section->year_level ? 'Year '.$section->year_level : '—' }}</td>
                                <td class="font-weight-bold">{{ $section->section_label }}</td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-success" data-toggle="modal" data-target="#editSectionModal{{ $section->id }}">Edit</button>
                                    <form action="{{ route('class_sections.destroy', $section) }}" method="post" class="d-inline" onsubmit="return confirm('Delete this section?');">
                                        @csrf
                                        @method('DELETE')
                                        <input type="hidden" name="return_department_key" value="{{ request('department_key') }}">
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">No sections yet. Add BSIT 1-1, BSED 2-2, etc.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade" id="addSectionModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form method="post" action="{{ route('class_sections.store') }}" id="addSectionForm">
                    @csrf
                    <input type="hidden" name="return_department_key" value="{{ request('department_key') }}">
                    <div class="modal-header">
                        <h5 class="modal-title">Add class section</h5>
                        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                    </div>
                    <div class="modal-body">
                        @if(auth()->user()->hasRole('secretary'))
                            @php $sk = strtoupper((string) auth()->user()->managed_schedule_department); @endphp
                            <input type="hidden" name="department_key" value="{{ $sk }}">
                        @else
                            <input type="hidden" name="department_key" id="add_department_key" value="{{ request('department_key', \App\Services\SchedulingDepartmentService::KEYS[0]) }}">
                        @endif

                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label>Course <span class="text-danger">*</span></label>
                                <select id="add_course_code" class="form-control section-builder" required>
                                    <option value="">— Select —</option>
                                    @foreach($courses as $course)
                                        <option value="{{ $course->code }}">{{ $course->code }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group col-md-4">
                                <label>Year <span class="text-danger">*</span></label>
                                <select id="add_year_level" name="year_level" class="form-control section-builder" required>
                                    <option value="">— Select —</option>
                                    <option value="1">1st Year</option>
                                    <option value="2">2nd Year</option>
                                    <option value="3">3rd Year</option>
                                    <option value="4">4th Year</option>
                                </select>
                            </div>
                            <div class="form-group col-md-4">
                                <label>Section <span class="text-danger">*</span></label>
                                <select id="add_section_num" class="form-control section-builder" required>
                                    <option value="">— Select —</option>
                                    @for($s = 1; $s <= 50; $s++)
                                        <option value="{{ $s }}">{{ $s }}</option>
                                    @endfor
                                </select>
                            </div>
                        </div>

                        <div class="form-group mb-0">
                            <label>Generated section label</label>
                            <div class="input-group">
                                <input type="text" id="add_section_label_preview"
                                       name="section_label"
                                       class="form-control font-weight-bold"
                                       placeholder="Select course, year and section above"
                                       required
                                       maxlength="64">
                                <div class="input-group-append">
                                    <span class="input-group-text"><i class="fas fa-magic"></i></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @foreach ($sections as $section)
        <div class="modal fade" id="editSectionModal{{ $section->id }}" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <form method="post" action="{{ route('class_sections.update', $section) }}"
                          class="edit-section-form" data-section-id="{{ $section->id }}">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="return_department_key" value="{{ request('department_key') }}">
                        <div class="modal-header">
                            <h5 class="modal-title">Edit section</h5>
                            <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" name="department_key" value="{{ $section->department_key }}">

                            @php
                                /* Try to parse existing label back into parts: CODE YEAR-SECTION */
                                $parsedCode = '';
                                $parsedYear = $section->year_level ?? '';
                                $parsedSec  = '';
                                if (preg_match('/^([A-Z]+)\s+(\d+)-(\d+)$/i', $section->section_label, $m)) {
                                    $parsedCode = strtoupper($m[1]);
                                    $parsedYear = (int) $m[2];
                                    $parsedSec  = (int) $m[3];
                                }
                            @endphp

                            <div class="form-row">
                                <div class="form-group col-md-4">
                                    <label>Course <span class="text-danger">*</span></label>
                                    <select class="form-control edit-section-builder"
                                            data-field="course"
                                            data-sid="{{ $section->id }}"
                                            required>
                                        <option value="">— Select —</option>
                                        @foreach($courses as $course)
                                            <option value="{{ $course->code }}"
                                                {{ $parsedCode === $course->code ? 'selected' : '' }}>
                                                {{ $course->code }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group col-md-4">
                                    <label>Year <span class="text-danger">*</span></label>
                                    <select name="year_level"
                                            class="form-control edit-section-builder"
                                            data-field="year"
                                            data-sid="{{ $section->id }}"
                                            required>
                                        <option value="">— Select —</option>
                                        <option value="1" {{ $parsedYear == 1 ? 'selected' : '' }}>1st Year</option>
                                        <option value="2" {{ $parsedYear == 2 ? 'selected' : '' }}>2nd Year</option>
                                        <option value="3" {{ $parsedYear == 3 ? 'selected' : '' }}>3rd Year</option>
                                        <option value="4" {{ $parsedYear == 4 ? 'selected' : '' }}>4th Year</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-4">
                                    <label>Section <span class="text-danger">*</span></label>
                                    <select class="form-control edit-section-builder"
                                            data-field="section"
                                            data-sid="{{ $section->id }}"
                                            required>
                                        <option value="">— Select —</option>
                                        @for($s = 1; $s <= 50; $s++)
                                            <option value="{{ $s }}" {{ $parsedSec == $s ? 'selected' : '' }}>{{ $s }}</option>
                                        @endfor
                                    </select>
                                </div>
                            </div>

                            <div class="form-group mb-0">
                                <label>Generated section label</label>
                                <div class="input-group">
                                    <input type="text"
                                           id="edit_section_label_{{ $section->id }}"
                                           name="section_label"
                                           class="form-control font-weight-bold"
                                           value="{{ $section->section_label }}"
                                           required
                                           maxlength="64">
                                    <div class="input-group-append">
                                        <span class="input-group-text"><i class="fas fa-magic"></i></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-success">Update</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endforeach
@endsection

@section('script-bottom')
<script>
(function () {
    /* ── Add modal ── */
    function buildAddLabel() {
        var code    = document.getElementById('add_course_code').value;
        var year    = document.getElementById('add_year_level').value;
        var section = document.getElementById('add_section_num').value;
        var preview = document.getElementById('add_section_label_preview');

        if (code && year && section) {
            preview.value = code + ' ' + year + '-' + section;
        } else {
            preview.value = '';
        }
    }

    ['add_course_code', 'add_year_level', 'add_section_num'].forEach(function (id) {
        var el = document.getElementById(id);
        if (el) el.addEventListener('change', buildAddLabel);
    });

    /* ── Edit modals ── */
    document.querySelectorAll('.edit-section-builder').forEach(function (sel) {
        sel.addEventListener('change', function () {
            var sid     = this.dataset.sid;
            var form    = document.querySelector('.edit-section-form[data-section-id="' + sid + '"]');
            if (!form) return;

            var courseEl  = form.querySelector('[data-field="course"]');
            var yearEl    = form.querySelector('[data-field="year"]');
            var sectionEl = form.querySelector('[data-field="section"]');
            var labelEl   = document.getElementById('edit_section_label_' + sid);

            var code    = courseEl  ? courseEl.value  : '';
            var year    = yearEl    ? yearEl.value    : '';
            var section = sectionEl ? sectionEl.value : '';

            if (code && year && section && labelEl) {
                labelEl.value = code + ' ' + year + '-' + section;
            }
        });
    });
})();
</script>
@endsection
