@extends('layouts.master')

@section('css')
    <style>
        .course-table td,
        .course-table th {
            vertical-align: middle;
        }
        .course-code {
            display: inline-block;
            min-width: 88px;
            padding: 0.35rem 0.65rem;
            border-radius: 999px;
            background: #f3e4d7;
            color: #6f330d;
            font-weight: 700;
            letter-spacing: 0.04em;
            text-align: center;
        }
    </style>
@endsection

@section('breadcrumb')
    <div class="col-sm-6 text-left">
        <h4 class="page-title">Maintenance Form</h4>
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

    <div class="row">
        <div class="col-12 mb-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="mb-3" style="color:#3e2412;font-weight:700;">School Settings</h5>
                    <form method="POST" action="{{ route('admin.maintenance_form.timetable_settings.update') }}">
                        @csrf
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>School Name</label>
                                <input type="text" name="school_name" class="form-control" value="{{ old('school_name', $timetableSettings['school_name']) }}" required maxlength="255">
                            </div>
                            <div class="form-group col-md-3">
                                <label>Semester</label>
                                <select name="semester_label" class="form-control" required>
                                    @foreach (['1st Semester', '2nd Semester'] as $semesterOption)
                                        <option value="{{ $semesterOption }}" {{ old('semester_label', $timetableSettings['semester_label']) === $semesterOption ? 'selected' : '' }}>
                                            {{ $semesterOption }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group col-md-3">
                                <label>School Year</label>
                                <select name="school_year" class="form-control" required>
                                    @foreach ($schoolYearOptions as $schoolYearOption)
                                        <option value="{{ $schoolYearOption }}" {{ old('school_year', $timetableSettings['school_year']) === $schoolYearOption ? 'selected' : '' }}>
                                            {{ $schoolYearOption }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="form-group mb-3">
                            <label>School Address</label>
                            <textarea name="school_address" class="form-control" rows="2" readonly>{{ $timetableSettings['school_address'] }}</textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Save School Settings</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0" style="color:#3e2412;font-weight:700;">Course Management</h5>
                        <a href="#addCourseModal" data-toggle="modal" class="btn btn-primary btn-sm btn-flat">
                            Add Course
                        </a>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-striped table-bordered course-table mb-0">
                            <thead>
                                <tr>
                                    <th style="width: 140px;">Course Code</th>
                                    <th>Course Name</th>
                                    <th style="width: 170px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($courses as $course)
                                    <tr>
                                        <td><span class="course-code">{{ $course->code }}</span></td>
                                        <td class="font-weight-bold">{{ $course->name }}</td>
                                        <td class="text-nowrap">
                                            <a href="#editCourseModal{{ $course->id }}" data-toggle="modal" class="btn btn-success btn-sm btn-flat">
                                                <i class="fa fa-edit"></i> Edit
                                            </a>
                                            <a href="#deleteCourseModal{{ $course->id }}" data-toggle="modal" class="btn btn-danger btn-sm btn-flat">
                                                <i class="fa fa-trash"></i> Delete
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center text-muted py-4">No courses added yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="addCourseModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title"><b>Add Course</b></h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form method="POST" action="{{ route('admin.maintenance_form.courses.store') }}">
                    @csrf
                    <div class="modal-body">
                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label>Course Code</label>
                                <input type="text" name="code" class="form-control" placeholder="BSIT" required maxlength="30">
                            </div>
                            <div class="form-group col-md-8">
                                <label>Course Name</label>
                                <input type="text" name="name" class="form-control" placeholder="Bachelor of Science in Information Technology" required maxlength="150">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save Course</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @foreach($courses as $course)
        <div class="modal fade" id="editCourseModal{{ $course->id }}" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title"><b>Edit Course</b></h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form method="POST" action="{{ route('admin.maintenance_form.courses.update', $course) }}">
                        @csrf
                        @method('PUT')
                        <div class="modal-body">
                            <div class="form-row">
                                <div class="form-group col-md-4">
                                    <label>Course Code</label>
                                    <input type="text" name="code" class="form-control" value="{{ $course->code }}" required maxlength="30">
                                </div>
                                <div class="form-group col-md-8">
                                    <label>Course Name</label>
                                    <input type="text" name="name" class="form-control" value="{{ $course->name }}" required maxlength="150">
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-success">Update Course</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="modal fade" id="deleteCourseModal{{ $course->id }}" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title"><b>Delete Course</b></h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form method="POST" action="{{ route('admin.maintenance_form.courses.destroy', $course) }}">
                        @csrf
                        @method('DELETE')
                        <div class="modal-body">
                            <p class="mb-2">Are you sure you want to delete this course?</p>
                            <div class="font-weight-bold">{{ $course->code }} - {{ $course->name }}</div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-danger">Delete Course</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endforeach
@endsection
