<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Student;
use App\Models\ClassSchedule;
use App\Models\Progress;
use App\Models\Assignment;
use App\Models\Notification;
use App\Models\Feedback;
use App\Models\Resource;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $student = Student::where('user_id', $user->id)->firstOrFail();

        $summaryStats = [
            'totalCourses' => 6,
            'assignmentsSubmitted' => 12,
            'totalAssignments' => 15,
            'attendanceRate' => 94,
            'currentGPA' => 3.7,
            'lastLogin' => now()->subHours(2)->format('g:i A'),
        ];

        $weeklySchedule = ClassSchedule::where('student_id', $student->user_id)
            ->whereBetween('date', [now()->startOfWeek(), now()->endOfWeek()])
            ->get();

        $nextWeekSchedule = ClassSchedule::where('student_id', $student->user_id)
            ->whereBetween('date', [now()->addWeek()->startOfWeek(), now()->addWeek()->endOfWeek()])
            ->get();

        $academicProgress = Progress::where('student_id', $student->user_id)->get();

        $upcomingItems = Assignment::where('student_id', $student->user_id)
            ->whereDate('due_date', '>=', now())
            ->orderBy('due_date')
            ->get();

        $notifications = Notification::where('student_id', $student->user_id)
            ->latest()
            ->take(5)
            ->get();

        $unreadNotifications = $notifications->whereNull('read_at')->count();

        //  Attendance record via method from model
        $attendanceRecord = $student->attendanceSummary();

        $recentFeedback = Feedback::where('student_id', $student->user_id)
            ->latest()
            ->take(2)
            ->get();

        $resources = Resource::where('student_id', $student->user_id)->latest()->take(5)->get();

        return response()->json([
            'student' => [
                'name' => $user->name,
                'studentId' => $student->student_code,
                'course' => 'Computer Science - Year 3',
                'lastLogin' => $summaryStats['lastLogin'],
            ],
            'summaryStats' => $summaryStats,
            'weeklySchedule' => $weeklySchedule,
            'nextWeekSchedule' => $nextWeekSchedule,
            'academicProgress' => $academicProgress,
            'upcomingItems' => $upcomingItems,
            'notifications' => $notifications,
            'unreadNotifications' => $unreadNotifications,
            'attendanceRecord' => $attendanceRecord,
            'recentFeedback' => $recentFeedback,
            'resources' => $resources,
        ]);
    }
}
