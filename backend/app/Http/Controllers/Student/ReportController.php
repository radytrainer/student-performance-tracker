<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Grade;
use App\Models\Attendance;
use App\Models\ReportCard;
use App\Models\Term;
use App\Models\ClassSubject;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\AcademicSummaryExport;
use App\Exports\GradeReportExport;
use App\Exports\AttendanceReportExport;
use App\Exports\ProgressReportExport;
use App\Exports\TranscriptExport;
// use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
    protected $student;

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $this->student = Auth::user()->student;
            if (!$this->student) {
                return response()->json(['error' => 'Student profile not found'], 404);
            }
            return $next($request);
        });
    }

    /**
     * Get student reports dashboard data
     */
    public function index()
    {
        try {
            $currentTerm = Term::where('is_current', true)->first();
            
            $data = [
                'student_stats' => $this->getStudentStats($currentTerm),
                'available_reports' => $this->getAvailableReports(),
                'recent_reports' => $this->getRecentReports(),
                'gpa_history' => $this->getGPAHistory(),
                'achievements' => $this->getAchievements()
            ];

            return response()->json($data);
        } catch (\Exception $e) {
            Log::error('ReportController error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json([
                'error' => 'Failed to load dashboard data', 
                'message' => $e->getMessage(),
                'line' => $e->getLine()
            ], 500);
        }
    }

    /**
     * Generate a new report
     */
    public function generate(Request $request)
    {
        $request->validate([
            'type' => 'required|in:academic_summary,grade_report,attendance_report,progress_report,transcript',
            'period' => 'required|in:current_quarter,current_semester,academic_year,all_time',
            'format' => 'required|in:pdf,excel'
        ]);

        try {
            $reportData = $this->generateReportData($request->type, $request->period);
            
            if ($request->format === 'pdf') {
                return $this->generatePDFReport($request->type, $reportData);
            } else {
                return $this->generateExcelReport($request->type, $reportData);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to generate report: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get student statistics
     */
    private function getStudentStats($currentTerm)
    {
        try {
            $studentId = $this->student->user_id;
            
            // Simple default values to test basic functionality
            $gpa = 0.0;
            $attendancePercentage = 0;
            $rank = '0/0';
            $credits = 0;
            
            // Try to calculate GPA safely
            try {
                $gpa = $this->calculateGPA($studentId, $currentTerm->id ?? null);
            } catch (\Exception $e) {
                Log::error('GPA calculation failed: ' . $e->getMessage());
            }
            
            // Try to calculate attendance safely
            try {
                $attendanceData = $this->calculateAttendancePercentage($studentId, $currentTerm->id ?? null);
                $attendancePercentage = $attendanceData['percentage'];
            } catch (\Exception $e) {
                Log::error('Attendance calculation failed: ' . $e->getMessage());
            }
            
            // Try to get class rank safely
            try {
                $classRank = $this->getClassRank($studentId, $currentTerm->id ?? null);
                $rank = $classRank['rank'] . '/' . $classRank['total'];
            } catch (\Exception $e) {
                Log::error('Class rank calculation failed: ' . $e->getMessage());
            }
            
            // Try to get credits safely
            try {
                $credits = $this->getCreditsEarned($studentId);
            } catch (\Exception $e) {
                Log::error('Credits calculation failed: ' . $e->getMessage());
            }

            return [
                'gpa' => number_format($gpa, 1),
                'attendance' => $attendancePercentage,
                'rank' => $rank,
                'credits' => $credits
            ];
        } catch (\Exception $e) {
            Log::error('getStudentStats failed: ' . $e->getMessage());
            // Return safe defaults
            return [
                'gpa' => '0.0',
                'attendance' => '0',
                'rank' => '0/0',
                'credits' => '0'
            ];
        }
    }

    /**
     * Calculate student GPA
     */
    private function calculateGPA($studentId, $termId = null)
    {
        $gradesQuery = Grade::where('student_id', $studentId);
        
        if ($termId) {
            $gradesQuery->where('term_id', $termId);
        }
        
        $grades = $gradesQuery->with('classSubject.subject')->get();
        
        if ($grades->isEmpty()) {
            return 0;
        }

        $totalPoints = 0;
        $totalCredits = 0;

        foreach ($grades as $grade) {
            $gradePoints = $this->getGradePoints($grade->grade_letter);
            $credits = $grade->classSubject->subject->credit_hours ?? 1;
            
            $totalPoints += $gradePoints * $credits;
            $totalCredits += $credits;
        }

        return $totalCredits > 0 ? $totalPoints / $totalCredits : 0;
    }

    /**
     * Convert grade letter to points
     */
    private function getGradePoints($gradeLetter)
    {
        $gradeScale = [
            'A+' => 4.0, 'A' => 4.0, 'A-' => 3.7,
            'B+' => 3.3, 'B' => 3.0, 'B-' => 2.7,
            'C+' => 2.3, 'C' => 2.0, 'C-' => 1.7,
            'D+' => 1.3, 'D' => 1.0, 'D-' => 0.7,
            'F' => 0.0
        ];

        return $gradeScale[$gradeLetter] ?? 0;
    }

    /**
     * Calculate attendance percentage
     */
    private function calculateAttendancePercentage($studentId, $termId = null)
    {
        $attendanceQuery = Attendance::where('student_id', $studentId);
        
        if ($termId) {
            $term = Term::find($termId);
            if ($term) {
                $attendanceQuery->whereBetween('date', [$term->start_date, $term->end_date]);
            }
        }

        $totalDays = $attendanceQuery->count();
        $presentDays = $attendanceQuery->where('status', 'present')->count();

        $percentage = $totalDays > 0 ? round(($presentDays / $totalDays) * 100) : 0;

        return [
            'percentage' => $percentage,
            'present_days' => $presentDays,
            'total_days' => $totalDays
        ];
    }

    /**
     * Get class rank
     */
    private function getClassRank($studentId, $termId = null)
    {
        // Get all students in the same class
        $currentClassId = $this->student->current_class_id;
        
        $studentsInClass = Student::where('current_class_id', $currentClassId)
            ->pluck('user_id')
            ->toArray();

        // Calculate GPA for each student
        $studentGPAs = [];
        foreach ($studentsInClass as $id) {
            $gpa = $this->calculateGPA($id, $termId);
            $studentGPAs[] = ['student_id' => $id, 'gpa' => $gpa];
        }

        // Sort by GPA descending
        usort($studentGPAs, function($a, $b) {
            return $b['gpa'] <=> $a['gpa'];
        });

        // Find current student's rank
        $rank = 1;
        foreach ($studentGPAs as $index => $studentData) {
            if ($studentData['student_id'] == $studentId) {
                $rank = $index + 1;
                break;
            }
        }

        return [
            'rank' => $rank,
            'total' => count($studentsInClass)
        ];
    }

    /**
     * Get credits earned
     */
    private function getCreditsEarned($studentId)
    {
        return Grade::where('student_id', $studentId)
            ->whereNotIn('grade_letter', ['F', 'D-'])
            ->with('classSubject.subject')
            ->get()
            ->sum(function($grade) {
                return $grade->classSubject->subject->credit_hours ?? 1;
            });
    }

    /**
     * Get available reports configuration
     */
    private function getAvailableReports()
    {
        return [
            [
                'id' => 1,
                'name' => 'Academic Summary',
                'description' => 'Overall academic performance overview',
                'icon' => 'fas fa-chart-bar',
                'color' => '#3B82F6',
                'updateFrequency' => 'Updated weekly'
            ],
            [
                'id' => 2,
                'name' => 'Grade Report',
                'description' => 'Detailed grades by subject and assignment',
                'icon' => 'fas fa-clipboard-list',
                'color' => '#10B981',
                'updateFrequency' => 'Updated daily'
            ],
            [
                'id' => 3,
                'name' => 'Attendance Report',
                'description' => 'Attendance records and patterns',
                'icon' => 'fas fa-calendar-check',
                'color' => '#F59E0B',
                'updateFrequency' => 'Updated daily'
            ],
            [
                'id' => 4,
                'name' => 'Progress Report',
                'description' => 'Academic progress and trends',
                'icon' => 'fas fa-chart-line',
                'color' => '#8B5CF6',
                'updateFrequency' => 'Updated monthly'
            ],
            [
                'id' => 5,
                'name' => 'Transcript',
                'description' => 'Official academic transcript',
                'icon' => 'fas fa-scroll',
                'color' => '#EF4444',
                'updateFrequency' => 'Updated at term end'
            ]
        ];
    }

    /**
     * Get recent reports
     */
    private function getRecentReports()
    {
        return ReportCard::where('student_id', $this->student->user_id)
            ->with(['term', 'generatedBy'])
            ->orderBy('generated_at', 'desc')
            ->take(10)
            ->get()
            ->map(function($report) {
                return [
                    'id' => $report->id,
                    'name' => 'Report - ' . $report->term->term_name,
                    'type' => 'academic_summary',
                    'period' => $report->term->term_name,
                    'generatedAt' => $report->generated_at,
                    'status' => $report->file_path ? 'completed' : 'processing',
                    'file_path' => $report->file_path
                ];
            });
    }

    /**
     * Get GPA history
     */
    private function getGPAHistory()
    {
        $terms = Term::orderBy('start_date', 'desc')->take(4)->get();
        $history = [];

        foreach ($terms as $term) {
            $gpa = $this->calculateGPA($this->student->user_id, $term->id);
            $history[] = [
                'name' => $term->term_name,
                'gpa' => number_format($gpa, 1)
            ];
        }

        return $history;
    }

    /**
     * Get student achievements
     */
    private function getAchievements()
    {
        $achievements = [];
        $currentTerm = Term::where('is_current', true)->first();

        if ($currentTerm) {
            $gpa = $this->calculateGPA($this->student->user_id, $currentTerm->id);
            
            // Honor roll achievement
            if ($gpa >= 3.5) {
                $achievements[] = [
                    'id' => 1,
                    'title' => 'Honor Roll',
                    'description' => 'Made honor roll for ' . $currentTerm->term_name
                ];
            }

            // Perfect attendance
            $attendanceData = $this->calculateAttendancePercentage($this->student->user_id, $currentTerm->id);
            if ($attendanceData['percentage'] == 100) {
                $achievements[] = [
                    'id' => 2,
                    'title' => 'Perfect Attendance',
                    'description' => 'Perfect attendance in ' . $currentTerm->term_name
                ];
            }

            // High performance in specific subjects
            $topSubjects = Grade::where('student_id', $this->student->user_id)
                ->where('term_id', $currentTerm->id)
                ->whereIn('grade_letter', ['A+', 'A', 'A-'])
                ->with('classSubject.subject')
                ->get()
                ->groupBy('classSubject.subject.subject_name');

            foreach ($topSubjects->take(3) as $subjectName => $grades) {
                $achievements[] = [
                    'id' => 3 + count($achievements),
                    'title' => $subjectName . ' Achievement',
                    'description' => 'Excellent performance in ' . $subjectName
                ];
            }
        }

        return $achievements;
    }

    /**
     * Generate report data based on type and period
     */
    private function generateReportData($type, $period)
    {
        $termId = null;
        
        switch ($period) {
            case 'current_quarter':
            case 'current_semester':
                $term = Term::where('is_current', true)->first();
                $termId = $term->id ?? null;
                break;
            case 'academic_year':
                $terms = Term::where('academic_year', now()->year)->pluck('id');
                break;
            case 'all_time':
                // No filter
                break;
        }

        $data = [
            'student' => $this->student->load('user', 'currentClass'),
            'period' => $period,
            'generated_at' => now(),
        ];

        switch ($type) {
            case 'academic_summary':
                $data = array_merge($data, $this->getAcademicSummaryData($termId));
                break;
            case 'grade_report':
                $data = array_merge($data, $this->getGradeReportData($termId));
                break;
            case 'attendance_report':
                $data = array_merge($data, $this->getAttendanceReportData($termId));
                break;
            case 'progress_report':
                $data = array_merge($data, $this->getProgressReportData($termId));
                break;
            case 'transcript':
                $data = array_merge($data, $this->getTranscriptData());
                break;
        }

        return $data;
    }

    /**
     * Get academic summary data
     */
    private function getAcademicSummaryData($termId)
    {
        return [
            'gpa' => $this->calculateGPA($this->student->user_id, $termId),
            'attendance' => $this->calculateAttendancePercentage($this->student->user_id, $termId),
            'grades' => Grade::where('student_id', $this->student->user_id)
                ->when($termId, function($query, $termId) {
                    return $query->where('term_id', $termId);
                })
                ->with(['classSubject.subject', 'term'])
                ->get(),
            'class_rank' => $this->getClassRank($this->student->user_id, $termId)
        ];
    }

    /**
     * Get grade report data
     */
    private function getGradeReportData($termId)
    {
        $grades = Grade::where('student_id', $this->student->user_id)
            ->when($termId, function($query, $termId) {
                return $query->where('term_id', $termId);
            })
            ->with(['classSubject.subject', 'term'])
            ->orderBy('term_id', 'desc')
            ->get()
            ->groupBy('classSubject.subject.subject_name');

        return [
            'grades_by_subject' => $grades,
            'overall_gpa' => $this->calculateGPA($this->student->user_id, $termId)
        ];
    }

    /**
     * Get attendance report data
     */
    private function getAttendanceReportData($termId)
    {
        $attendanceQuery = Attendance::where('student_id', $this->student->user_id);
        
        if ($termId) {
            $term = Term::find($termId);
            if ($term) {
                $attendanceQuery->whereBetween('date', [$term->start_date, $term->end_date]);
            }
        }

        $attendanceRecords = $attendanceQuery->with('class')->orderBy('date', 'desc')->get();

        return [
            'attendance_records' => $attendanceRecords,
            'summary' => $this->calculateAttendancePercentage($this->student->user_id, $termId)
        ];
    }

    /**
     * Get progress report data
     */
    private function getProgressReportData($termId)
    {
        $terms = Term::orderBy('start_date', 'desc')->take(4)->get();
        $progressData = [];

        foreach ($terms as $term) {
            $progressData[] = [
                'term' => $term,
                'gpa' => $this->calculateGPA($this->student->user_id, $term->id),
                'attendance' => $this->calculateAttendancePercentage($this->student->user_id, $term->id),
                'grades_count' => Grade::where('student_id', $this->student->user_id)
                    ->where('term_id', $term->id)
                    ->count()
            ];
        }

        return [
            'progress_by_term' => $progressData,
            'achievements' => $this->getAchievements()
        ];
    }

    /**
     * Get transcript data
     */
    private function getTranscriptData()
    {
        $allGrades = Grade::where('student_id', $this->student->user_id)
            ->with(['classSubject.subject', 'term'])
            ->orderBy('term_id', 'asc')
            ->get()
            ->groupBy('term.term_name');

        $overallGPA = $this->calculateGPA($this->student->user_id);
        $totalCredits = $this->getCreditsEarned($this->student->user_id);

        return [
            'grades_by_term' => $allGrades,
            'overall_gpa' => $overallGPA,
            'total_credits' => $totalCredits,
            'graduation_status' => $totalCredits >= 120 ? 'Eligible' : 'In Progress'
        ];
    }

    /**
     * Generate PDF report
     */
    private function generatePDFReport($type, $data)
    {
        // For now, return a simple JSON response since DomPDF is not yet installed
        // In production, uncomment the PDF generation code below
        
        // Create report record
        $reportCard = ReportCard::create([
            'student_id' => $this->student->user_id,
            'term_id' => Term::where('is_current', true)->first()->id ?? null,
            'generated_by' => Auth::id(),
            'overall_grade' => $data['gpa'] ?? 0,
            'attendance_percentage' => $data['attendance']['percentage'] ?? 0,
            'file_path' => null, // Will be set when PDF is actually generated
            'generated_at' => now()
        ]);
        
        return response()->json([
            'message' => 'Report generated successfully',
            'report_id' => $reportCard->id,
            'data' => $data
        ]);
        
        /*
        // Uncomment this code after installing barryvdh/laravel-dompdf
        $viewName = "reports.{$type}";
        
        $pdf = PDF::loadView($viewName, $data);
        
        $filename = "{$type}_" . $this->student->student_code . "_" . now()->format('Y-m-d') . ".pdf";
        
        // Save to storage
        $filePath = "reports/{$filename}";
        $pdf->save(storage_path("app/public/{$filePath}"));
        
        // Update report record with file path
        $reportCard->update(['file_path' => $filePath]);
        
        return $pdf->download($filename);
        */
    }

    /**
     * Generate Excel report
     */
    private function generateExcelReport($type, $data)
    {
        $filename = "{$type}_" . $this->student->student_code . "_" . now()->format('Y-m-d') . ".xlsx";
        
        try {
            switch ($type) {
                case 'academic_summary':
                    $export = new AcademicSummaryExport($data);
                    break;
                case 'grade_report':
                    $export = new GradeReportExport($data);
                    break;
                case 'attendance_report':
                    $export = new AttendanceReportExport($data);
                    break;
                case 'progress_report':
                    $export = new ProgressReportExport($data);
                    break;
                case 'transcript':
                    $export = new TranscriptExport($data);
                    break;
                default:
                    $export = new AcademicSummaryExport($data);
            }

            // Create report record
            $reportCard = ReportCard::create([
                'student_id' => $this->student->user_id,
                'term_id' => Term::where('is_current', true)->first()->id ?? null,
                'generated_by' => Auth::id(),
                'overall_grade' => $data['gpa'] ?? 0,
                'attendance_percentage' => $data['attendance']['percentage'] ?? 0,
                'file_path' => null, // Excel files are not stored, downloaded directly
                'generated_at' => now()
            ]);

            return Excel::download($export, $filename);
            
        } catch (\Exception $e) {
            Log::error('Excel generation failed: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to generate Excel report: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Download existing report
     */
    public function download($id)
    {
        $report = ReportCard::where('student_id', $this->student->user_id)
            ->findOrFail($id);

        if (!$report->file_path || !file_exists(storage_path("app/public/{$report->file_path}"))) {
            return response()->json(['error' => 'Report file not found'], 404);
        }

        return response()->download(storage_path("app/public/{$report->file_path}"));
    }
}
