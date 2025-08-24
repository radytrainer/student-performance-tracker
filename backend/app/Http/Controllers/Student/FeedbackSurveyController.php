<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\FormAssignment;
use App\Models\FormResponse;
use App\Models\FeedbackForm;
use App\Models\StudentClass;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class FeedbackSurveyController extends Controller
{
    /**
     * Get all surveys assigned to the authenticated student
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $studentId = $request->user()->id;
            
            // Get student's current classes
            $studentClasses = StudentClass::where('student_id', $studentId)
                ->where('status', 'active')
                ->pluck('class_id');

            // Get all active form assignments for student's classes
            $assignments = FormAssignment::active()
                ->whereIn('class_id', $studentClasses)
                ->with([
                    'feedbackForm' => function ($query) {
                        $query->active();
                    },
                    'assignedClass',
                    'responses' => function ($query) use ($studentId) {
                        $query->where('student_id', $studentId);
                    }
                ])
                ->whereHas('feedbackForm', function ($query) {
                    $query->active();
                })
                ->orderBy('created_at', 'desc')
                ->get();

            // Add completion status and format data
            $surveys = $assignments->map(function ($assignment) use ($studentId) {
                $isCompleted = $assignment->responses->count() > 0;
                $response = $assignment->responses->first();

                return [
                    'id' => $assignment->id,
                    'title' => $assignment->feedbackForm->title,
                    'description' => $assignment->feedbackForm->description,
                    'survey_type' => $assignment->feedbackForm->survey_type,
                    'google_form_url' => $assignment->feedbackForm->google_form_url,
                    'class_name' => $assignment->assignedClass->class_name,
                    'due_date' => $assignment->due_date,
                    'instructions' => $assignment->instructions,
                    'is_completed' => $isCompleted,
                    'completed_at' => $response ? $response->submitted_at : null,
                    'average_score' => $response ? $response->average_score : null,
                    'created_at' => $assignment->created_at,
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $surveys
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load surveys',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get a specific survey details for the student
     */
    public function show(Request $request, $assignmentId): JsonResponse
    {
        try {
            $studentId = $request->user()->id;
            
            // Get student's current classes
            $studentClasses = StudentClass::where('student_id', $studentId)
                ->where('status', 'active')
                ->pluck('class_id');

            $assignment = FormAssignment::active()
                ->whereIn('class_id', $studentClasses)
                ->with([
                    'feedbackForm',
                    'assignedClass',
                    'responses' => function ($query) use ($studentId) {
                        $query->where('student_id', $studentId);
                    }
                ])
                ->findOrFail($assignmentId);

            $isCompleted = $assignment->responses->count() > 0;
            $response = $assignment->responses->first();

            $surveyData = [
                'id' => $assignment->id,
                'title' => $assignment->feedbackForm->title,
                'description' => $assignment->feedbackForm->description,
                'survey_type' => $assignment->feedbackForm->survey_type,
                'google_form_url' => $assignment->feedbackForm->google_form_url,
                'google_form_id' => $assignment->feedbackForm->google_form_id,
                'class_name' => $assignment->assignedClass->class_name,
                'due_date' => $assignment->due_date,
                'instructions' => $assignment->instructions,
                'is_completed' => $isCompleted,
                'completed_at' => $response ? $response->submitted_at : null,
                'response_data' => $response ? $response->response_data : null,
                'average_score' => $response ? $response->average_score : null,
                'created_at' => $assignment->created_at,
            ];

            return response()->json([
                'success' => true,
                'data' => $surveyData
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Survey not found or not accessible',
            ], 404);
        }
    }

    /**
     * Mark survey as completed and store response data
     */
    public function markCompleted(Request $request, $assignmentId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'google_response_id' => 'nullable|string|max:255',
            'response_data' => 'required|array|min:1',
            'response_data.question_1' => 'required|integer|between:1,10',
            'response_data.question_2' => 'required|integer|between:1,10',
            'response_data.question_3' => 'required|integer|between:1,10',
            'response_data.question_4' => 'required|integer|between:1,10',
            'response_data.question_5' => 'required|integer|between:1,10',
            'submitted_at' => 'required|date',
            'time_spent' => 'nullable|integer|min:0',
            'completion_method' => 'nullable|string|in:manual,google_form',
        ], [
            'response_data.question_*.required' => 'All questions must be answered',
            'response_data.question_*.integer' => 'Response must be a number',
            'response_data.question_*.between' => 'Response must be between 1 and 10',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $studentId = $request->user()->id;
            
            // Verify student has access to this assignment
            $studentClasses = StudentClass::where('student_id', $studentId)
                ->where('status', 'active')
                ->pluck('class_id');

            $assignment = FormAssignment::active()
                ->whereIn('class_id', $studentClasses)
                ->with('feedbackForm')
                ->findOrFail($assignmentId);

            // Check if survey is still active and not past due
            if ($assignment->due_date && $assignment->due_date < now()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Survey submission deadline has passed'
                ], 410);
            }

            // Check if already completed
            $existingResponse = FormResponse::where('feedback_form_id', $assignment->feedback_form_id)
                ->where('student_id', $studentId)
                ->where('form_assignment_id', $assignmentId)
                ->first();

            if ($existingResponse) {
                return response()->json([
                    'success' => false,
                    'message' => 'Survey already completed',
                    'data' => [
                        'completed_at' => $existingResponse->submitted_at,
                        'average_score' => $existingResponse->average_score
                    ]
                ], 409);
            }

            // Validate response data structure
            $responseData = $request->response_data;
            $requiredQuestions = ['question_1', 'question_2', 'question_3', 'question_4', 'question_5'];
            foreach ($requiredQuestions as $question) {
                if (!isset($responseData[$question]) || $responseData[$question] < 1 || $responseData[$question] > 10) {
                    return response()->json([
                        'success' => false,
                        'message' => "Invalid response for {$question}. Must be between 1 and 10."
                    ], 422);
                }
            }

            // Create response record with enhanced data
            $response = FormResponse::create([
                'feedback_form_id' => $assignment->feedback_form_id,
                'student_id' => $studentId,
                'form_assignment_id' => $assignmentId,
                'google_response_id' => $request->google_response_id,
                'response_data' => array_merge($responseData, [
                    'time_spent' => $request->time_spent,
                    'completion_method' => $request->completion_method ?? 'manual',
                    'submitted_via' => 'web_interface',
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent()
                ]),
                'submitted_at' => $request->submitted_at,
            ]);

            // Calculate scores with enhanced validation
            $response->calculateScores();
            $response->save();

            // Log completion for analytics
            Log::info('Survey completed', [
            'student_id' => $studentId,
            'assignment_id' => $assignmentId,
            'form_id' => $assignment->feedback_form_id,
            'average_score' => $response->average_score,
            'completion_method' => $request->completion_method ?? 'manual'
            ]);

            // Broadcast realtime event to the teacher who owns the form
            try {
            $teacherId = (int) ($assignment->feedbackForm->created_by_teacher_id ?? $assignment->assigned_by_teacher_id);
            if ($teacherId) {
            event(new \App\Events\SurveyCompleted($teacherId, [
                'assignment_id' => $assignmentId,
                'form_id' => $assignment->feedback_form_id,
                'form_title' => $assignment->feedbackForm->title,
                'student_id' => $studentId,
                'average_score' => round($response->average_score, 2),
                'submitted_at' => $response->submitted_at,
                ]));
                }
            } catch (\Throwable $e) {}
 
            return response()->json([
                'success' => true,
                'message' => 'Survey completion recorded successfully',
                'data' => [
                    'id' => $response->id,
                    'average_score' => round($response->average_score, 2),
                    'score_total' => $response->score_total,
                    'submitted_at' => $response->submitted_at,
                    'completion_status' => 'completed',
                    'form_title' => $assignment->feedbackForm->title,
                    'class_name' => $assignment->assignedClass->class_name ?? 'N/A'
                ]
            ], 201);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Survey assignment not found or not accessible'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Survey submission error', [
                'student_id' => $request->user()->id ?? null,
                'assignment_id' => $assignmentId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to record survey completion',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Get student's survey completion statistics
     */
    public function getStats(Request $request): JsonResponse
    {
        try {
            $studentId = $request->user()->id;
            
            // Get student's current classes
            $studentClasses = StudentClass::where('student_id', $studentId)
                ->where('status', 'active')
                ->pluck('class_id');

            // Get all assignments for student
            $totalAssignments = FormAssignment::active()
                ->whereIn('class_id', $studentClasses)
                ->whereHas('feedbackForm', function ($query) {
                    $query->active();
                })
                ->count();

            // Get completed assignments
            $completedAssignments = FormResponse::where('student_id', $studentId)
                ->whereHas('formAssignment', function ($query) use ($studentClasses) {
                    $query->active()->whereIn('class_id', $studentClasses);
                })
                ->count();

            // Get average score across all completed surveys
            $averageScore = FormResponse::where('student_id', $studentId)
                ->whereHas('formAssignment', function ($query) use ($studentClasses) {
                    $query->active()->whereIn('class_id', $studentClasses);
                })
                ->avg('average_score');

            // Recent completed surveys
            $recentSurveys = FormResponse::where('student_id', $studentId)
                ->whereHas('formAssignment', function ($query) use ($studentClasses) {
                    $query->active()->whereIn('class_id', $studentClasses);
                })
                ->with(['feedbackForm', 'formAssignment.assignedClass'])
                ->orderBy('submitted_at', 'desc')
                ->limit(5)
                ->get()
                ->map(function ($response) {
                    return [
                        'title' => $response->feedbackForm->title,
                        'class_name' => $response->formAssignment->assignedClass->class_name,
                        'average_score' => $response->average_score,
                        'submitted_at' => $response->submitted_at,
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => [
                    'total_assignments' => $totalAssignments,
                    'completed_assignments' => $completedAssignments,
                    'completion_rate' => $totalAssignments > 0 ? round(($completedAssignments / $totalAssignments) * 100, 2) : 0,
                    'average_score' => $averageScore ? round($averageScore, 2) : null,
                    'recent_surveys' => $recentSurveys,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
