<?php

namespace App\Console\Commands;

use App\Models\FormAssignment;
use App\Jobs\SendSurveyReminder;
use Illuminate\Console\Command;
use Carbon\Carbon;

class ProcessScheduledSurveys extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'surveys:process-scheduled';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process scheduled surveys and send reminders';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Processing scheduled surveys...');

        // 1. Activate scheduled surveys that are due
        $this->activateScheduledSurveys();

        // 2. Send reminders for surveys approaching due date
        $this->sendSurveyReminders();

        $this->info('Scheduled survey processing complete.');
    }

    /**
     * Activate surveys that are scheduled for today
     */
    protected function activateScheduledSurveys()
    {
        $now = Carbon::now();
        
        $scheduledAssignments = FormAssignment::where('is_active', false)
            ->whereNotNull('scheduled_date')
            ->where('scheduled_date', '<=', $now)
            ->get();

        foreach ($scheduledAssignments as $assignment) {
            $assignment->update(['is_active' => true]);
            $this->info("Activated survey assignment ID: {$assignment->id}");
        }

        $this->info("Activated {$scheduledAssignments->count()} scheduled surveys.");
    }

    /**
     * Send reminders for surveys approaching due date
     */
    protected function sendSurveyReminders()
    {
        $reminderAssignments = FormAssignment::where('is_active', true)
            ->where('auto_remind', true)
            ->whereNotNull('due_date')
            ->whereNotNull('remind_days_before')
            ->get();

        $remindersSent = 0;

        foreach ($reminderAssignments as $assignment) {
            $daysUntilDue = Carbon::now()->diffInDays($assignment->due_date, false);
            
            // Send reminder if we're at the specified number of days before due date
            if ($daysUntilDue == $assignment->remind_days_before) {
                SendSurveyReminder::dispatch($assignment->id);
                $remindersSent++;
                $this->info("Queued reminder for assignment ID: {$assignment->id}");
            }
        }

        $this->info("Queued {$remindersSent} survey reminders.");
    }
}
