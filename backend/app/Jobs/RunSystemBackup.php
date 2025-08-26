<?php

namespace App\Jobs;

use App\Models\SystemSetting;
use App\Models\User;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\Classes;
use App\Models\Subject;
use App\Models\Grade;
use App\Models\Attendance;
use App\Models\Term;
use App\Models\ReportCard;
use App\Models\AuditLog;
use App\Models\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class RunSystemBackup implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public ?int $initiatedByUserId;

    public function __construct(?int $initiatedByUserId = null)
    {
        $this->initiatedByUserId = $initiatedByUserId;
    }

    public function handle(): void
    {
        $ts = now();
        $name = 'backup_' . $ts->format('Ymd_His');
        $backupDir = storage_path('app/backups');
        @mkdir($backupDir, 0775, true);

        // Collect snapshot
        $settingsRows = SystemSetting::all();
        $settings = [];
        foreach ($settingsRows as $row) {
            $val = json_decode($row->setting_value, true);
            $settings[$row->setting_key] = $val === null ? $row->setting_value : $val;
        }

        $snapshot = [
            'generated_at' => $ts->toDateTimeString(),
            'app' => config('app.name'),
            'counts' => [
                'users' => User::count(),
                'students' => Student::count(),
                'teachers' => Teacher::count(),
                'classes' => Classes::count(),
                'subjects' => Subject::count(),
                'grades' => Grade::count(),
                'attendance' => Attendance::count(),
                'terms' => Term::count(),
                'report_cards' => ReportCard::count(),
            ],
            'settings' => $settings,
        ];

        $jsonPath = $backupDir . DIRECTORY_SEPARATOR . $name . '.json';
        file_put_contents($jsonPath, json_encode($snapshot, JSON_PRETTY_PRINT));

        $zipPath = $backupDir . DIRECTORY_SEPARATOR . $name . '.zip';
        $zipCreated = false;
        try {
            if (class_exists('ZipArchive')) {
                $zip = new \ZipArchive();
                if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === true) {
                    // Add snapshot
                    $zip->addFile($jsonPath, 'backup.json');
                    // Add reports if exist
                    $reportsDir = storage_path('app/public/reports');
                    if (is_dir($reportsDir)) {
                        $this->addDirToZip($zip, $reportsDir, 'reports');
                    }
                    $zip->close();
                    $zipCreated = true;
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Backup zip creation failed: ' . $e->getMessage());
        }

        // Audit log
        try {
            AuditLog::create([
                'user_id' => $this->initiatedByUserId,
                'action' => 'system_backup',
                'model_type' => 'system',
                'model_id' => null,
                'old_values' => null,
                'new_values' => [
                    'file' => $zipCreated ? basename($zipPath) : basename($jsonPath),
                    'path' => $zipCreated ? $zipPath : $jsonPath,
                    'zip' => $zipCreated,
                ],
                'ip_address' => null,
                'user_agent' => null,
            ]);
        } catch (\Throwable $e) {}

        // Notify initiator
        if ($this->initiatedByUserId) {
            try {
                $n = Notification::create([
                    'user_id' => $this->initiatedByUserId,
                    'title' => 'System backup completed',
                    'message' => 'Backup file: ' . ($zipCreated ? basename($zipPath) : basename($jsonPath)),
                    'type' => 'info',
                    'is_read' => false,
                    'sent_at' => now(),
                ]);
                event(new \App\Events\NotificationCreated($n));
            } catch (\Throwable $e) {}
        }

        // Remove loose JSON if we created a zip
        if ($zipCreated) {
            @unlink($jsonPath);
        }
    }

    private function addDirToZip(\ZipArchive $zip, string $dir, string $base): void
    {
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );
        foreach ($iterator as $file) {
            $path = (string)$file;
            $local = $base . '/' . ltrim(str_replace($dir, '', $path), '/\\');
            if (is_dir($path)) {
                $zip->addEmptyDir($local);
            } else {
                $zip->addFile($path, $local);
            }
        }
    }
}
