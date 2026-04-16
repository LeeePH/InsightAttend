@extends('layouts.master')

@section('breadcrumb')
    <div class="col-sm-6">
        <h4 class="page-title text-left">Backups</h4>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="javascript:void(0);">Home</a></li>
            <li class="breadcrumb-item"><a href="javascript:void(0);">Backups</a></li>
        </ol>
    </div>
@endsection

@section('content')
    @include('includes.flash')

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <form action="{{ route('admin.backups.create') }}" method="POST" class="mb-4">
                                @csrf
                                <div class="form-group">
                                    <label for="label">Backup Label (Optional)</label>
                                    <input type="text" name="label" id="label" class="form-control" placeholder="e.g. pre-payroll">
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
                                <button type="submit" class="btn btn-danger" onclick="return confirm('Restoring will replace current data. Continue?')">
                                    <i class="fa fa-upload"></i> Upload & Restore
                                </button>
                            </form>
                        </div>
                    </div>

                    <h4 class="mt-0 header-title mb-3">Available Backups</h4>
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
                                        <td>
                                            <a href="{{ route('admin.backups.download', ['backup' => $backup['name']]) }}" class="btn btn-info btn-sm">
                                                <i class="fa fa-download"></i> Download
                                            </a>
                                            <form action="{{ route('admin.backups.restore', ['backup' => $backup['name']]) }}" method="POST" style="display:inline;">
                                                @csrf
                                                <button type="submit" class="btn btn-warning btn-sm" onclick="return confirm('Restoring this backup will replace current data. Continue?')">
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
    </div>
@endsection
