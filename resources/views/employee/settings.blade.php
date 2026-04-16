@extends('layouts.master')

@section('breadcrumb')
    <div class="col-sm-6 text-left">
        <h4 class="page-title">Settings</h4>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('employee.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Settings</li>
        </ol>
    </div>
@endsection

@section('content')
    @include('includes.flash')

    <div class="row">
        <div class="col-lg-7">
            <div class="card border shadow-sm">
                <div class="card-body">
                    <h5 class="card-title mb-3">Personal information</h5>

                    <form method="POST" action="{{ route('employee.settings.profile') }}" autocomplete="off">
                        @csrf

                        <div class="form-group">
                            <label for="name">Full name</label>
                            <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name', $employee->name) }}" required maxlength="128">
                            @error('name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>

                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" class="form-control @error('email') is-invalid @enderror"
                                   value="{{ old('email', $employee->email) }}" required maxlength="255">
                            @error('email')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>

                        <div class="form-group">
                            <label for="department">Department</label>
                            <select id="department" name="department" class="form-control @error('department') is-invalid @enderror" required>
                                <option value="" disabled {{ old('department', $employee->department) ? '' : 'selected' }}>— Select —</option>
                                @foreach ($departmentOptions as $opt)
                                    <option value="{{ $opt }}" {{ old('department', $employee->department) === $opt ? 'selected' : '' }}>{{ $opt }}</option>
                                @endforeach
                            </select>
                            @error('department')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>

                        <div class="form-group">
                            <label for="position">Position</label>
                            <input type="text" id="position" name="position" class="form-control @error('position') is-invalid @enderror"
                                   value="{{ old('position', $employee->position) }}" required maxlength="128">
                            @error('position')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>

                        <button type="submit" class="btn btn-primary">Save changes</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card border shadow-sm">
                <div class="card-body">
                    <h5 class="card-title mb-3">Change password</h5>
                    <form method="POST" action="{{ route('employee.settings.password') }}" autocomplete="off">
                        @csrf
                        <div class="form-group">
                            <label for="current_password">Current password</label>
                            <input type="password" name="current_password" id="current_password"
                                   class="form-control @error('current_password') is-invalid @enderror" required autocomplete="current-password">
                            @error('current_password')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-group">
                            <label for="password">New password</label>
                            <input type="password" name="password" id="password"
                                   class="form-control @error('password') is-invalid @enderror" required minlength="8" autocomplete="new-password">
                            @error('password')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-group">
                            <label for="password_confirmation">Confirm new password</label>
                            <input type="password" name="password_confirmation" id="password_confirmation"
                                   class="form-control" required minlength="8" autocomplete="new-password">
                        </div>
                        <button type="submit" class="btn btn-secondary">Update password</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

