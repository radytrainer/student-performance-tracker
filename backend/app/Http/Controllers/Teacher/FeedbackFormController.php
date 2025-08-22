<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\FeedbackForm;
use App\Models\FormAssignment;
use App\Models\IndividualFormAssignment;
use App\Models\FormResponse;
use App\Models\Classes;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class FeedbackFormController extends Controller
{
    /**
     * Display a listing of feedback forms created by the authenticated teacher.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $forms = FeedbackForm::byTeacher($request->user()->id)
                ->with(['assignments.assignedClass', 'responses'])
                ->orderBy('created_at', 'desc')
                ->get();

            // Add response counts
            $forms->each(function ($form) {
                $form->total_responses = $form->responses->count();
                $form->average_score = $form->responses->avg('average_score');
            });

            return response()->json([
                'success' => true,
                'data' => $forms
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load feedback forms',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created feedback form.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'survey_type' => 'required|in:weekly,monthly',
            'google_form_url' => 'required|url',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after:start_date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Extract Google Form ID from URL
            $googleFormId = $this->extractGoogleFormId($request->google_form_url);

            $form = FeedbackForm::create([
                'title' => $request->title,
                'description' => $request->description,
                'survey_type' => $request->survey_type,
                'google_form_url' => $request->google_form_url,
                'google_form_id' => $googleFormId,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'created_by_teacher_id' => $request->user()->id,
                'is_active' => true,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Feedback form created successfully',
                'data' => $form
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create feedback form',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified feedback form.
     */
    public function show(Request $request, $id): JsonResponse
    {
        try {
            $form = FeedbackForm::byTeacher($request->user()->id)
                ->with(['assignments.assignedClass', 'responses.student'])
                ->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $form
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Feedback form not found',
            ], 404);
        }
    }

    /**
     * Update the specified feedback form.
     */
    public function update(Request $request, $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|nullable|string',
            'is_active' => 'sometimes|boolean',
            'google_form_url' => 'sometimes|required|url',
            'start_date' => 'sometimes|nullable|date',
            'end_date' => 'sometimes|nullable|date|after:start_date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $form = FeedbackForm::byTeacher($request->user()->id)->findOrFail($id);
            
            $updateData = $request->only([
                'title', 'description', 'is_active', 'start_date', 'end_date'
            ]);

            if ($request->has('google_form_url')) {
                $updateData['google_form_url'] = $request->google_form_url;
                $updateData['google_form_id'] = $this->extractGoogleFormId($request->google_form_url);
            }

            $form->update($updateData);

            return response()->json([
                'success' => true,
                'message' => 'Feedback form updated successfully',
                'data' => $form
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update feedback form',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified feedback form.
     */
    public function destroy(Request $request, $id): JsonResponse
    {
        try {
            $form = FeedbackForm::byTeacher($request->user()->id)->findOrFail($id);
            $form->delete();

            return response()->json([
                'success' => true,
                'message' => 'Feedback form deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete feedback form',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get teacher's classes for assignment
     */
    public function getMyClasses(Request $request): JsonResponse
    {
        try {
            $teacherId = $request->user()->id;
            
            // For now, allow teachers to assign surveys to all classes
            // In production, you might want to restrict this based on teacher assignments
            $classes = Classes::all();
            
            // Add student count for each class
            foreach ($classes as $class) {
                $class->students_count = \App\Models\StudentClass::where('class_id', $class->id)
                    ->where('status', 'active')
                    ->count();
            }

            return response()->json([
                'success' => true,
                'data' => $classes
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load classes',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Assign form to classes
     */
    public function assignToClasses(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'assignments' => 'required|array',
            'assignments.*.feedback_form_id' => 'required|exists:feedback_forms,id',
            'assignments.*.class_id' => 'required|exists:classes,id',
            'assignments.*.due_date' => 'nullable|date',
            'assignments.*.instructions' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $assignments = [];
            
            foreach ($request->assignments as $assignmentData) {
                // Verify teacher owns the form
                $form = FeedbackForm::byTeacher($request->user()->id)
                    ->findOrFail($assignmentData['feedback_form_id']);

                $assignment = FormAssignment::create([
                    'feedback_form_id' => $assignmentData['feedback_form_id'],
                    'class_id' => $assignmentData['class_id'],
                    'assigned_by_teacher_id' => $request->user()->id,
                    'due_date' => $assignmentData['due_date'] ?? null,
                    'instructions' => $assignmentData['instructions'] ?? null,
                    'is_active' => true,
                ]);

                $assignments[] = $assignment;
            }

            return response()->json([
                'success' => true,
                'message' => 'Form assigned to classes successfully',
                'data' => $assignments
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to assign form to classes',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get available users for individual assignment (excluding admins)
     */
    public function getAvailableUsers(Request $request): JsonResponse
    {
        try {
            // Get all users except admins
            $users = User::where('role', '!=', 'admin')
                ->where('is_active', true)
                ->select('id', 'username', 'first_name', 'last_name', 'email', 'role')
                ->orderBy('role')
                ->orderBy('first_name')
                ->get();

            // Group users by role for better organization
            $groupedUsers = [
                'teachers' => $users->where('role', 'teacher')->values(),
                'students' => $users->where('role', 'student')->values(),
            ];

            return response()->json([
                'success' => true,
                'data' => $groupedUsers
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load users',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Assign form to individual users
     */
    public function assignToUsers(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'assignments' => 'required|array',
            'assignments.*.feedback_form_id' => 'required|exists:feedback_forms,id',
            'assignments.*.user_id' => 'required|exists:users,id',
            'assignments.*.due_date' => 'nullable|date',
            'assignments.*.instructions' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $assignments = [];
            
            foreach ($request->assignments as $assignmentData) {
                // Verify teacher owns the form
                $form = FeedbackForm::byTeacher($request->user()->id)
                    ->findOrFail($assignmentData['feedback_form_id']);

                // Verify the user being assigned is not an admin
                $assignedUser = User::where('id', $assignmentData['user_id'])
                    ->where('role', '!=', 'admin')
                    ->where('is_active', true)
                    ->firstOrFail();

                // Skip if already assigned (respect unique constraint)
                $existing = IndividualFormAssignment::where('feedback_form_id', $assignmentData['feedback_form_id'])
                    ->where('assigned_to_user_id', $assignmentData['user_id'])
                    ->first();
                if ($existing) {
                    continue;
                }

                $assignment = IndividualFormAssignment::create([
                    'feedback_form_id' => $assignmentData['feedback_form_id'],
                    'assigned_to_user_id' => $assignmentData['user_id'],
                    'assigned_by_teacher_id' => $request->user()->id,
                    'due_date' => $assignmentData['due_date'] ?? null,
                    'instructions' => $assignmentData['instructions'] ?? null,
                    'is_active' => true,
                ]);

                $assignment->load(['assignedToUser', 'feedbackForm']);
                $assignments[] = $assignment;
            }

            return response()->json([
                'success' => true,
                'message' => 'Form assigned to users successfully',
                'data' => $assignments
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to assign form to users',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get individual assignments for a form
     */
    public function getIndividualAssignments(Request $request, $formId): JsonResponse
    {
        try {
            // Verify teacher owns the form
            $form = FeedbackForm::byTeacher($request->user()->id)->findOrFail($formId);

            $assignments = IndividualFormAssignment::where('feedback_form_id', $formId)
                ->with(['assignedToUser', 'feedbackForm'])
                ->active()
                ->get();

            return response()->json([
                'success' => true,
                'data' => $assignments
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load individual assignments',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get analytics for a specific feedback form
     */
    public function getFormAnalytics(Request $request, $formId): JsonResponse
    {
        try {
            // Verify teacher owns the form
            $form = FeedbackForm::byTeacher($request->user()->id)->findOrFail($formId);

            // Get all assignments for this form
            $assignments = FormAssignment::where('feedback_form_id', $formId)
                ->with(['assignedClass', 'responses'])
                ->get();

            // Get all responses for this form
            $responses = FormResponse::where('feedback_form_id', $formId)
                ->with(['student', 'formAssignment.assignedClass'])
                ->get();

            // Calculate analytics
            $analytics = [
                'form' => [
                    'id' => $form->id,
                    'title' => $form->title,
                    'survey_type' => $form->survey_type,
                    'created_at' => $form->created_at,
                ],
                'assignments' => [
                    'total' => $assignments->count(),
                    'classes' => $assignments->map(function($assignment) {
                        return [
                            'class_name' => $assignment->assignedClass->class_name,
                            'due_date' => $assignment->due_date,
                            'responses_count' => $assignment->responses->count()
                        ];
                    })
                ],
                'responses' => [
                    'total' => $responses->count(),
                    'completion_rate' => $assignments->count() > 0 ? 
                        round(($responses->count() / $assignments->sum(function($a) { return $a->assignedClass->students->count(); })) * 100, 2) : 0,
                    'average_score' => round($responses->avg('average_score'), 2),
                    'score_distribution' => $this->getScoreDistribution($responses),
                    'question_averages' => $this->getQuestionAverages($responses),
                    'completion_trend' => $this->getCompletionTrend($responses),
                    'class_breakdown' => $this->getClassBreakdown($responses)
                ],
                'insights' => $this->generateInsights($responses, $assignments)
            ];

            return response()->json([
                'success' => true,
                'data' => $analytics
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load form analytics',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get overall teacher survey analytics
     */
    public function getTeacherAnalytics(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }
            
            $teacherId = $user->id;

            // Get all forms by teacher
            $forms = FeedbackForm::byTeacher($teacherId)->with('responses')->get();
            $totalResponses = $forms->sum(function($form) { return $form->responses->count(); });

            // Get response data for the last 30 days
            $recentResponses = FormResponse::whereHas('feedbackForm', function($query) use ($teacherId) {
                $query->where('created_by_teacher_id', $teacherId);
            })->where('submitted_at', '>=', now()->subDays(30))->get();

            $analytics = [
                'overview' => [
                    'total_forms' => $forms->count(),
                    'active_forms' => $forms->where('is_active', true)->count(),
                    'total_responses' => $totalResponses,
                    'average_score' => $totalResponses > 0 ? round($forms->flatMap->responses->avg('average_score') ?? 0, 2) : 0,
                    'recent_responses' => $recentResponses->count()
                ],
                'form_performance' => $forms->map(function($form) {
                    return [
                        'id' => $form->id,
                        'title' => $form->title,
                        'survey_type' => $form->survey_type,
                        'responses_count' => $form->responses->count(),
                        'average_score' => $form->responses->count() > 0 ? round($form->responses->avg('average_score') ?? 0, 2) : 0,
                        'created_at' => $form->created_at
                    ];
                })->sortByDesc('responses_count')->values(),
                'monthly_trend' => $this->getMonthlyTrend($teacherId),
                'score_trends' => $this->getScoreTrends($teacherId),
                'engagement_metrics' => $this->getEngagementMetrics($teacherId)
            ];

            return response()->json([
                'success' => true,
                'data' => $analytics
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load teacher analytics',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Helper method to get score distribution
     */
    private function getScoreDistribution($responses)
    {
        $distribution = [];
        for ($i = 1; $i <= 10; $i++) {
            $count = $responses->filter(function($response) use ($i) {
                return floor($response->average_score) == $i;
            })->count();
            $distribution[$i] = $count;
        }
        return $distribution;
    }

    /**
     * Helper method to get question averages
     */
    private function getQuestionAverages($responses)
    {
        $questionAverages = [];
        for ($i = 1; $i <= 5; $i++) {
            $values = $responses->map(function($response) use ($i) {
                return $response->response_data["question_{$i}"] ?? null;
            })->filter()->values();
            
            $questionAverages["question_{$i}"] = [
                'average' => $values->count() > 0 ? round($values->avg() ?? 0, 2) : 0,
                'count' => $values->count()
            ];
        }
        return $questionAverages;
    }

    /**
     * Helper method to get completion trend
     */
    private function getCompletionTrend($responses)
    {
        return $responses->groupBy(function($response) {
            return $response->submitted_at->format('Y-m-d');
        })->map(function($dayResponses, $date) {
            return [
                'date' => $date,
                'count' => $dayResponses->count(),
                'average_score' => $dayResponses->count() > 0 ? round($dayResponses->avg('average_score') ?? 0, 2) : 0
            ];
        })->sortKeys()->values();
    }

    /**
     * Helper method to get class breakdown
     */
    private function getClassBreakdown($responses)
    {
        return $responses->groupBy(function($response) {
            return $response->formAssignment->assignedClass->class_name;
        })->map(function($classResponses, $className) {
            return [
                'class_name' => $className,
                'responses_count' => $classResponses->count(),
                'average_score' => $classResponses->count() > 0 ? round($classResponses->avg('average_score') ?? 0, 2) : 0,
                'completion_rate' => round(($classResponses->count() / max($classResponses->count(), 1)) * 100, 2)
            ];
        })->values();
    }

    /**
     * Helper method to get monthly trend
     */
    private function getMonthlyTrend($teacherId)
    {
        return FormResponse::whereHas('feedbackForm', function($query) use ($teacherId) {
            $query->where('created_by_teacher_id', $teacherId);
        })
        ->where('submitted_at', '>=', now()->subMonths(6))
        ->selectRaw('DATE_FORMAT(submitted_at, "%Y-%m") as month')
        ->selectRaw('COUNT(*) as responses_count')
        ->selectRaw('AVG(average_score) as average_score')
        ->groupBy('month')
        ->orderBy('month')
        ->get()
        ->map(function($item) {
            return [
                'month' => $item->month,
                'responses_count' => $item->responses_count,
                'average_score' => round($item->average_score, 2)
            ];
        });
    }

    /**
     * Helper method to get score trends
     */
    private function getScoreTrends($teacherId)
    {
        return FormResponse::whereHas('feedbackForm', function($query) use ($teacherId) {
            $query->where('created_by_teacher_id', $teacherId);
        })
        ->where('submitted_at', '>=', now()->subDays(30))
        ->selectRaw('DATE(submitted_at) as date')
        ->selectRaw('AVG(average_score) as average_score')
        ->groupBy('date')
        ->orderBy('date')
        ->get()
        ->map(function($item) {
            return [
                'date' => $item->date,
                'average_score' => round($item->average_score, 2)
            ];
        });
    }

    /**
     * Helper method to get engagement metrics
     */
    private function getEngagementMetrics($teacherId)
    {
        $responses = FormResponse::whereHas('feedbackForm', function($query) use ($teacherId) {
            $query->where('created_by_teacher_id', $teacherId);
        })->get();

        $responseTime = $responses->map(function($response) {
            return $response->response_data['time_spent'] ?? 0;
        })->filter();

        return [
            'average_completion_time' => $responseTime->count() > 0 ? round($responseTime->avg() ?? 0, 2) : 0,
            'response_quality' => [
                'complete_responses' => $responses->where('score_total', '>=', 25)->count(),
                'partial_responses' => $responses->where('score_total', '<', 25)->count()
            ],
            'submission_methods' => $responses->groupBy(function($response) {
                return $response->response_data['completion_method'] ?? 'unknown';
            })->map->count()
        ];
    }

    /**
     * Helper method to generate insights
     */
    private function generateInsights($responses, $assignments)
    {
        $insights = [];
        
        if ($responses->count() > 0) {
            $avgScore = $responses->avg('average_score');
            
            if ($avgScore >= 8) {
                $insights[] = [
                    'type' => 'positive',
                    'title' => 'Excellent Performance',
                    'message' => 'Your teaching is highly rated with an average score of ' . round($avgScore, 1) . '/10'
                ];
            } elseif ($avgScore >= 6) {
                $insights[] = [
                    'type' => 'neutral',
                    'title' => 'Good Performance',
                    'message' => 'Your teaching is well-received with room for improvement. Average score: ' . round($avgScore, 1) . '/10'
                ];
            } else {
                $insights[] = [
                    'type' => 'warning',
                    'title' => 'Improvement Needed',
                    'message' => 'Consider reviewing feedback to improve teaching effectiveness. Average score: ' . round($avgScore, 1) . '/10'
                ];
            }

            // Completion rate insight
            $completionRate = $assignments->count() > 0 ? 
                ($responses->count() / $assignments->sum(function($a) { return $a->assignedClass->students->count(); })) * 100 : 0;
            
            if ($completionRate < 50) {
                $insights[] = [
                    'type' => 'warning',
                    'title' => 'Low Response Rate',
                    'message' => 'Only ' . round($completionRate, 1) . '% of students have completed the survey. Consider sending reminders.'
                ];
            }
        }

        return $insights;
    }

    /**
     * Bulk assign surveys to multiple classes
     */
    public function bulkAssignSurveys(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'survey_id' => 'required|integer|exists:feedback_forms,id',
            'class_ids' => 'required|array|min:1',
            'class_ids.*' => 'integer|exists:classes,id',
            'due_date' => 'nullable|date|after:today',
            'instructions' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $teacherId = $request->user()->id;
            $surveyId = $request->survey_id;
            $classIds = $request->class_ids;

            // Verify teacher owns the survey
            $survey = FeedbackForm::byTeacher($teacherId)->findOrFail($surveyId);

            $assignments = [];
            foreach ($classIds as $classId) {
                // Check if assignment already exists
                $existingAssignment = FormAssignment::where('feedback_form_id', $surveyId)
                    ->where('class_id', $classId)
                    ->first();

                if (!$existingAssignment) {
                    $assignment = FormAssignment::create([
                        'feedback_form_id' => $surveyId,
                        'class_id' => $classId,
                        'assigned_by_teacher_id' => $teacherId,
                        'due_date' => $request->due_date,
                        'instructions' => $request->instructions,
                        'is_active' => true,
                    ]);

                    $assignment->load(['assignedClass', 'feedbackForm']);
                    $assignments[] = $assignment;
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Survey assigned to ' . count($assignments) . ' classes successfully',
                'data' => $assignments
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to bulk assign surveys',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get survey templates
     */
    public function getSurveyTemplates(Request $request): JsonResponse
    {
        try {
            $templates = [
                [
                    'id' => 'weekly_feedback',
                    'name' => 'Weekly Feedback Template',
                    'description' => 'Standard weekly feedback survey for regular class assessment',
                    'type' => 'weekly',
                    'questions' => [
                        'How would you rate the overall teaching quality this week?',
                        'How clear and understandable were the lesson explanations?',
                        'How effectively did the teacher engage with students?',
                        'How well did the teacher provide feedback on your work?',
                        'How would you rate your overall learning experience this week?'
                    ]
                ],
                [
                    'id' => 'monthly_comprehensive',
                    'name' => 'Monthly Comprehensive Review',
                    'description' => 'Comprehensive monthly review covering all aspects of teaching and learning',
                    'type' => 'monthly',
                    'questions' => [
                        'How would you rate the course content delivery over the past month?',
                        'How satisfied are you with the pace of lessons?',
                        'How effective are the teaching methods used in class?',
                        'How well does the teacher respond to student questions and concerns?',
                        'How would you rate your overall progress this month?'
                    ]
                ],
                [
                    'id' => 'course_evaluation',
                    'name' => 'Course Evaluation Template',
                    'description' => 'End-of-course evaluation for comprehensive feedback',
                    'type' => 'monthly',
                    'questions' => [
                        'How would you rate the overall course structure and organization?',
                        'How effective were the teaching materials and resources?',
                        'How well did the course meet your learning expectations?',
                        'How would you rate the teacher\'s communication and availability?',
                        'How likely are you to recommend this course to other students?'
                    ]
                ]
            ];

            return response()->json([
                'success' => true,
                'data' => $templates
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load survey templates',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create survey from template
     */
    public function createFromTemplate(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'template_id' => 'required|string',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'google_form_url' => 'required|url',
            'survey_type' => 'required|in:weekly,monthly',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after:start_date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $googleFormId = $this->extractGoogleFormId($request->google_form_url);

            $form = FeedbackForm::create([
                'title' => $request->title,
                'description' => $request->description,
                'survey_type' => $request->survey_type,
                'google_form_url' => $request->google_form_url,
                'google_form_id' => $googleFormId,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'created_by_teacher_id' => $request->user()->id,
                'is_active' => true,
                'template_used' => $request->template_id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Survey created from template successfully',
                'data' => $form
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create survey from template',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Schedule survey for automatic assignment
     */
    public function scheduleSurvey(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'survey_id' => 'required|integer|exists:feedback_forms,id',
            'class_ids' => 'required|array|min:1',
            'class_ids.*' => 'integer|exists:classes,id',
            'schedule_date' => 'required|date|after:now',
            'due_date' => 'required|date|after:schedule_date',
            'instructions' => 'nullable|string|max:1000',
            'auto_remind' => 'boolean',
            'remind_days_before' => 'nullable|integer|min:1|max:7',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $teacherId = $request->user()->id;
            
            // Verify teacher owns the survey
            $survey = FeedbackForm::byTeacher($teacherId)->findOrFail($request->survey_id);

            // Create scheduled assignments (inactive until schedule date)
            $scheduledAssignments = [];
            foreach ($request->class_ids as $classId) {
                $assignment = FormAssignment::create([
                    'feedback_form_id' => $request->survey_id,
                    'class_id' => $classId,
                    'assigned_by_teacher_id' => $teacherId,
                    'due_date' => $request->due_date,
                    'instructions' => $request->instructions,
                    'is_active' => false, // Will be activated on schedule date
                    'scheduled_date' => $request->schedule_date,
                    'auto_remind' => $request->auto_remind ?? false,
                    'remind_days_before' => $request->remind_days_before ?? 1,
                ]);

                $assignment->load(['assignedClass', 'feedbackForm']);
                $scheduledAssignments[] = $assignment;
            }

            return response()->json([
                'success' => true,
                'message' => 'Survey scheduled for ' . count($scheduledAssignments) . ' classes',
                'data' => [
                    'scheduled_assignments' => $scheduledAssignments,
                    'schedule_date' => $request->schedule_date,
                    'due_date' => $request->due_date
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to schedule survey',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Extract Google Form ID from URL
     */
    private function extractGoogleFormId($url): ?string
    {
        if (preg_match('/\/forms\/d\/([a-zA-Z0-9-_]+)/', $url, $matches)) {
            return $matches[1];
        }
        return null;
    }
}
