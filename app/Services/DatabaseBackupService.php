<?php

namespace App\Services;

use App\Models\Department;
use Database\Seeders\AdminUserSeeder;
use Database\Seeders\CourseSeeder;
use Database\Seeders\SubjectSeeder;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class DatabaseBackupService
{
    private const BACKUP_DIR = 'backups';
    private const FIXED_DEPARTMENTS = [
        'Student Services Department',
        'Admin Department',
        'Academic Department',
        'Finance Department',
        'Registrar Department',
        'HR department',
        'IT Department',
        'Education Department',
        'SHTM Department',
    ];

    public function createBackup(?string $label = null): array
    {
        $driver = DB::connection()->getDriverName();
        $tables = $this->getTableNames($driver);
        $data = [];

        foreach ($tables as $table) {
            $data[$table] = DB::table($table)->get()->map(function ($row) {
                return (array) $row;
            })->all();
        }

        $timestamp = now()->format('Ymd-His');
        $safeLabel = $label ? '-' . preg_replace('/[^A-Za-z0-9_-]/', '', $label) : '';
        $fileName = 'backup-' . $timestamp . $safeLabel . '.json';
        $relativePath = self::BACKUP_DIR . '/' . $fileName;

        $payload = [
            'meta' => [
                'generated_at' => Carbon::now()->toIso8601String(),
                'driver' => $driver,
                'table_count' => count($tables),
                'app' => config('app.name'),
            ],
            'tables' => $data,
        ];

        Storage::disk('local')->put(
            $relativePath,
            json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE)
        );

        return [
            'name' => $fileName,
            'path' => $relativePath,
            'size' => Storage::disk('local')->size($relativePath),
        ];
    }

    public function listBackups(): array
    {
        $files = collect(Storage::disk('local')->files(self::BACKUP_DIR))
            ->filter(function ($path) {
                return substr($path, -5) === '.json';
            })
            ->map(function ($path) {
                return [
                    'path' => $path,
                    'name' => basename($path),
                    'size' => Storage::disk('local')->size($path),
                    'last_modified' => Carbon::createFromTimestamp(Storage::disk('local')->lastModified($path)),
                ];
            })
            ->sortByDesc('last_modified')
            ->values()
            ->all();

        return $files;
    }

    public function pruneOldBackups(int $keep = 30): int
    {
        $keep = max(1, $keep);
        $files = $this->listBackups();

        if (count($files) <= $keep) {
            return 0;
        }

        $toDelete = array_slice($files, $keep);
        $deleted = 0;

        foreach ($toDelete as $file) {
            if (Storage::disk('local')->exists($file['path'])) {
                Storage::disk('local')->delete($file['path']);
                $deleted++;
            }
        }

        return $deleted;
    }

    public function restoreBackup(string $relativePath): void
    {
        if (!Storage::disk('local')->exists($relativePath)) {
            throw new RuntimeException('Backup file not found.');
        }

        $content = Storage::disk('local')->get($relativePath);
        $decoded = json_decode($content, true);

        if (!is_array($decoded) || !isset($decoded['tables']) || !is_array($decoded['tables'])) {
            throw new RuntimeException('Invalid backup format.');
        }

        $driver = DB::connection()->getDriverName();

        DB::transaction(function () use ($decoded, $driver) {
            $this->disableForeignKeys($driver);

            try {
                foreach ($decoded['tables'] as $table => $rows) {
                    if (!Schema::hasTable($table)) {
                        throw new RuntimeException('Table "' . $table . '" does not exist in current database.');
                    }

                    DB::table($table)->delete();

                    if (!is_array($rows) || empty($rows)) {
                        continue;
                    }

                    foreach (array_chunk($rows, 500) as $chunk) {
                        DB::table($table)->insert($chunk);
                    }
                }
            } finally {
                $this->enableForeignKeys($driver);
            }
        });
    }

    public function storeUploadedBackup(string $sourcePath, string $originalName): string
    {
        $safeName = preg_replace('/[^A-Za-z0-9_.-]/', '-', pathinfo($originalName, PATHINFO_FILENAME));
        $finalName = 'uploaded-' . now()->format('Ymd-His') . '-' . $safeName . '.json';
        $relativePath = self::BACKUP_DIR . '/' . $finalName;

        $content = file_get_contents($sourcePath);

        if ($content === false) {
            throw new RuntimeException('Could not read uploaded backup file.');
        }

        Storage::disk('local')->put($relativePath, $content);

        return $relativePath;
    }

    public function normalizeBackupPath(string $backupFileName): string
    {
        $safeName = basename($backupFileName);
        return self::BACKUP_DIR . '/' . $safeName;
    }

    public function getDatabaseStats(): array
    {
        $driver = DB::connection()->getDriverName();
        $tables = $this->getTableNames($driver);
        $rows = 0;

        foreach ($tables as $table) {
            try {
                $rows += (int) DB::table($table)->count();
            } catch (\Throwable $e) {
                // Ignore tables that cannot be counted cleanly.
            }
        }

        return [
            'database' => DB::connection()->getDatabaseName(),
            'driver' => $driver,
            'table_count' => count($tables),
            'row_count' => $rows,
            'backup_count' => count($this->listBackups()),
        ];
    }

    public function resetDatabase(): void
    {
        $driver = DB::connection()->getDriverName();
        $tables = array_values(array_filter(
            $this->getTableNames($driver),
            fn ($table) => $table !== 'migrations'
        ));

        // Ensure audit_logs is cleared last (after users) to avoid FK issues
        // when the middleware tries to log the reset action itself.
        $ordered = array_values(array_filter($tables, fn ($t) => $t !== 'audit_logs'));
        $ordered[] = 'audit_logs'; // clear audit_logs last, still inside FK-disabled block

        DB::transaction(function () use ($driver, $ordered) {
            $this->disableForeignKeys($driver);

            try {
                foreach ($ordered as $table) {
                    DB::table($table)->delete();
                }
            } finally {
                $this->enableForeignKeys($driver);
            }
        });

        Artisan::call('db:seed', ['--class' => AdminUserSeeder::class, '--force' => true]);
        Artisan::call('db:seed', ['--class' => CourseSeeder::class, '--force' => true]);
        Artisan::call('db:seed', ['--class' => SubjectSeeder::class, '--force' => true]);
        $this->restoreFixedDepartments();
    }

    public function deleteDatabase(): void
    {
        $driver = DB::connection()->getDriverName();
        $tables = array_reverse($this->getTableNames($driver));

        $this->disableForeignKeys($driver);

        try {
            foreach ($tables as $table) {
                Schema::dropIfExists($table);
            }
        } finally {
            $this->enableForeignKeys($driver);
        }
    }

    private function getTableNames(string $driver): array
    {
        switch ($driver) {
            case 'mysql':
                $database = DB::connection()->getDatabaseName();
                $tables = DB::select('SELECT TABLE_NAME FROM information_schema.tables WHERE table_schema = ? AND table_type = ?', [$database, 'BASE TABLE']);
                return array_map(function ($row) {
                    return $row->TABLE_NAME;
                }, $tables);
            case 'pgsql':
                $tables = DB::select("SELECT tablename FROM pg_tables WHERE schemaname='public'");
                return array_map(function ($row) {
                    return $row->tablename;
                }, $tables);
            case 'sqlite':
                $tables = DB::select("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'");
                return array_map(function ($row) {
                    return $row->name;
                }, $tables);
            case 'sqlsrv':
                $tables = DB::select("SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_TYPE = 'BASE TABLE'");
                return array_map(function ($row) {
                    return $row->TABLE_NAME;
                }, $tables);
            default:
                throw new RuntimeException('Unsupported database driver for backup: ' . $driver);
        }
    }

    private function disableForeignKeys(string $driver): void
    {
        switch ($driver) {
            case 'mysql':
                DB::statement('SET FOREIGN_KEY_CHECKS=0');
                break;
            case 'sqlite':
                DB::statement('PRAGMA foreign_keys=OFF');
                break;
            case 'pgsql':
                DB::statement("SET session_replication_role = 'replica'");
                break;
        }
    }

    private function enableForeignKeys(string $driver): void
    {
        switch ($driver) {
            case 'mysql':
                DB::statement('SET FOREIGN_KEY_CHECKS=1');
                break;
            case 'sqlite':
                DB::statement('PRAGMA foreign_keys=ON');
                break;
            case 'pgsql':
                DB::statement("SET session_replication_role = 'origin'");
                break;
        }
    }

    private function restoreFixedDepartments(): void
    {
        foreach (self::FIXED_DEPARTMENTS as $name) {
            Department::updateOrCreate(
                ['name' => $name],
                ['description' => null, 'is_active' => true]
            );
        }
    }
}
