<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Grade;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class GradeController extends BaseController
{
    public function index(Request $request)
    {
        $user = $this->getCurrentUser();

        $query = Grade::with([
            'student.user',
            'term',
            'classSubject.class',
            'classSubject.subject',
        ])->accessibleToUser($user);

        if ($request->filled('class_id')) {
            $query->whereHas('classSubject.class', function ($q) use ($request) {
                $q->where('id', $request->class_id);
            });
        }

        if ($request->filled('subject_id')) {
            $query->whereHas('classSubject.subject', function ($q) use ($request) {
                $q->where('id', $request->subject_id);
            });
        }

        if ($request->filled('term_id')) {
            $query->where('term_id', $request->term_id);
        }

        if ($request->filled('assessment_type')) {
            $query->where('assessment_type', $request->assessment_type);
        }

        return $this->successResponse($query->get(), 'Grades retrieved successfully');
    }

    public function store(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:students,user_id',
            'class_subject_id' => 'required|exists:class_subjects,id',
            'term_id' => 'required|exists:terms,id',
            'assessment_type' => 'required|in:exam,quiz,assignment,project,participation',
            'max_score' => 'required|numeric|min:0',
            'score_obtained' => 'required|numeric|min:0',
            'weightage' => 'sometimes|numeric|min:0|max:100',
            'grade_letter' => 'nullable|string|max:3',
            'remarks' => 'nullable|string',
        ]);

        $user = $this->getCurrentUser();

        if ($user->isTeacher()) {
            $classSubject = \App\Models\ClassSubject::findOrFail($request->class_subject_id);
            if ($classSubject->teacher_id !== $user->teacher->user_id) {
                return $this->errorResponse('You can only create grades for your assigned subjects', 403);
            }
        }

        $grade = Grade::create([
            'student_id' => $request->student_id,
            'class_subject_id' => $request->class_subject_id,
            'term_id' => $request->term_id,
            'assessment_type' => $request->assessment_type,
            'max_score' => $request->max_score,
            'score_obtained' => $request->score_obtained,
            'weightage' => $request->weightage ?? 1.00,
            'grade_letter' => $request->grade_letter,
            'remarks' => $request->remarks,
            'recorded_by' => $user->id,
            'recorded_at' => now(),
        ]);

        $grade->load(['student.user', 'classSubject.subject', 'classSubject.class', 'term', 'recordedBy']);

        return $this->successResponse($grade, 'Grade created successfully', 201);
    }

    public function show($id)
    {
        $user = $this->getCurrentUser();

        try {
            $grade = Grade::with(['student.user', 'classSubject.subject', 'classSubject.class', 'term', 'recordedBy'])->findOrFail($id);

            if ($user->isTeacher() && $grade->recorded_by !== $user->id) {
                return $this->errorResponse('Unauthorized', 403);
            }

            return $this->successResponse($grade, 'Grade retrieved successfully');
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Grade not found', 404);
        }
    }

    public function update(Request $request, $id)
    {
        $user = $this->getCurrentUser();

        try {
            $grade = Grade::findOrFail($id);

            if ($user->isTeacher() && $grade->recorded_by !== $user->id) {
                return $this->errorResponse('Unauthorized', 403);
            }

            $request->validate([
                'assessment_type' => 'sometimes|in:exam,quiz,assignment,project,participation',
                'max_score' => 'sometimes|numeric|min:0',
                'score_obtained' => 'sometimes|numeric|min:0',
                'weightage' => 'sometimes|numeric|min:0|max:100',
                'grade_letter' => 'nullable|string|max:3',
                'remarks' => 'nullable|string',
            ]);

            $grade->update($request->only([
                'assessment_type', 'max_score', 'score_obtained', 
                'weightage', 'grade_letter', 'remarks'
            ]));

            $grade->load(['student.user', 'classSubject.subject', 'term', 'recordedBy']);

            return $this->successResponse($grade, 'Grade updated successfully');
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Grade not found', 404);
        }
    }

    public function destroy($id)
    {
        $user = $this->getCurrentUser();

        try {
            $grade = Grade::findOrFail($id);

            if ($user->isTeacher() && $grade->recorded_by !== $user->id) {
                return $this->errorResponse('Unauthorized', 403);
            }

            $grade->delete();

            return $this->successResponse(null, 'Grade deleted successfully');
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Grade not found', 404);
        }
    }

    public function studentGrades($studentId, Request $request)
    {
        $user = $this->getCurrentUser();

        $student = \App\Models\Student::findOrFail($studentId);

        // Simple role check
        if ($user->isTeacher() && $student->class->teacher_id !== $user->id) {
            return $this->errorResponse('Unauthorized', 403);
        }

        $query = Grade::with(['classSubject.subject', 'term', 'recordedBy'])
                     ->accessibleToUser($user)
                     ->forStudent($studentId);

        if ($request->filled('term_id')) {
            $query->forTerm($request->term_id);
        }

        if ($request->filled('assessment_type')) {
            $query->byAssessmentType($request->assessment_type);
        }

        $grades = $query->orderBy('recorded_at', 'desc')->get();

        $statistics = [
            'total_grades' => $grades->count(),
            'average_score' => $grades->avg('score_obtained'),
            'highest_score' => $grades->max('score_obtained'),
            'lowest_score' => $grades->min('score_obtained'),
            'by_assessment_type' => $grades->groupBy('assessment_type')->map(function ($group) {
                return [
                    'count' => $group->count(),
                    'average' => $group->avg('score_obtained'),
                ];
            }),
        ];

        return $this->successResponse([
            'grades' => $grades,
            'statistics' => $statistics
        ], 'Student grades retrieved successfully');
    }

    public function bulkStore(Request $request)
    {
        $request->validate([
            'grades' => 'required|array',
            'grades.*.student_id' => 'required|exists:students,user_id',
            'grades.*.class_subject_id' => 'required|exists:class_subjects,id',
            'grades.*.term_id' => 'required|exists:terms,id',
            'grades.*.assessment_type' => 'required|in:exam,quiz,assignment,project,participation',
            'grades.*.max_score' => 'required|numeric|min:0',
            'grades.*.score_obtained' => 'required|numeric|min:0',
            'grades.*.weightage' => 'sometimes|numeric|min:0|max:100',
            'grades.*.grade_letter' => 'nullable|string|max:3',
            'grades.*.remarks' => 'nullable|string',
        ]);

        $user = $this->getCurrentUser();
        $createdGrades = [];
        $errors = [];

        foreach ($request->grades as $index => $gradeData) {
            try {
                if ($user->isTeacher()) {
                    $classSubject = \App\Models\ClassSubject::findOrFail($gradeData['class_subject_id']);
                    if ($classSubject->teacher_id !== $user->teacher->user_id) {
                        $errors[] = "Grade {$index}: Unauthorized";
                        continue;
                    }
                }

                $grade = Grade::create([
                    'student_id' => $gradeData['student_id'],
                    'class_subject_id' => $gradeData['class_subject_id'],
                    'term_id' => $gradeData['term_id'],
                    'assessment_type' => $gradeData['assessment_type'],
                    'max_score' => $gradeData['max_score'],
                    'score_obtained' => $gradeData['score_obtained'],
                    'weightage' => $gradeData['weightage'] ?? 1.00,
                    'grade_letter' => $gradeData['grade_letter'] ?? null,
                    'remarks' => $gradeData['remarks'] ?? null,
                    'recorded_by' => $user->id,
                    'recorded_at' => now(),
                ]);

                $createdGrades[] = $grade;
            } catch (\Exception $e) {
                $errors[] = "Grade {$index}: " . $e->getMessage();
            }
        }

        return $this->successResponse([
            'created_grades' => count($createdGrades),
            'total_attempted' => count($request->grades),
            'errors' => $errors,
            'grades' => $createdGrades
        ], 'Bulk grade creation completed');
    }

    public function assessmentTypes()
    {
        $types = Grade::select('assessment_type')->distinct()->pluck('assessment_type');

        return response()->json($types);
    }
}
