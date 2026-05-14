@extends('layouts.master')

@php
    use Illuminate\Support\Str;

    $profileName = $employee?->name ?? $user->name;
    $profileEmail = $employee?->email ?? $user->email;
    $profilePhone = $employee?->phone;
    $profileDepartment = $employee?->department?->name ?? $employee?->department;
    $profilePosition = $employee?->position;
    $profileDateHired = $employee?->date_hired ?? $employee?->created_at;
    $profileType = $employee?->employment_type === 'part_time' ? 'Part-time' : ($employee?->employment_type === 'full_time' ? 'Full-time' : 'Not set');
    $profileSkills = collect(preg_split('/\r\n|\r|\n/', (string) ($employee?->skills ?? '')))->map(fn ($item) => trim($item))->filter();
    $profileAchievements = collect(preg_split('/\r\n|\r|\n/', (string) ($employee?->achievements ?? '')))->map(fn ($item) => trim($item))->filter();
    $avatarImage = $employee?->face_image;
    $avatarSrc = null;

    if ($avatarImage) {
        if (Str::startsWith($avatarImage, ['http://', 'https://', 'data:image', '/'])) {
            $avatarSrc = $avatarImage;
        } else {
            $avatarSrc = asset($avatarImage);
        }
    }

    $profileModalErrors = collect([
        'name',
        'email',
        'position',
        'phone',
        'date_hired',
        'employment_type',
        'skills',
        'achievements',
        'emergency_contact_name',
        'emergency_contact_relationship',
        'emergency_contact_phone',
        'profile_photo',
    ])->contains(fn ($field) => $errors->has($field));
@endphp

@section('css')
<style>
    :root {
        --profile-bg: #f8f1eb;
        --profile-card: #ffffff;
        --profile-text: #3e2412;
        --profile-muted: #7b5a45;
        --profile-border: #e2cdbd;
        --profile-accent: #8b4513;
        --profile-accent-soft: #f4e4d8;
    }

    body {
        background: var(--profile-bg);
    }

    .profile-page .card,
    .profile-page .detail-card,
    .profile-page .modal-content {
        border: 1px solid var(--profile-border);
        box-shadow: 0 12px 28px rgba(62, 36, 18, 0.08);
    }

    .profile-hero {
        border-radius: 18px;
        background: linear-gradient(135deg, #4c2d17 0%, #8b4513 100%);
        color: #fff;
        padding: 2rem;
    }

    .profile-avatar-wrap {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.85rem;
    }

    .profile-avatar {
        width: 110px;
        height: 110px;
        border-radius: 28px;
        object-fit: cover;
        background: rgba(255, 255, 255, 0.14);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2.2rem;
        font-weight: 700;
        color: #fff;
        overflow: hidden;
        border: 3px solid rgba(255, 255, 255, 0.22);
    }

    .profile-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .profile-edit-trigger {
        border-radius: 999px;
        padding: 0.55rem 1rem;
        font-weight: 600;
        border: none;
        color: white;
        background: #fff3ea;
        box-shadow: 0 10px 24px rgba(35, 18, 6, 0.16);
    }

    .profile-edit-trigger:hover,
    .profile-edit-trigger:focus {
        color: #2d1708;
        background: #ffffff;
    }

    .profile-hero-subtitle {
        color: rgba(255, 245, 236, 0.82);
    }

    .detail-card {
        background: var(--profile-card);
        border-radius: 16px;
        padding: 1.25rem;
        height: 100%;
    }

    .detail-label {
        display: block;
        font-size: 0.78rem;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: var(--profile-muted);
        margin-bottom: 0.3rem;
    }

    .detail-value {
        color: var(--profile-text);
        font-weight: 700;
        word-break: break-word;
    }

    .section-title {
        color: var(--profile-text);
        font-weight: 700;
        margin-bottom: 1rem;
    }

    .list-card ul {
        margin: 0;
        padding-left: 1.1rem;
        color: var(--profile-text);
    }

    .list-card li + li {
        margin-top: 0.45rem;
    }

    .profile-page .form-control,
    .profile-page textarea,
    .profile-page .custom-file-label,
    .profile-page .custom-file-label::after {
        border-color: var(--profile-border);
    }

    .profile-page .form-control:focus,
    .profile-page textarea:focus {
        border-color: rgba(139, 69, 19, 0.55);
        box-shadow: 0 0 0 3px rgba(139, 69, 19, 0.12);
    }

    .profile-modal .modal-header {
        background: #fff8f3;
        border-bottom: 1px solid var(--profile-border);
    }

    .profile-modal .modal-dialog {
        max-width: 920px;
    }

    .profile-modal .modal-body {
        max-height: 72vh;
        overflow-y: auto;
    }

    .profile-modal .modal-title {
        color: var(--profile-text);
        font-weight: 700;
    }

    .profile-modal .btn-primary {
        background: #8b4513;
        border-color: #8b4513;
        color: white;
    }

    .profile-modal .btn-primary:hover,
    .profile-modal .btn-primary:focus {
        background: #6f360e;
        border-color: #6f360e;
    }

    .profile-upload-help {
        color: var(--profile-muted);
        font-size: 0.83rem;
    }
</style>
@endsection

@section('breadcrumb')
<div class="col-sm-6 text-left">
    <h4 class="page-title">Profile</h4>
</div>
@endsection

@section('content')
@include('includes.flash')

<div class="profile-page">
    <div class="row">
        <div class="col-12">
            <div class="profile-hero mb-4">
                <div class="d-flex flex-column flex-md-row align-items-md-center">
                    <div class="profile-avatar-wrap mr-md-4 mb-3 mb-md-0">
                        <div class="profile-avatar">
                            @if ($avatarSrc)
                                <img src="{{ $avatarSrc }}" alt="{{ $profileName }}">
                            @else
                                {{ Str::upper(Str::substr($profileName, 0, 1)) }}
                            @endif
                        </div>
                        @if ($isOwnProfile && $isEmployeeProfile)
                            <button type="button" class="btn profile-edit-trigger" data-toggle="modal" data-target="#editProfileModal">
                                Edit Profile
                            </button>
                        @endif
                    </div>
                    <div>
                        <h2 class="mb-1">{{ $profileName }}</h2>
                        <div class="profile-hero-subtitle">{{ $profilePosition ?: 'Account user' }}</div>
                        <div class="profile-hero-subtitle">{{ $profileEmail }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if ($isEmployeeProfile)
        <div class="row">
            <div class="col-lg-4 mb-4">
                <div class="detail-card">
                    <h5 class="section-title">Employment Details</h5>
                    <div class="mb-3">
                        <span class="detail-label">Position</span>
                        <div class="detail-value">{{ $profilePosition ?: 'Not set' }}</div>
                    </div>
                    <div class="mb-3">
                        <span class="detail-label">Department</span>
                        <div class="detail-value">{{ $profileDepartment ?: 'Not set' }}</div>
                    </div>
                    <div class="mb-3">
                        <span class="detail-label">Date Hired</span>
                        <div class="detail-value">{{ $profileDateHired ? $profileDateHired->format('F d, Y') : 'Not set' }}</div>
                    </div>
                    <div>
                        <span class="detail-label">Employment Status</span>
                        <div class="detail-value">{{ $profileType }}</div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 mb-4">
                <div class="detail-card">
                    <h5 class="section-title">Contact Information</h5>
                    <div class="mb-3">
                        <span class="detail-label">Email</span>
                        <div class="detail-value">{{ $profileEmail ?: 'Not set' }}</div>
                    </div>
                    <div>
                        <span class="detail-label">Phone</span>
                        <div class="detail-value">{{ $profilePhone ?: 'Not set' }}</div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 mb-4">
                <div class="detail-card">
                    <h5 class="section-title">Emergency Contact</h5>
                    <div class="mb-3">
                        <span class="detail-label">Name</span>
                        <div class="detail-value">{{ $employee?->emergency_contact_name ?: 'Not set' }}</div>
                    </div>
                    <div class="mb-3">
                        <span class="detail-label">Relationship</span>
                        <div class="detail-value">{{ $employee?->emergency_contact_relationship ?: 'Not set' }}</div>
                    </div>
                    <div>
                        <span class="detail-label">Phone</span>
                        <div class="detail-value">{{ $employee?->emergency_contact_phone ?: 'Not set' }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-6 mb-4">
                <div class="detail-card list-card">
                    <h5 class="section-title">Skills & Expertise</h5>
                    @if ($profileSkills->count())
                        <ul>
                            @foreach ($profileSkills as $skill)
                                <li>{{ $skill }}</li>
                            @endforeach
                        </ul>
                    @else
                        <div class="detail-value">No skills added yet.</div>
                    @endif
                </div>
            </div>

            <div class="col-lg-6 mb-4">
                <div class="detail-card list-card">
                    <h5 class="section-title">Achievements</h5>
                    @if ($profileAchievements->count())
                        <ul>
                            @foreach ($profileAchievements as $achievement)
                                <li>{{ $achievement }}</li>
                            @endforeach
                        </ul>
                    @else
                        <div class="detail-value">No achievements added yet.</div>
                    @endif
                </div>
            </div>
        </div>

        @if ($isOwnProfile)
            <div class="row">
                <div class="col-lg-5 mb-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="section-title">Account Security</h5>
                            <form method="POST" action="{{ route('employee.settings.password') }}">
                                @csrf
                                <div class="form-group">
                                    <label for="current_password">Current Password</label>
                                    <input type="password" name="current_password" id="current_password" class="form-control @error('current_password') is-invalid @enderror" required>
                                    @error('current_password')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>
                                <div class="form-group">
                                    <label for="password">New Password</label>
                                    <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror" required>
                                    @error('password')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>
                                <div class="form-group">
                                    <label for="password_confirmation">Confirm Password</label>
                                    <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" required>
                                </div>
                                <button type="submit" class="btn btn-outline-secondary">Update Password</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal fade profile-modal" id="editProfileModal" tabindex="-1" role="dialog" aria-labelledby="editProfileModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
                    <div class="modal-content">
                        <form method="POST" action="{{ route('employee.settings.profile') }}" enctype="multipart/form-data">
                            @csrf
                            <div class="modal-header">
                                <h5 class="modal-title" id="editProfileModalLabel">Edit Profile</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label for="profile_name">Name</label>
                                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="profile_name" name="name" value="{{ old('name', $employee->name) }}" required>
                                        @error('name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label for="profile_email">Email</label>
                                        <input type="email" class="form-control @error('email') is-invalid @enderror" id="profile_email" name="email" value="{{ old('email', $employee->email) }}" required>
                                        @error('email')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label for="profile_position">Position</label>
                                        <input type="text" class="form-control @error('position') is-invalid @enderror" id="profile_position" name="position" value="{{ old('position', $employee->position) }}" required>
                                        @error('position')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label for="profile_phone">Phone</label>
                                        <input type="text" class="form-control @error('phone') is-invalid @enderror" id="profile_phone" name="phone" value="{{ old('phone', $employee->phone) }}">
                                        @error('phone')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label for="profile_date_hired">Date Hired</label>
                                        <input type="date" class="form-control @error('date_hired') is-invalid @enderror" id="profile_date_hired" name="date_hired" value="{{ old('date_hired', optional($employee->date_hired)->toDateString()) }}">
                                        @error('date_hired')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label for="profile_employment_type">Employment Status</label>
                                        <select class="form-control @error('employment_type') is-invalid @enderror" id="profile_employment_type" name="employment_type">
                                            <option value="">Select status</option>
                                            <option value="full_time" {{ old('employment_type', $employee->employment_type) === 'full_time' ? 'selected' : '' }}>Full-time</option>
                                            <option value="part_time" {{ old('employment_type', $employee->employment_type) === 'part_time' ? 'selected' : '' }}>Part-time</option>
                                        </select>
                                        @error('employment_type')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="profile_photo">Profile Picture</label>
                                    <input type="file" class="form-control-file @error('profile_photo') is-invalid @enderror" id="profile_photo" name="profile_photo" accept="image/*">
                                    <div class="profile-upload-help mt-2">Upload a new employee picture in JPG, PNG, or WEBP format. Max size: 5 MB.</div>
                                    @error('profile_photo')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>
                                <div class="form-group">
                                    <label for="profile_skills">Skills & Expertise</label>
                                    <textarea class="form-control @error('skills') is-invalid @enderror" id="profile_skills" name="skills" rows="4" placeholder="One skill per line">{{ old('skills', $employee->skills) }}</textarea>
                                    @error('skills')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>
                                <div class="form-group">
                                    <label for="profile_achievements">Achievements</label>
                                    <textarea class="form-control @error('achievements') is-invalid @enderror" id="profile_achievements" name="achievements" rows="4" placeholder="One achievement per line">{{ old('achievements', $employee->achievements) }}</textarea>
                                    @error('achievements')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>
                                <div class="form-row">
                                    <div class="form-group col-md-4">
                                        <label for="emergency_contact_name">Emergency Contact Name</label>
                                        <input type="text" class="form-control @error('emergency_contact_name') is-invalid @enderror" id="emergency_contact_name" name="emergency_contact_name" value="{{ old('emergency_contact_name', $employee->emergency_contact_name) }}">
                                        @error('emergency_contact_name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label for="emergency_contact_relationship">Relationship</label>
                                        <input type="text" class="form-control @error('emergency_contact_relationship') is-invalid @enderror" id="emergency_contact_relationship" name="emergency_contact_relationship" value="{{ old('emergency_contact_relationship', $employee->emergency_contact_relationship) }}">
                                        @error('emergency_contact_relationship')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label for="emergency_contact_phone">Phone</label>
                                        <input type="text" class="form-control @error('emergency_contact_phone') is-invalid @enderror" id="emergency_contact_phone" name="emergency_contact_phone" value="{{ old('emergency_contact_phone', $employee->emergency_contact_phone) }}">
                                        @error('emergency_contact_phone')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary">Save Profile</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endif
    @else
        <div class="row">
            <div class="col-lg-6 mb-4">
                <div class="detail-card">
                    <h5 class="section-title">Account Details</h5>
                    <div class="mb-3">
                        <span class="detail-label">Name</span>
                        <div class="detail-value">{{ $user->name }}</div>
                    </div>
                    <div class="mb-3">
                        <span class="detail-label">Email</span>
                        <div class="detail-value">{{ $user->email }}</div>
                    </div>
                    <div>
                        <span class="detail-label">Role</span>
                        <div class="detail-value">{{ optional($user->roles->first())->name ?? 'User' }}</div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection

@section('script')
@if ($profileModalErrors)
<script>
    $(function () {
        $('#editProfileModal').modal('show');
    });
</script>
@endif
@endsection
