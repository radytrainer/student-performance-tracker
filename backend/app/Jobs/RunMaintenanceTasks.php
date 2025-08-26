<?php

namespace App\Jobs;

use App\Models\AuditLog;
use App\Models\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class RunMaintenanceTasks implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public ?int $initiatedByUserId;

    public function __construct(?int $initiatedByUserId = null)
    {
        $this->initiatedByUserId = $initiatedByUserId;
    }

    public function handle(): void
    {
        $results = [];
        try {
            $results['optimize:clear'] = Artisan::call('optimize:clear');
        } catch (\Throwable $e) { Log::warning('optimize:clear failed: '.$e->getMessage()); }

        foreach (['cache:clear', 'config:clear', 'route:clear', 'view:clear'] as $cmd) {
            try { $results[$cmd] = Artisan::call($cmd); } catch (\Throwable $e) {
                Log::warning($cmd.' failed: '.$e->getMessage());
            }
        }
        // Queue restart is best-effort
        try { $results['queue:restart'] = Artisan::call('queue:restart'); } catch (\Throwable $e) {}

        // Audit log
        try {
            AuditLog::create([
                'user_id' => $this->initiatedByUserId,
                'action' => 'maintenance_run',
                'model_type' => 'system',
                'model_id' => null,
                'old_values' => null,
                'new_values' => ['commands' => array_keys($results)],
                'ip_address' => null,
                'user_agent' => null,
            ]);
        } catch (\Throwable $e) {}

        // Notify initiator
        if ($this->initiatedByUserId) {
            try {
                $n = Notification::create([
                    'user_id' => $this->initiatedByUserId,
                    'title' => 'Maintenance completed',
                    'message' => 'System maintenance tasks finished successfully.',
                    'type' => 'success',
                    'is_read' => false,
                    'sent_at' => now(),
                ]);
                event(new \App\Events\NotificationCreated($n));
            } catch (\Throwable $e) {}
        }
    }
}
