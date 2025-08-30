<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Student;
use App\Models\Grade;
use App\Models\Term;

class StudentAnalyticsController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:sanctum', 'role:teacher']);
    }

    /**
     * Return per-subject student vs class average (percentage) for a given student and optional term.
     * GET /api/teacher/students/{student}/comparison?term_id=optional
     */
    public function comparison(Request $request, int $student): JsonResponse
    {
        try {
            $termId = $request->query('term_id');
            if (!$termId) {
                $termId = optional(Term::where('is_current', true)->first())->id;
            }

            $studentModel = Student::where('user_id', $student)->first();
            if (!$studentModel) {
                return response()->json(['data' => [], 'message' => 'Student not found'], 404);
            }

            $classId = $studentModel->current_class_id;
            if (!$classId) {
                return response()->json(['data' => []]);
            }

            // Student averages by subject
            $studentAverages = Grade::query()
                ->join('class_subjects', 'grades.class_subject_id', '=', 'class_subjects.id')
                ->join('subjects', 'class_subjects.subject_id', '=', 'subjects.id')
                ->select('subjects.id as subject_id', 'subjects.subject_name')
                ->when($termId, function ($q) use ($termId) { $q->where('grades.term_id', $termId); })
                ->where('grades.student_id', $studentModel->user_id)
                ->groupBy('subjects.id', 'subjects.subject_name')
                ->selectRaw('CASE WHEN SUM(grades.max_score) > 0 THEN (SUM(grades.score_obtained) / SUM(grades.max_score)) * 100 ELSE 0 END as student_avg')
                ->get()
                ->keyBy('subject_id');

            // Class averages by subject for student's current class
            $classAverages = Grade::query()
                ->join('class_subjects', 'grades.class_subject_id', '=', 'class_subjects.id')
                ->join('subjects', 'class_subjects.subject_id', '=', 'subjects.id')
                ->select('subjects.id as subject_id', 'subjects.subject_name')
                ->when($termId, function ($q) use ($termId) { $q->where('grades.term_id', $termId); })
                ->where('class_subjects.class_id', $classId)
                ->groupBy('subjects.id', 'subjects.subject_name')
                ->selectRaw('CASE WHEN SUM(grades.max_score) > 0 THEN (SUM(grades.score_obtained) / SUM(grades.max_score)) * 100 ELSE 0 END as class_avg')
                ->get()
                ->keyBy('subject_id');

            $subjectIds = collect(array_unique(array_merge($studentAverages->keys()->all(), $classAverages->keys()->all())));
            $result = $subjectIds->map(function ($sid) use ($studentAverages, $classAverages) {
                $s = $studentAverages->get($sid);
                $c = $classAverages->get($sid);
                return [
                    'subject_id' => $sid,
                    'subject_name' => $s->subject_name ?? ($c->subject_name ?? 'Unknown'),
                    'student_avg' => round(optional($s)->student_avg ?? 0, 1),
                    'class_avg' => round(optional($c)->class_avg ?? 0, 1),
                ];
            })->values();

            return response()->json(['data' => $result]);
        } catch (\Throwable $e) {
            return response()->json(['data' => [], 'error' => $e->getMessage()], 500);
        }
    }
}
