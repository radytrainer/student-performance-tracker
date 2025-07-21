<?php

namespace App\Jobs;

use App\Models\FormAssignment;
use App\Models\StudentClass;
use App\Models\User;
use App\Models\FormResponse;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use App\Mail\SurveyReminderMail;

class SendSurveyReminder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $assignmentId;

    /**
     * Create a new job instance.
     */
    public function __construct($assignmentId)
    {
        $this->assignmentId = $assignmentId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $assignment = FormAssignment::with(['feedbackForm', 'assignedClass'])
            ->find($this->assignmentId);

        if (!$assignment || !$assignment->auto_remind) {
            return;
        }

        // Get students in the assigned class
        $studentIds = StudentClass::where('class_id', $assignment->class_id)
            ->where('status', 'active')
            ->pluck('student_id');

        // Get students who haven't completed the survey
        $completedStudentIds = FormResponse::where('form_assignment_id', $assignment->id)
            ->pluck('student_id');

        $pendingStudentIds = $studentIds->diff($completedStudentIds);

        // Get user details for pending students
        $pendingStudents = User::whereIn('id', $pendingStudentIds)
            ->where('is_active', true)
            ->get();

        foreach ($pendingStudents as $student) {
            try {
                Mail::to($student->email)
                    ->send(new SurveyReminderMail($student, $assignment));
            } catch (\Exception $e) {
                \Log::error('Failed to send survey reminder', [
                    'student_id' => $student->id,
                    'assignment_id' => $assignment->id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        \Log::info('Survey reminders sent', [
            'assignment_id' => $assignment->id,
            'students_notified' => $pendingStudents->count()
        ]);
    }
}
