<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Student;
use App\Models\Grade;
use App\Models\Attendance;
use App\Models\Classes;
use App\Models\Subject;
use App\Models\Term;

class DashboardController extends BaseController
{
    /**
     * Get role-specific dashboard data
     */
    public function index(Request $request)
    {
        $user = $this->getCurrentUser();
        
        switch ($user->role) {
            case 'admin':
                return $this->getAdminDashboard();
            case 'teacher':
                return $this->getTeacherDashboard($user);
            case 'student':
                return $this->getStudentDashboard($user);
            default:
                return $this->errorResponse('Invalid user role', 403);
        }
    }

    /**
     * Admin dashboard with system-wide statistics
     */
    private function getAdminDashboard()
    {
        $currentTerm = Term::where('is_current', true)->first();
        
        $data = [
            'overview' => [
                'total_students' => Student::active()->count(),
                'total_teachers' => User::where('role', 'teacher')->where('is_active', true)->count(),
                'total_classes' => Classes::count(),
                'total_subjects' => Subject::count(),
                'current_term' => $currentTerm?->term_name,
            ],
            'recent_activity' => [
                'new_students_this_month' => Student::whereHas('user', function($q) {
                    $q->where('created_at', '>=', now()->startOfMonth());
                })->count(),
                'grades_entered_today' => Grade::whereDate('recorded_at', today())->count(),
                'attendance_marked_today' => Attendance::whereDate('recorded_at', today())->count(),
            ],
            'performance_summary' => [
                'average_grade' => Grade::when($currentTerm, function($q) use ($currentTerm) {
                    return $q->where('term_id', $currentTerm->id);
                })->avg('score_obtained'),
                'attendance_rate' => $this->calculateSystemAttendanceRate($currentTerm),
                'active_alerts' => \App\Models\PerformanceAlert::where('is_resolved', false)->count(),
            ],
            'top_performing_classes' => $this->getTopPerformingClasses($currentTerm),
            'recent_alerts' => \App\Models\PerformanceAlert::with(['student.user', 'term'])
                ->where('is_resolved', false)
                ->latest()
                ->limit(10)
                ->get(),
        ];

        return $this->successResponse($data, 'Admin dashboard data retrieved successfully');
    }

    /**
     * Teacher dashboard with class-specific data
     */
    private function getTeacherDashboard(User $user)
    {
        $currentTerm = Term::where('is_current', true)->first();
        
        // Get teacher's assigned classes and students
        $assignedClasses = $user->teacher->classSubjects()
            ->with(['class', 'subject'])
            ->get()
            ->pluck('class')
            ->unique('id');

        $assignedStudents = Student::accessibleToUser($user)->get();

        $data = [
            'overview' => [
                'assigned_classes' => $assignedClasses->count(),
                'total_students' => $assignedStudents->count(),
                'subjects_teaching' => $user->teacher->classSubjects()->distinct('subject_id')->count(),
                'current_term' => $currentTerm?->term_name,
            ],
            'my_classes' => $assignedClasses->map(function ($class) use ($user) {
                return [
                    'id' => $class->id,
                    'name' => $class->class_name,
                    'students_count' => Student::inClass($class->id)->count(),
                    'subjects_teaching' => $user->teacher->classSubjects()
                        ->where('class_id', $class->id)
                        ->with('subject')
                        ->get()
                        ->pluck('subject.subject_name'),
                ];
            }),
            'recent_activity' => [
                'grades_entered_this_week' => Grade::accessibleToUser($user)
                    ->where('recorded_at', '>=', now()->startOfWeek())
                    ->count(),
                'attendance_marked_today' => Attendance::accessibleToUser($user)
                    ->whereDate('recorded_at', today())
                    ->count(),
                'pending_grades' => $this->getPendingGradesCount($user, $currentTerm),
            ],
            'student_performance' => [
                'class_averages' => $this->getClassAverages($user, $currentTerm),
                'low_performers' => $this->getLowPerformers($user, $currentTerm),
                'attendance_issues' => $this->getAttendanceIssues($user),
            ],
            'upcoming_tasks' => [
                'assignments_due' => [], // Would need assignment tracking
                'exams_scheduled' => [], // Would need exam scheduling
                'reports_due' => [], // Would need report scheduling
            ],
        ];

        return $this->successResponse($data, 'Teacher dashboard data retrieved successfully');
    }

    /**
     * Student dashboard with personal academic data
     */
    private function getStudentDashboard(User $user)
    {
        $currentTerm = Term::where('is_current', true)->first();
        $student = $user->student;

        $data = [
            'overview' => [
                'current_class' => $student->currentClass?->class_name,
                'enrollment_date' => $student->enrollment_date->format('Y-m-d'),
                'current_term' => $currentTerm?->term_name,
                'student_code' => $student->student_code,
            ],
            'academic_performance' => [
                'current_gpa' => $this->calculateStudentGPA($student, $currentTerm),
                'total_grades' => Grade::forStudent($student->user_id)->count(),
                'attendance_percentage' => $this->calculateStudentAttendanceRate($student),
                'class_rank' => $this->getStudentClassRank($student, $currentTerm),
            ],
            'recent_grades' => Grade::forStudent($student->user_id)
                ->with(['classSubject.subject', 'term'])
                ->latest('recorded_at')
                ->limit(10)
                ->get()
                ->map(function ($grade) {
                    return [
                        'id' => $grade->id,
                        'subject' => $grade->classSubject->subject->subject_name,
                        'assessment_type' => $grade->assessment_type,
                        'score' => $grade->score_obtained,
                        'max_score' => $grade->max_score,
                        'percentage' => round(($grade->score_obtained / $grade->max_score) * 100, 2),
                        'grade_letter' => $grade->grade_letter,
                        'date' => $grade->recorded_at->format('Y-m-d'),
                    ];
                }),
            'attendance_summary' => [
                'total_days' => Attendance::forStudent($student->user_id)->count(),
                'present_days' => Attendance::forStudent($student->user_id)->byStatus('present')->count(),
                'absent_days' => Attendance::forStudent($student->user_id)->byStatus('absent')->count(),
                'late_days' => Attendance::forStudent($student->user_id)->byStatus('late')->count(),
                'recent_attendance' => Attendance::forStudent($student->user_id)
                    ->latest('date')
                    ->limit(10)
                    ->get()
                    ->map(function ($attendance) {
                        return [
                            'date' => $attendance->date->format('Y-m-d'),
                            'status' => $attendance->status,
                            'class' => $attendance->class->class_name ?? 'Unknown',
                        ];
                    }),
            ],
            'subject_performance' => $this->getStudentSubjectPerformance($student, $currentTerm),
            'alerts' => \App\Models\PerformanceAlert::where('student_id', $student->user_id)
                ->where('is_resolved', false)
                ->latest()
                ->get(),
        ];

        return $this->successResponse($data, 'Student dashboard data retrieved successfully');
    }

    // Helper methods

    private function calculateSystemAttendanceRate($term)
    {
        $query = Attendance::query();
        if ($term) {
            // Assuming attendance is within term dates
            $query->whereBetween('date', [$term->start_date, $term->end_date]);
        }
        
        $total = $query->count();
        if ($total === 0) return 0;
        
        $present = $query->where('status', 'present')->count();
        return round(($present / $total) * 100, 2);
    }

    private function getTopPerformingClasses($term, $limit = 5)
    {
        return Classes::withAvg(['studentClasses as average_grade' => function ($query) use ($term) {
            $query->join('grades', 'student_classes.student_id', '=', 'grades.student_id')
                  ->when($term, function ($q) use ($term) {
                      return $q->where('grades.term_id', $term->id);
                  });
        }], 'grades.score_obtained')
        ->orderByDesc('average_grade')
        ->limit($limit)
        ->get();
    }

    private function getPendingGradesCount($user, $term)
    {
        // This would require a more complex query to determine "pending" grades
        // For now, return 0
        return 0;
    }

    private function getClassAverages($user, $term)
    {
        return $user->teacher->classSubjects()
            ->with(['class', 'subject'])
            ->get()
            ->map(function ($classSubject) use ($term) {
                $average = Grade::where('class_subject_id', $classSubject->id)
                    ->when($term, function ($q) use ($term) {
                        return $q->where('term_id', $term->id);
                    })
                    ->avg('score_obtained');
                
                return [
                    'class' => $classSubject->class->class_name,
                    'subject' => $classSubject->subject->subject_name,
                    'average' => round($average ?? 0, 2),
                ];
            });
    }

    private function getLowPerformers($user, $term, $threshold = 60)
    {
        return Student::accessibleToUser($user)
            ->whereHas('grades', function ($query) use ($term, $threshold) {
                $query->when($term, function ($q) use ($term) {
                    return $q->where('term_id', $term->id);
                })
                ->groupBy('student_id')
                ->havingRaw('AVG(score_obtained) < ?', [$threshold]);
            })
            ->with('user')
            ->limit(10)
            ->get();
    }

    private function getAttendanceIssues($user, $threshold = 80)
    {
        // Students with attendance below threshold
        return Student::accessibleToUser($user)
            ->whereHas('attendances', function ($query) use ($threshold) {
                $query->groupBy('student_id')
                      ->havingRaw('(COUNT(CASE WHEN status = "present" THEN 1 END) / COUNT(*)) * 100 < ?', [$threshold]);
            })
            ->with('user')
            ->limit(10)
            ->get();
    }

    private function calculateStudentGPA($student, $term)
    {
        $grades = Grade::forStudent($student->user_id)
            ->when($term, function ($q) use ($term) {
                return $q->where('term_id', $term->id);
            })
            ->get();

        if ($grades->isEmpty()) return 0;

        $totalWeightedScore = $grades->sum(function ($grade) {
            return ($grade->score_obtained / $grade->max_score) * $grade->weightage;
        });

        $totalWeight = $grades->sum('weightage');

        return $totalWeight > 0 ? round(($totalWeightedScore / $totalWeight) * 4, 2) : 0; // Convert to 4.0 scale
    }

    private function calculateStudentAttendanceRate($student)
    {
        $total = Attendance::forStudent($student->user_id)->count();
        if ($total === 0) return 0;
        
        $present = Attendance::forStudent($student->user_id)->byStatus('present')->count();
        return round(($present / $total) * 100, 2);
    }

    private function getStudentClassRank($student, $term)
    {
        // This would require a complex ranking query
        // For now, return null
        return null;
    }

    private function getStudentSubjectPerformance($student, $term)
    {
        return Grade::forStudent($student->user_id)
            ->when($term, function ($q) use ($term) {
                return $q->where('term_id', $term->id);
            })
            ->with(['classSubject.subject'])
            ->get()
            ->groupBy('classSubject.subject.subject_name')
            ->map(function ($grades, $subject) {
                return [
                    'subject' => $subject,
                    'average' => round($grades->avg('score_obtained'), 2),
                    'highest' => $grades->max('score_obtained'),
                    'lowest' => $grades->min('score_obtained'),
                    'total_assessments' => $grades->count(),
                ];
            })
            ->values();
    }
}
