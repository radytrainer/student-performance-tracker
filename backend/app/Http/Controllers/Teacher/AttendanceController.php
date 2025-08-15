<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Student;
use App\Models\Classes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Notification;

class AttendanceController extends Controller
{
    /**
     * Get all attendance records with filtering
     */
    public function index(Request $request)
    {
        try {
            $query = Attendance::with(['student.user', 'class'])
                ->orderBy('date', 'desc');

            // Apply filters
            if ($request->has('class_id') && $request->class_id) {
                $query->where('class_id', $request->class_id);
            }

            if ($request->has('student_id') && $request->student_id) {
                $query->where('student_id', $request->student_id);
            }

            if ($request->has('status') && $request->status) {
                $query->where('status', $request->status);
            }

            if ($request->has('start_date') && $request->start_date) {
                $query->where('date', '>=', $request->start_date);
            }

            if ($request->has('end_date') && $request->end_date) {
                $query->where('date', '<=', $request->end_date);
            }

            $attendances = $query->get();

            return response()->json([
                'success' => true,
                'attendances' => $attendances,
                'total' => $attendances->count()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch attendance records',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get attendance for a specific date and class
     */
    public function show($id)
    {
        try {
            // If $id is 'class-date', handle it differently
            if (strpos($id, '-') !== false) {
                list($classId, $date) = explode('-', $id);
                
                $attendances = Attendance::with(['student.user', 'class'])
                    ->where('class_id', $classId)
                    ->where('date', $date)
                    ->get();

                return response()->json([
                    'success' => true,
                    'attendances' => $attendances
                ]);
            }

            // Original behavior for single record
            $attendance = Attendance::with(['student.user', 'class'])->findOrFail($id);

            return response()->json([
                'success' => true,
                'attendance' => $attendance
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch attendance',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get teacher's classes
     */
    public function getMyClasses()
    {
        try {
            $teacherId = Auth::id();
            $classes = Classes::where('teacher_id', $teacherId)->get();

            return response()->json([
                'success' => true,
                'classes' => $classes
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch classes',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get students in a class
     */
    public function getStudentsByClass($classId)
    {
        try {
            $students = Student::with(['user'])
                ->where('current_class_id', $classId)
                ->get();

            return response()->json([
                'success' => true,
                'students' => $students
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch students',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get recent attendance records
     */
    public function getRecentAttendance()
    {
        try {
            $recent = Attendance::with(['student.user', 'class'])
                ->where('date', '>=', now()->subDays(7))
                ->orderBy('date', 'desc')
                ->limit(50)
                ->get();

            return response()->json([
                'success' => true,
                'attendances' => $recent
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch recent attendance',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store new attendance record
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'student_id' => 'required|integer|exists:students,user_id',
                'class_id' => 'required|integer|exists:classes,id',
                'date' => 'required|date|before_or_equal:today',
                'status' => 'required|in:present,absent,late',
                'notes' => 'nullable|string|max:500',
            ]);

            // Validate that student belongs to the class
            $student = Student::where('user_id', $validated['student_id'])
                ->where('current_class_id', $validated['class_id'])
                ->first();
                
            if (!$student) {
                return response()->json([
                    'success' => false,
                    'message' => 'Student does not belong to the specified class'
                ], 422);
            }

            // Check if attendance already exists for this student/date/class
            $existing = Attendance::where('student_id', $validated['student_id'])
                ->where('class_id', $validated['class_id'])
                ->where('date', $validated['date'])
                ->first();

            if ($existing) {
                $existing->update([
                    'status' => $validated['status'],
                    'notes' => $validated['notes'] ?? null
                ]);
                
                return response()->json([
                    'success' => true,
                    'message' => 'Attendance updated successfully!',
                    'attendance' => $existing->load(['student.user', 'class'])
                ], 200);
            }

            $attendance = Attendance::create($validated);

            // Create notification
            Notification::create([
                'user_id' => $validated['student_id'],
                'type' => 'attendance',
                'title' => 'Attendance Marked',
                'message' => "Your attendance has been marked as {$validated['status']} for {$validated['date']}",
                'data' => json_encode(['attendance_id' => $attendance->id])
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Attendance recorded successfully!',
                'attendance' => $attendance->load(['student.user', 'class'])
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to record attendance',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update attendance record
     */
    public function update(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'status' => 'required|in:present,absent,late',
                'notes' => 'nullable|string|max:500',
            ]);

            $attendance = Attendance::findOrFail($id);
            $attendance->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Attendance updated successfully',
                'attendance' => $attendance->load(['student.user', 'class'])
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update attendance',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get attendance statistics
     */
    public function getStats()
    {
        try {
            $stats = [
                'total_records' => Attendance::count(),
                'present_count' => Attendance::where('status', 'present')->count(),
                'absent_count' => Attendance::where('status', 'absent')->count(),
                'late_count' => Attendance::where('status', 'late')->count(),
                'today_records' => Attendance::whereDate('date', today())->count(),
            ];

            return response()->json([
                'success' => true,
                'stats' => $stats
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export attendance data
     */
    public function export(Request $request)
    {
        try {
            $query = Attendance::with(['student.user', 'class']);

            if ($request->has('class_id')) {
                $query->where('class_id', $request->class_id);
            }

            if ($request->has('start_date')) {
                $query->where('date', '>=', $request->start_date);
            }

            if ($request->has('end_date')) {
                $query->where('date', '<=', $request->end_date);
            }

            $attendances = $query->get();

            return response()->json([
                'success' => true,
                'attendances' => $attendances
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to export attendance',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete an attendance record
     */
    public function destroy($id)
    {
        try {
            $attendance = Attendance::findOrFail($id);
            $attendance->delete();

            return response()->json([
                'success' => true,
                'message' => 'Attendance record deleted successfully',
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete attendance',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
