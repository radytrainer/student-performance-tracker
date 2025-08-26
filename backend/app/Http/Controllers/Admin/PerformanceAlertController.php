<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PerformanceAlert;
use App\Models\Student;
use App\Models\Term;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\JsonResponse;

class PerformanceAlertController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:sanctum']);
    }

    /**
    * List alerts. Admin sees all; teacher sees alerts for students they can access; student sees their own.
    */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $query = PerformanceAlert::query()
            ->with(['student.user', 'term', 'relatedSubject', 'resolvedBy', 'createdBy'])
            ->orderByDesc('created_at');

        if ($request->filled('is_resolved')) {
            $query->where('is_resolved', filter_var($request->input('is_resolved'), FILTER_VALIDATE_BOOLEAN));
        }
        if ($request->filled('alert_type')) {
            $query->where('alert_type', $request->input('alert_type'));
        }
        if ($request->filled('severity')) {
            $query->where('severity', $request->input('severity'));
        }
        if ($request->filled('term_id')) {
            $query->where('term_id', (int)$request->input('term_id'));
        }

        // Role-based scoping
        if ($user->role === 'student' && $user->student) {
            $query->where('student_id', $user->student->user_id);
        } elseif ($user->role === 'teacher' && $user->teacher) {
            // Limit to students in teacher's classes
            $studentIds = Student::whereIn('current_class_id', function($q) use ($user) {
                $q->select('class_id')->from('class_subjects')->where('teacher_id', $user->teacher->user_id);
            })->pluck('user_id');
            $query->whereIn('student_id', $studentIds);
        } elseif ($user->role === 'admin') {
            // Admin sees all
        } else {
            return response()->json(['success' => false, 'message' => 'Forbidden'], 403);
        }

        $perPage = max(1, (int) $request->input('per_page', 15));
        $data = $query->paginate($perPage);
        return response()->json(['success' => true, 'data' => $data]);
    }

    /**
     * Create a manual alert (admin/teacher).
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!in_array($user->role, ['admin', 'teacher'])) {
            return response()->json(['success' => false, 'message' => 'Forbidden'], 403);
        }
        $validated = $request->validate([
            'student_id' => 'required|exists:students,user_id',
            'term_id' => 'nullable|exists:terms,id',
            'alert_type' => 'required|in:low_grade,attendance,behavior,improvement',
            'severity' => 'required|in:low,medium,high',
            'message' => 'required|string|max:1000',
            'related_subject_id' => 'nullable|exists:subjects,id',
        ]);

        if (empty($validated['term_id'])) {
            $validated['term_id'] = optional(Term::where('is_current', true)->first())->id;
        }
        $validated['created_by'] = $user->id;
        $validated['is_resolved'] = false;

        $alert = PerformanceAlert::create($validated);

        // Notify student
        try {
            $n = \App\Models\Notification::create([
                'user_id' => $validated['student_id'],
                'title' => 'Performance Alert',
                'message' => $validated['message'],
                'type' => 'warning',
                'is_read' => false,
                'sent_at' => now(),
            ]);
            event(new \App\Events\NotificationCreated($n));
        } catch (\Throwable $e) {}

        return response()->json(['success' => true, 'data' => $alert], 201);
    }

    /**
     * Resolve or update an alert (admin/teacher).
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        if (!in_array($user->role, ['admin', 'teacher'])) {
            return response()->json(['success' => false, 'message' => 'Forbidden'], 403);
        }
        $alert = PerformanceAlert::findOrFail($id);

        $validated = $request->validate([
            'is_resolved' => 'nullable|boolean',
            'message' => 'nullable|string|max:1000',
            'severity' => 'nullable|in:low,medium,high',
        ]);

        if (array_key_exists('is_resolved', $validated)) {
            $alert->is_resolved = (bool)$validated['is_resolved'];
            $alert->resolved_by = $user->id;
            $alert->resolved_at = $alert->is_resolved ? now() : null;
        }
        if (array_key_exists('message', $validated)) {
            $alert->message = $validated['message'];
        }
        if (array_key_exists('severity', $validated)) {
            $alert->severity = $validated['severity'];
        }
        $alert->save();

        if ($alert->is_resolved) {
            // Notify student of resolution
            try {
                $n = \App\Models\Notification::create([
                    'user_id' => $alert->student_id,
                    'title' => 'Alert resolved',
                    'message' => 'A performance alert has been marked as resolved.',
                    'type' => 'info',
                    'is_read' => false,
                    'sent_at' => now(),
                ]);
                event(new \App\Events\NotificationCreated($n));
            } catch (\Throwable $e) {}
        }

        return response()->json(['success' => true, 'data' => $alert]);
    }
}
