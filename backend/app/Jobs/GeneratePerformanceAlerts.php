<?php

namespace App\Jobs;

use App\Models\PerformanceAlert;
use App\Models\Student;
use App\Models\Grade;
use App\Models\Attendance;
use App\Models\Term;
use App\Models\SystemSetting;
use App\Models\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GeneratePerformanceAlerts implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public ?int $termId;

    public function __construct(?int $termId = null)
    {
        $this->termId = $termId;
    }

    public function handle(): void
    {
        $term = $this->termId ? Term::find($this->termId) : Term::where('is_current', true)->first();
        if (!$term) return;

        $passing = (int) (SystemSetting::where('setting_key', 'passingGrade')->first()->setting_value ?? 60);
        $attRequired = (int) (SystemSetting::where('setting_key', 'requiredAttendance')->first()->setting_value ?? 75);
        // stored as JSON; decode if needed
        $passing = is_numeric($passing) ? (int)$passing : ((int) json_decode((string)$passing, true));
        $attRequired = is_numeric($attRequired) ? (int)$attRequired : ((int) json_decode((string)$attRequired, true));

        // For each student in classes
        $students = Student::all(['user_id', 'current_class_id']);
        foreach ($students as $s) {
            // Grade average
            $g = Grade::selectRaw('SUM(score_obtained) as so, SUM(max_score) as ms')
                ->where('student_id', $s->user_id)
                ->where('term_id', $term->id)
                ->first();
            $gradePct = ($g && $g->ms > 0) ? ($g->so / $g->ms) * 100 : null;

            // Attendance percent
            $a = Attendance::selectRaw("COUNT(*) as total, SUM(CASE WHEN status='present' THEN 1 ELSE 0 END) as present")
                ->where('student_id', $s->user_id)
                ->whereBetween('date', [$term->start_date, $term->end_date])
                ->first();
            $attPct = ($a && $a->total > 0) ? ($a->present / $a->total) * 100 : null;

            // Low grade alert
            if ($gradePct !== null && $gradePct < $passing) {
                $this->createAlert($s->user_id, $term->id, 'low_grade', 'medium', 'Average grade below passing threshold ('.$passing.'%)');
            }
            // Attendance alert
            if ($attPct !== null && $attPct < $attRequired) {
                $this->createAlert($s->user_id, $term->id, 'attendance', 'medium', 'Attendance below required '.$attRequired.'%');
            }
        }
    }

    private function createAlert(int $studentId, int $termId, string $type, string $severity, string $message): void
    {
        $existing = PerformanceAlert::where([
            'student_id' => $studentId,
            'term_id' => $termId,
            'alert_type' => $type,
            'is_resolved' => false,
        ])->first();
        if ($existing) return; // avoid duplicates

        $alert = PerformanceAlert::create([
            'student_id' => $studentId,
            'term_id' => $termId,
            'alert_type' => $type,
            'severity' => $severity,
            'message' => $message,
            'is_resolved' => false,
            'created_by' => null,
        ]);

        try {
            $n = Notification::create([
                'user_id' => $studentId,
                'title' => 'Performance Alert',
                'message' => $message,
                'type' => 'warning',
                'is_read' => false,
                'sent_at' => now(),
            ]);
            event(new \App\Events\NotificationCreated($n));
        } catch (\Throwable $e) {}
    }
}
