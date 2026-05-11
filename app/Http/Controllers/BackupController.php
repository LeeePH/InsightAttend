<?php

namespace App\Http\Controllers;

use App\Services\DatabaseBackupService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Throwable;

class BackupController extends Controller
{
    private $backupService;

    public function __construct(DatabaseBackupService $backupService)
    {
        $this->backupService = $backupService;
    }

    public function index(): View
    {
        $backups = $this->backupService->listBackups();
        $stats = $this->backupService->getDatabaseStats();

        return view('admin.backups', compact('backups', 'stats'));
    }

    public function create(Request $request): RedirectResponse
    {
        try {
            $label = $request->input('label');
            $backup = $this->backupService->createBackup($label);
            $this->backupService->pruneOldBackups(30);
            flash()->success('Success', 'Backup created successfully: ' . $backup['name']);
            return back();
        } catch (Throwable $e) {
            flash()->error('Error', 'Backup creation failed: ' . $e->getMessage());
            return back();
        }
    }

    public function download(string $backup)
    {
        $path = $this->backupService->normalizeBackupPath($backup);

        if (!Storage::disk('local')->exists($path)) {
            flash()->error('Error', 'Backup file not found.');
            return redirect()->route('admin.backups');
        }

        return Storage::disk('local')->download($path, basename($path));
    }

    public function restore(string $backup): RedirectResponse
    {
        try {
            $path = $this->backupService->normalizeBackupPath($backup);
            $this->backupService->restoreBackup($path);
            flash()->success('Success', 'Backup restored successfully from ' . basename($path));
            return back();
        } catch (Throwable $e) {
            flash()->error('Error', 'Restore failed: ' . $e->getMessage());
            return back();
        }
    }

    public function uploadAndRestore(Request $request): RedirectResponse
    {
        $request->validate([
            'backup_file' => ['required', 'file', 'mimes:json,txt', 'max:51200'],
        ]);

        try {
            $uploadedFile = $request->file('backup_file');
            $storedPath = $this->backupService->storeUploadedBackup(
                $uploadedFile->getRealPath(),
                $uploadedFile->getClientOriginalName()
            );

            $this->backupService->restoreBackup($storedPath);
            flash()->success('Success', 'Uploaded backup restored successfully.');
            return back();
        } catch (Throwable $e) {
            flash()->error('Error', 'Upload restore failed: ' . $e->getMessage());
            return back();
        }
    }

    public function resetDatabase(): RedirectResponse
    {
        $lockResponse = $this->validateDangerZoneRequest(request(), 'reset');
        if ($lockResponse) {
            return $lockResponse;
        }

        try {
            $this->backupService->resetDatabase();
            flash()->success('Success', 'Database reset completed. Default admin access was restored.');
            return back();
        } catch (Throwable $e) {
            flash()->error('Error', 'Database reset failed: ' . $e->getMessage());
            return back();
        }
    }

    public function deleteDatabase(): RedirectResponse
    {
        $lockResponse = $this->validateDangerZoneRequest(request(), 'delete');
        if ($lockResponse) {
            return $lockResponse;
        }

        try {
            $this->backupService->deleteDatabase();
            flash()->success('Success', 'Database tables were deleted.');
            return back();
        } catch (Throwable $e) {
            flash()->error('Error', 'Database delete failed: ' . $e->getMessage());
            return back();
        }
    }

    private function validateDangerZoneRequest(Request $request, string $action): ?RedirectResponse
    {
        $lockKey = 'danger-zone-lock:' . auth()->id() . ':' . $action;
        $lockedUntil = Cache::get($lockKey);

        if ($lockedUntil && now()->lessThan($lockedUntil)) {
            $minutesLeft = max(1, now()->diffInMinutes($lockedUntil));
            flash()->error('Error', 'This action is locked. Please wait ' . $minutesLeft . ' minute(s) before trying again.');
            return back();
        }

        $validated = $request->validate([
            'database_name' => ['required', 'string', 'max:255'],
        ]);

        $actualDatabase = (string) $this->backupService->getDatabaseStats()['database'];
        if ($validated['database_name'] !== $actualDatabase) {
            $until = now()->addMinutes(5);
            Cache::put($lockKey, $until, $until);
            flash()->error('Error', 'Wrong database name. This action is now locked for 5 minutes.');
            return back();
        }

        Cache::forget($lockKey);

        return null;
    }
}
