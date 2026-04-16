<?php

namespace App\Console\Commands;

use App\Services\DatabaseBackupService;
use Illuminate\Console\Command;
use Throwable;

class RunDatabaseBackup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backup:run {--label=} {--keep=30}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a JSON database backup';

    public function handle(DatabaseBackupService $backupService): int
    {
        try {
            $backup = $backupService->createBackup($this->option('label'));
            $deleted = $backupService->pruneOldBackups((int) $this->option('keep'));
            $this->info('Backup created: ' . $backup['name']);
            if ($deleted > 0) {
                $this->info('Old backups removed: ' . $deleted);
            }
            return self::SUCCESS;
        } catch (Throwable $e) {
            $this->error('Backup failed: ' . $e->getMessage());
            return self::FAILURE;
        }
    }
}
