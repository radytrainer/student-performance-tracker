<?php

namespace App\Http\Controllers;

use App\Models\Grade;
use App\Models\User;
use Illuminate\Http\Request;

class GradeController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Grade::class, 'grade');
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', Grade::class);

        $user = auth()->user();

        $query = Grade::with(['student.user', 'classSubject.subject', 'classSubject.class', 'term', 'recordedBy'])
            ->accessibleToUser($user);

        // Optional filters
        if ($request->filled('student_id')) {
            $query->where('student_id', $request->student_id);
        }

        if ($request->filled('term_id')) {
            $query->where('term_id', $request->term_id);
        }

        if ($request->filled('assessment_type')) {
            $query->where('assessment_type', $request->assessment_type);
        }

        if ($request->filled('class_id')) {
            $query->whereHas('classSubject', fn($q) => $q->where('class_id', $request->class_id));
        }

        $grades = $query->latest('recorded_at')->paginate($request->get('per_page', 15));

        return $this->paginatedResponse($grades, 'Grades retrieved successfully');
    }

    public function store(Request $request)
    {
        $this->authorize('create', Grade::class);

        $data = $request->validate([
            'student_id'        => 'required|exists:students,user_id',
            'class_subject_id'  => 'required|exists:class_subjects,id',
            'term_id'           => 'required|exists:terms,id',
            'assessment_type'   => 'required|in:exam,quiz,assignment',
            'max_score'         => 'required|numeric|min:0',
            'score_obtained'    => 'required|numeric|min:0',
            'weightage'         => 'nullable|numeric|min:0|max:100',
            'grade_letter'      => 'nullable|string|max:3',
            'remarks'           => 'nullable|string',
        ]);

        $user = auth()->user();
        $user = $user instanceof \App\Models\User ? $user : User::find($user->id);
        if ($user->isTeacher()) {
            $subject = \App\Models\ClassSubject::findOrFail($data['class_subject_id']);
            if (!$user->teacher || $subject->teacher_id !== $user->teacher->user_id) {
                abort(403, 'You can only grade your assigned subjects.');
            }
        }

        $data['weightage'] = $data['weightage'] ?? 1.00;
        $data['recorded_by'] = $user->id;
        $data['recorded_at'] = now();

        $grade = Grade::create($data);
        $grade->load(['student.user', 'classSubject.subject', 'term', 'recordedBy']);

        return $this->successResponse($grade, 'Grade created successfully', 201);
    }

    public function show(Grade $grade)
    {
        $this->authorize('view', $grade);
        $grade->load(['student.user', 'classSubject.subject', 'term', 'recordedBy']);

        return $this->successResponse($grade, 'Grade retrieved successfully');
    }

    public function update(Request $request, Grade $grade)
    {
        $this->authorize('update', $grade);

        $data = $request->validate([
            'assessment_type'   => 'sometimes|in:exam,quiz,assignment',
            'max_score'         => 'sometimes|numeric|min:0',
            'score_obtained'    => 'sometimes|numeric|min:0',
            'weightage'         => 'sometimes|numeric|min:0|max:100',
            'grade_letter'      => 'nullable|string|max:3',
            'remarks'           => 'nullable|string',
        ]);

        $grade->update($data);
        $grade->load(['student.user', 'classSubject.subject', 'term', 'recordedBy']);

        return $this->successResponse($grade, 'Grade updated successfully');
    }

    public function destroy(Grade $grade)
    {
        $this->authorize('delete', $grade);
        $grade->delete();

        return $this->successResponse(null, 'Grade deleted successfully');
    }

    public function studentGrades($studentId, Request $request)
    {
        $student = \App\Models\Student::findOrFail($studentId);
        $this->authorize('viewStudentGrades', [Grade::class, $student->user]);

        $query = Grade::with(['classSubject.subject', 'term', 'recordedBy'])
            ->where('student_id', $studentId)
            ->accessibleToUser(auth()->user());

        if ($request->filled('term_id')) {
            $query->where('term_id', $request->term_id);
        }

        if ($request->filled('assessment_type')) {
            $query->where('assessment_type', $request->assessment_type);
        }

        $grades = $query->orderBy('recorded_at', 'desc')->get();

        $statistics = [
            'total_grades'       => $grades->count(),
            'average_score'      => $grades->avg('score_obtained'),
            'highest_score'      => $grades->max('score_obtained'),
            'lowest_score'       => $grades->min('score_obtained'),
            'by_assessment_type' => $grades->groupBy('assessment_type')->map(fn($grp) => [
                'count'   => $grp->count(),
                'average' => $grp->avg('score_obtained'),
            ]),
        ];

        return $this->successResponse(compact('grades', 'statistics'), 'Student grades retrieved successfully');
    }
}
