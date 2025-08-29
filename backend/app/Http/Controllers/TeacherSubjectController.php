<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\BaseController;
use App\Http\Requests\AssignTeacherSubjectsRequest;

class TeacherSubjectController extends BaseController
{
    /**
     * Assign subjects to a teacher
     */
    public function assignSubjects(AssignTeacherSubjectsRequest $request, User $teacher): JsonResponse
    {

        // Ensure the user is a teacher
        if ($teacher->role !== 'teacher') {
            return $this->errorResponse('User is not a teacher', 400);
        }

        // School isolation check
        $currentUser = $request->user();
        if ($currentUser->isSchoolAdmin() && $teacher->school_id !== $currentUser->school_id) {
            return $this->errorResponse('Cannot assign subjects to teachers from other schools', 403);
        }

        // Check if subjects belong to the same school (if school isolation is enabled)
        $subjects = Subject::whereIn('id', $request->subject_ids)->get();
        
        DB::beginTransaction();
        try {
            $primarySubjectId = $request->input('primary_subject_id');
            $assignments = [];
            
            foreach ($request->subject_ids as $subjectId) {
                // Check if assignment already exists
                $existingAssignment = $teacher->subjects()->where('subject_id', $subjectId)->exists();
                if ($existingAssignment) {
                    continue; // Skip duplicate assignments
                }
                
                $assignments[] = [
                    'subject_id' => $subjectId,
                    'primary_subject' => $primarySubjectId == $subjectId,
                    'assigned_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now()
                ];
            }

            if (!empty($assignments)) {
                $teacher->subjects()->attach($assignments);
            }

            DB::commit();

            // Load teacher with subjects for response
            $teacher->load(['subjects' => function($query) {
                $query->withPivot('primary_subject', 'assigned_at');
            }]);

            return $this->successResponse([
                'teacher' => $teacher,
                'assigned_count' => count($assignments),
                'message' => count($assignments) > 0 ? 'Subjects assigned successfully' : 'No new subjects were assigned'
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return $this->errorResponse('Failed to assign subjects: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Remove subject assignments from a teacher
     */
    public function removeSubjects(Request $request, User $teacher): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'subject_ids' => 'required|array',
            'subject_ids.*' => 'integer|exists:subjects,id'
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors(), 422);
        }

        // Ensure the user is a teacher
        if ($teacher->role !== 'teacher') {
            return $this->errorResponse('User is not a teacher', 400);
        }

        // School isolation check
        $currentUser = $request->user();
        if ($currentUser->isSchoolAdmin() && $teacher->school_id !== $currentUser->school_id) {
            return $this->errorResponse('Cannot modify subjects for teachers from other schools', 403);
        }

        DB::beginTransaction();
        try {
            $detachedCount = $teacher->subjects()->detach($request->subject_ids);
            
            DB::commit();

            // Load teacher with remaining subjects
            $teacher->load(['subjects' => function($query) {
                $query->withPivot('primary_subject', 'assigned_at');
            }]);

            return $this->successResponse([
                'teacher' => $teacher,
                'removed_count' => $detachedCount,
                'message' => "Removed {$detachedCount} subject assignment(s) successfully"
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return $this->errorResponse('Failed to remove subject assignments: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get all subjects assigned to a teacher
     */
    public function getTeacherSubjects(Request $request, User $teacher): JsonResponse
    {
        // Ensure the user is a teacher
        if ($teacher->role !== 'teacher') {
            return $this->errorResponse('User is not a teacher', 400);
        }

        // School isolation check
        $currentUser = $request->user();
        if ($currentUser->isSchoolAdmin() && $teacher->school_id !== $currentUser->school_id) {
            return $this->errorResponse('Cannot view subjects for teachers from other schools', 403);
        }

        $teacher->load(['subjects' => function($query) {
            $query->withPivot('primary_subject', 'assigned_at')
                  ->orderBy('pivot_primary_subject', 'desc')
                  ->orderBy('subject_name');
        }]);

        $subjects = $teacher->subjects->map(function($subject) {
            return [
                'id' => $subject->id,
                'subject_code' => $subject->subject_code,
                'subject_name' => $subject->subject_name,
                'description' => $subject->description,
                'credit_hours' => $subject->credit_hours,
                'department' => $subject->department,
                'is_core' => $subject->is_core,
                'primary_subject' => $subject->pivot->primary_subject,
                'assigned_at' => $subject->pivot->assigned_at,
            ];
        });

        return $this->successResponse([
            'teacher' => [
                'id' => $teacher->id,
                'first_name' => $teacher->first_name,
                'last_name' => $teacher->last_name,
                'email' => $teacher->email,
                'school_id' => $teacher->school_id
            ],
            'subjects' => $subjects,
            'total_subjects' => $subjects->count(),
            'primary_subjects_count' => $subjects->where('primary_subject', true)->count()
        ]);
    }

    /**
     * Get all teachers assigned to a subject
     */
    public function getSubjectTeachers(Request $request, Subject $subject): JsonResponse
    {
        // School isolation check for subjects might be needed here if subjects have school_id
        // For now, we'll load teachers and filter by school access
        $currentUser = $request->user();
        
        $teachersQuery = $subject->teachers()
            ->withPivot('primary_subject', 'assigned_at')
            ->orderBy('pivot_primary_subject', 'desc')
            ->orderBy('first_name');

        // Apply school isolation for non-super admins
        if ($currentUser->isSchoolAdmin()) {
            $teachersQuery->where('users.school_id', $currentUser->school_id);
        }

        $teachers = $teachersQuery->get()->map(function($teacher) {
            return [
                'id' => $teacher->id,
                'first_name' => $teacher->first_name,
                'last_name' => $teacher->last_name,
                'email' => $teacher->email,
                'school_id' => $teacher->school_id,
                'department_id' => $teacher->department_id,
                'primary_subject' => $teacher->pivot->primary_subject,
                'assigned_at' => $teacher->pivot->assigned_at,
            ];
        });

        return $this->successResponse([
            'subject' => [
                'id' => $subject->id,
                'subject_code' => $subject->subject_code,
                'subject_name' => $subject->subject_name,
                'description' => $subject->description,
                'department' => $subject->department
            ],
            'teachers' => $teachers,
            'total_teachers' => $teachers->count(),
            'primary_teachers_count' => $teachers->where('primary_subject', true)->count()
        ]);
    }

    /**
     * Bulk assign subjects to multiple teachers
     */
    public function bulkAssignSubjects(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'assignments' => 'required|array',
            'assignments.*.teacher_id' => 'required|integer|exists:users,id',
            'assignments.*.subject_ids' => 'required|array',
            'assignments.*.subject_ids.*' => 'integer|exists:subjects,id',
            'assignments.*.primary_subject_id' => 'nullable|integer'
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors(), 422);
        }

        $currentUser = $request->user();
        $successfulAssignments = 0;
        $errors = [];

        DB::beginTransaction();
        try {
            foreach ($request->assignments as $assignment) {
                $teacher = User::find($assignment['teacher_id']);
                
                // Validate teacher
                if (!$teacher || $teacher->role !== 'teacher') {
                    $errors[] = "User ID {$assignment['teacher_id']} is not a valid teacher";
                    continue;
                }

                // School isolation check
                if ($currentUser->isSchoolAdmin() && $teacher->school_id !== $currentUser->school_id) {
                    $errors[] = "Cannot assign subjects to teacher {$teacher->first_name} {$teacher->last_name} from another school";
                    continue;
                }

                // Process assignments for this teacher
                $primarySubjectId = $assignment['primary_subject_id'] ?? null;
                $teacherAssignments = [];
                
                foreach ($assignment['subject_ids'] as $subjectId) {
                    // Check for duplicates
                    if (!$teacher->subjects()->where('subject_id', $subjectId)->exists()) {
                        $teacherAssignments[] = [
                            'subject_id' => $subjectId,
                            'primary_subject' => $primarySubjectId == $subjectId,
                            'assigned_at' => now(),
                            'created_at' => now(),
                            'updated_at' => now()
                        ];
                    }
                }

                if (!empty($teacherAssignments)) {
                    $teacher->subjects()->attach($teacherAssignments);
                    $successfulAssignments += count($teacherAssignments);
                }
            }

            DB::commit();

            return $this->successResponse([
                'successful_assignments' => $successfulAssignments,
                'errors' => $errors,
                'message' => "Successfully assigned {$successfulAssignments} subject-teacher relationships"
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return $this->errorResponse('Bulk assignment failed: ' . $e->getMessage(), 500);
        }
    }
}
