<?php

namespace App\Mail;

use App\Models\User;
use App\Models\FormAssignment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SurveyReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public $student;
    public $assignment;

    /**
     * Create a new message instance.
     */
    public function __construct(User $student, FormAssignment $assignment)
    {
        $this->student = $student;
        $this->assignment = $assignment;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        $daysUntilDue = $this->assignment->due_date 
            ? $this->assignment->due_date->diffInDays(now())
            : null;

        return $this->subject('Reminder: Complete Your Feedback Survey')
            ->view('emails.survey-reminder')
            ->with([
                'studentName' => $this->student->first_name . ' ' . $this->student->last_name,
                'surveyTitle' => $this->assignment->feedbackForm->title,
                'className' => $this->assignment->assignedClass->class_name,
                'dueDate' => $this->assignment->due_date?->format('F j, Y'),
                'daysUntilDue' => $daysUntilDue,
                'surveyUrl' => config('app.frontend_url') . '/student/surveys',
                'instructions' => $this->assignment->instructions,
            ]);
    }
}
