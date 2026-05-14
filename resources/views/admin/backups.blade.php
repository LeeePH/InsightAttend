@extends('layouts.master')

@section('css')
    <style>
        .danger-zone {
            border: 1px solid #efc2c2;
            background: linear-gradient(180deg, #fff8f8 0%, #ffeaea 100%);
        }
        .danger-zone .header-title {
            color: #8d1d1d;
        }
        .danger-card {
            border: 1px solid #efcaca;
            border-radius: 14px;
            background: rgba(255, 255, 255, 0.9);
            padding: 1rem;
        }
        .danger-modal-note {
            border: 1px solid #f0d3d3;
            background: #fff6f6;
            border-radius: 12px;
            padding: 0.9rem 1rem;
        }
    </style>
@endsection

@section('breadcrumb')
    <div class="col-sm-6">
        <h4 class="page-title text-left">Admin Management</h4>
    </div>
@endsection

@section('content')
    @include('includes.flash')

    {{-- Danger zone success modal --}}
    @if(session('danger_zone_success'))
    <div class="modal fade" id="dangerZoneSuccessModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content text-center" style="border-radius:16px;overflow:hidden;">
                <div class="modal-body py-5 px-4">
                    <div style="font-size:3.5rem;line-height:1;margin-bottom:1rem;">
                        @if(session('danger_zone_success') === 'reset')
                            ✅
                        @else
                            🗑️
                        @endif
                    </div>
                    <h4 class="font-weight-bold mb-2">
                        @if(session('danger_zone_success') === 'reset')
                            Database Reset Successful
                        @else
                            Database Deleted
                        @endif
                    </h4>
                    <p class="text-muted mb-4">
                        @if(session('danger_zone_success') === 'reset')
                            All records have been cleared and the default admin account has been restored. You are now logged in as the default admin.
                        @else
                            All database tables have been removed. The system will not function until the database is restored or recreated.
                        @endif
                    </p>
                    <button type="button" class="btn btn-primary px-4" data-dismiss="modal" style="border-radius:999px;">
                        Got it
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <h4 class="mt-0 header-title mb-3">Backup tools</h4>
                    <div class="row">
                        <div class="col-md-6">
                            <form action="{{ route('admin.backups.create') }}" method="POST" class="mb-4">
                                @csrf
                                <div class="form-group">
                                    <label for="label">Backup Label (Optional)</label>
                                    <input type="text" name="label" id="label" class="form-control" placeholder="e.g. before-reset">
                                </div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fa fa-save"></i> Create Backup Now
                                </button>
                            </form>
                        </div>
                        <div class="col-md-6">
                            <form action="{{ route('admin.backups.upload_restore') }}" method="POST" enctype="multipart/form-data" class="mb-4">
                                @csrf
                                <div class="form-group">
                                    <label for="backup_file">Upload Backup File (.json)</label>
                                    <input type="file" name="backup_file" id="backup_file" class="form-control @error('backup_file') is-invalid @enderror" accept=".json,.txt" required>
                                    @error('backup_file')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <button type="submit" class="btn btn-warning">
                                    <i class="fa fa-upload"></i> Upload and Restore
                                </button>
                            </form>
                        </div>
                    </div>

                    <h4 class="mt-4 header-title mb-3">Available Backups</h4>
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered mb-0">
                            <thead>
                                <tr>
                                    <th>File</th>
                                    <th>Size</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($backups as $backup)
                                    <tr>
                                        <td>{{ $backup['name'] }}</td>
                                        <td>{{ number_format($backup['size'] / 1024, 2) }} KB</td>
                                        <td>{{ $backup['last_modified']->format('M d, Y h:i A') }}</td>
                                        <td class="text-nowrap">
                                            <a href="{{ route('admin.backups.download', ['backup' => $backup['name']]) }}" class="btn btn-info btn-sm">
                                                <i class="fa fa-download"></i> Download
                                            </a>
                                            <form action="{{ route('admin.backups.restore', ['backup' => $backup['name']]) }}" method="POST" style="display:inline;">
                                                @csrf
                                                <button type="submit" class="btn btn-secondary btn-sm">
                                                    <i class="fa fa-refresh"></i> Restore
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">No backup files found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card danger-zone">
                <div class="card-body">
                    <h4 class="mt-0 header-title mb-2">Danger Zone</h4>
                    <p class="text-muted">Create a backup first. These actions require database-name confirmation.</p>

                    <div class="danger-card mb-3">
                        <h5 class="mb-2">Reset database</h5>
                        <p class="text-muted mb-3">Clears current records, keeps the structure, and restores the default admin account.</p>
                        <button type="button" class="btn btn-warning btn-block" data-toggle="modal" data-target="#resetDatabaseModal">
                            <i class="fa fa-undo"></i> Reset Database
                        </button>
                    </div>

                    <div class="danger-card">
                        <h5 class="mb-2 text-danger">Delete database</h5>
                        <p class="text-muted mb-3">Deletes all database tables. The system will stop working until the database is restored or recreated.</p>
                        <button type="button" class="btn btn-danger btn-block" data-toggle="modal" data-target="#deleteDatabaseModal">
                            <i class="fa fa-trash"></i> Delete Database
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="resetDatabaseModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="{{ route('admin.backups.reset_database') }}">
                    @csrf
                    <div class="modal-header">
                        <h4 class="modal-title"><b>Reset Database</b></h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p class="mb-3">Are you sure you want to reset the database?</p>
                        <div class="danger-modal-note mb-3">
                            Enter the database name to continue. If the name is wrong, the action will stop and you must wait 5 minutes before trying again.
                        </div>
                        <div class="form-group mb-0">
                            <label for="reset_database_name">Database Name</label>
                            <input type="text" name="database_name" id="reset_database_name" class="form-control" required autocomplete="off">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-warning">Confirm Reset</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="deleteDatabaseModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="{{ route('admin.backups.delete_database') }}">
                    @csrf
                    <div class="modal-header">
                        <h4 class="modal-title"><b>Delete Database</b></h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p class="mb-3">Are you sure you want to delete the database?</p>
                        <div class="danger-modal-note mb-3">
                            Enter the database name to continue. If the name is wrong, the action will stop and you must wait 5 minutes before trying again.
                        </div>
                        <div class="form-group mb-0">
                            <label for="delete_database_name">Database Name</label>
                            <input type="text" name="database_name" id="delete_database_name" class="form-control" required autocomplete="off">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Confirm Delete</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@if(session('danger_zone_success'))
@section('script-bottom')
<script>
    $(document).ready(function () {
        $('#dangerZoneSuccessModal').modal({ backdrop: 'static', keyboard: false });
        $('#dangerZoneSuccessModal').modal('show');
    });
</script>
@endsection
@endif
