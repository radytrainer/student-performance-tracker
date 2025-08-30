<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StudentNote;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class StudentNoteController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:sanctum']);
    }

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $q = StudentNote::query()->with(['student.user', 'teacher']);
        if ($request->filled('student_id')) {
            $q->where('student_id', (int)$request->input('student_id'));
        }
        if ($user->role === 'teacher') {
            // Teachers see only their own notes or notes about their assigned students (non-private)
            $q->where(function($sub) use ($user) {
                $sub->where('teacher_id', $user->teacher->user_id)
                    ->orWhere(function($s2) use ($user) {
                        $s2->whereIn('student_id', Student::whereIn('current_class_id', function($qq) use ($user) {
                            $qq->select('class_id')->from('class_subjects')->where('teacher_id', $user->teacher->user_id);
                        })->pluck('user_id'))
                        ->where('is_private', false);
                    });
            });
        } elseif ($user->role === 'student') {
            // Student sees non-private notes about self
            $q->where('student_id', optional($user->student)->user_id)->where('is_private', false);
        } // admin sees all
        $per = max(1, (int) $request->input('per_page', 15));
        return response()->json(['success' => true, 'data' => $q->orderByDesc('id')->paginate($per)]);
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!in_array($user->role, ['admin', 'teacher'])) return response()->json(['success' => false, 'message' => 'Forbidden'], 403);
        $validated = $request->validate([
            'student_id' => 'required|exists:students,user_id',
            'title' => 'required|string|max:255',
            'note' => 'required|string',
            'is_private' => 'boolean',
        ]);
        $validated['teacher_id'] = $user->role === 'teacher' ? optional($user->teacher)->user_id : ($request->input('teacher_id') ?: null);
        $validated['is_private'] = (bool) ($validated['is_private'] ?? false);
        $note = StudentNote::create($validated);
        return response()->json(['success' => true, 'data' => $note], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $note = StudentNote::findOrFail($id);
        if ($user->role === 'teacher' && $note->teacher_id !== optional($user->teacher)->user_id) {
            return response()->json(['success' => false, 'message' => 'Forbidden'], 403);
        }
        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'note' => 'sometimes|string',
            'is_private' => 'sometimes|boolean',
        ]);
        $note->fill($validated);
        $note->save();
        return response()->json(['success' => true, 'data' => $note]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $note = StudentNote::findOrFail($id);
        if ($user->role === 'teacher' && $note->teacher_id !== optional($user->teacher)->user_id) {
            return response()->json(['success' => false, 'message' => 'Forbidden'], 403);
        }
        $note->delete();
        return response()->json(['success' => true]);
    }
}
