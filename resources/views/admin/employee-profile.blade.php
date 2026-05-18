@extends('layouts.master')

@php
    $skills = collect(preg_split('/\r\n|\r|\n/', (string) ($employee->educational_background ?? '')))->map(fn ($item) => trim($item))->filter();
    $achievements = collect(preg_split('/\r\n|\r|\n/', (string) ($employee->work_experience ?? '')))->map(fn ($item) => trim($item))->filter();
    $employmentType = $employee->employment_type === 'part_time' ? 'Part-time' : ($employee->employment_type === 'full_time' ? 'Full-time' : 'Not set');
@endphp

@section('css')
<style>
    body {
        background: #f8f1eb;
    }

    .employee-profile-page .card,
    .employee-profile-detail {
        border: 1px solid #e2cdbd;
        box-shadow: 0 12px 28px rgba(62, 36, 18, 0.08);
    }

    .employee-profile-hero {
        border-radius: 18px;
        background: linear-gradient(135deg, #4c2d17 0%, #8B4513 100%);
        color: #fff;
        padding: 2rem;
    }

    .employee-profile-avatar {
        width: 92px;
        height: 92px;
        border-radius: 24px;
        overflow: hidden;
        background: rgba(255, 255, 255, 0.14);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
        font-weight: 700;
    }

    .employee-profile-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .employee-profile-detail {
        background: #fff;
        border-radius: 16px;
        padding: 1.2rem;
        height: 100%;
    }

    .employee-profile-label {
        display: block;
        font-size: 0.78rem;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: #7b5a45;
        margin-bottom: 0.3rem;
    }

    .employee-profile-value {
        color: #3e2412;
        font-weight: 700;
        word-break: break-word;
    }

    .employee-profile-list {
        margin: 0;
        padding-left: 1.15rem;
        color: #3e2412;
    }

    .employee-profile-list li + li {
        margin-top: 0.45rem;
    }
</style>
@endsection

@section('breadcrumb')
<div class="col-sm-6 text-left">
    <h4 class="page-title">Employee Profile</h4>
</div>
@endsection

@section('content')
<div class="employee-profile-page">
    <div class="row">
        <div class="col-12">
            <div class="employee-profile-hero mb-4">
                <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between">
                    <div class="d-flex flex-column flex-md-row align-items-md-center">
                        <div class="employee-profile-avatar mr-md-4 mb-3 mb-md-0">
                            @if ($employee->face_image)
                                <img src="{{ $employee->face_image }}" alt="{{ $employee->name }}">
                            @else
                                {{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($employee->name, 0, 1)) }}
                            @endif
                        </div>
                        <div>
                            <h2 class="mb-1">{{ $employee->name }}</h2>
                            <div>{{ $employee->position ?: 'No position set' }}</div>
                            <div class="text-light">{{ $employee->email ?: 'No email saved' }}</div>
                        </div>
                    </div>
                    <div class="mt-3 mt-md-0">
                        <a href="{{ route('employees.index') }}" class="btn btn-light btn-sm">Back to Employees</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-4 mb-4">
            <div class="employee-profile-detail">
                <span class="employee-profile-label">Position</span>
                <div class="employee-profile-value mb-3">{{ $employee->position ?: 'Not set' }}</div>

                <span class="employee-profile-label">Department</span>
                <div class="employee-profile-value mb-3">{{ $employee->department?->name ?? 'Not set' }}</div>

                <span class="employee-profile-label">Date Hired</span>
                <div class="employee-profile-value mb-3">{{ $employee->date_hired ? $employee->date_hired->format('F d, Y') : 'Not set' }}</div>

                <span class="employee-profile-label">Employment Status</span>
                <div class="employee-profile-value">{{ $employmentType }}</div>
            </div>
        </div>

        <div class="col-lg-4 mb-4">
            <div class="employee-profile-detail">
                <span class="employee-profile-label">Email</span>
                <div class="employee-profile-value mb-3">{{ $employee->email ?: 'Not set' }}</div>

                <span class="employee-profile-label">Phone</span>
                <div class="employee-profile-value mb-3">{{ $employee->phone ?: 'Not set' }}</div>

                <span class="employee-profile-label">Schedule</span>
                <div class="employee-profile-value">
                    @if ($schedule)
                        {{ $schedule->slug }}
                    @else
                        Not assigned
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-4 mb-4">
            <div class="employee-profile-detail">
                <span class="employee-profile-label">Emergency Contact Name</span>
                <div class="employee-profile-value mb-3">{{ $employee->emergency_contact_name ?: 'Not set' }}</div>

                <span class="employee-profile-label">Relationship</span>
                <div class="employee-profile-value mb-3">{{ $employee->emergency_contact_relationship ?: 'Not set' }}</div>

                <span class="employee-profile-label">Phone</span>
                <div class="employee-profile-value">{{ $employee->emergency_contact_phone ?: 'Not set' }}</div>
            </div>
        </div>

        <div class="col-lg-6 mb-4">
            <div class="employee-profile-detail">
                <span class="employee-profile-label">Educational Background</span>
                @if ($skills->count())
                    <ul class="employee-profile-list">
                        @foreach ($skills as $skill)
                            <li>{{ $skill }}</li>
                        @endforeach
                    </ul>
                @else
                    <div class="employee-profile-value">No educational background added yet.</div>
                @endif
            </div>
        </div>

        <div class="col-lg-6 mb-4">
            <div class="employee-profile-detail">
                <span class="employee-profile-label">Work Experience</span>
                @if ($achievements->count())
                    <ul class="employee-profile-list">
                        @foreach ($achievements as $achievement)
                            <li>{{ $achievement }}</li>
                        @endforeach
                    </ul>
                @else
                    <div class="employee-profile-value">No work experience added yet.</div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
