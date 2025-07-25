<?php


use App\Http\Controllers\Api\SubjectController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\GradeController;
use App\Http\Controllers\ProfileImageController;
use App\Http\Controllers\Teacher\FeedbackFormController;
use App\Http\Controllers\Student\FeedbackSurveyController;
use App\Http\Controllers\UserController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/


Route::get('/subjects', [SubjectController::class, 'index']);


// Public routes (no authentication required)
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/register', [AuthController::class, 'register']);

Route::apiResource('/users', UserController::class);
Route::put('/users/{id}', [UserController::class, 'update']);


// Protected routes (authentication required)
Route::middleware(['auth:sanctum'])->group(function () {

    // Auth routes
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/user', [AuthController::class, 'user']);
    Route::post('/auth/refresh', [AuthController::class, 'refresh']);

    // Profile routes (all authenticated users)
    Route::get('/profile', [App\Http\Controllers\UserController::class, 'show']);
    Route::put('/profile', [App\Http\Controllers\UserController::class, 'update']);

    // Profile Image CRUD routes
    Route::get('/profile/image', [ProfileImageController::class, 'show']);
    Route::post('/profile/image', [ProfileImageController::class, 'upload']);
    Route::put('/profile/image', [ProfileImageController::class, 'update']);
    Route::delete('/profile/image', [ProfileImageController::class, 'delete']);

    // Admin only routes
    Route::middleware(['role:admin'])->group(function () {

        // User Management
        Route::apiResource('admin/users', 'Admin\UserController');
        Route::post('admin/users/{user}/reset-password', 'Admin\UserController@resetPassword');
        Route::put('admin/users/{user}/role', 'Admin\UserController@updateRole');

        // System Settings
        Route::get('admin/settings', 'Admin\SettingsController@index');
        Route::put('admin/settings', 'Admin\SettingsController@update');
        Route::get('admin/settings/{key}', 'Admin\SettingsController@show');

        // System Reports
        Route::get('admin/reports/overview', 'Admin\ReportsController@overview');
        Route::get('admin/reports/students', 'Admin\ReportsController@students');
        Route::get('admin/reports/teachers', 'Admin\ReportsController@teachers');
        Route::get('admin/reports/performance', 'Admin\ReportsController@performance');
        Route::get('admin/reports/attendance', 'Admin\ReportsController@attendance');

        // Audit Logs
        Route::get('admin/audit-logs', 'Admin\AuditLogController@index');
        Route::get('admin/audit-logs/{log}', 'Admin\AuditLogController@show');

        // Data Management
        Route::get('admin/data-imports', 'Admin\DataImportController@index');
        Route::get('admin/data-imports/{import}', 'Admin\DataImportController@show');
        Route::delete('admin/data-imports/{import}', 'Admin\DataImportController@destroy');

        // Class Management (Full Access)
        Route::apiResource('admin/classes', 'Admin\ClassController');
        Route::post('admin/classes/{class}/assign-teacher', 'Admin\ClassController@assignTeacher');
        Route::post('admin/classes/{class}/enroll-student', 'Admin\ClassController@enrollStudent');

        // Subject Management (Full Access)
        Route::apiResource('admin/subjects', 'Admin\SubjectController');
        Route::post('admin/subjects/{subject}/assign-teachers', 'Admin\SubjectController@assignTeachers');
    });

    // Admin & Teacher routes
    Route::middleware(['role:admin,teacher'])->group(function () {

        // Student Management
        Route::apiResource('students', 'StudentController');
        Route::get('students/{student}/performance', 'StudentController@performance')->middleware('permission:students.view');
        Route::get('students/{student}/grades', 'StudentController@grades')->middleware('permission:grades.view');
        Route::get('students/{student}/attendance', 'StudentController@attendance')->middleware('permission:attendance.view');
        Route::post('students/import', 'StudentController@import')->middleware('permission:imports.create');
        Route::get('students/export', 'StudentController@export')->middleware('permission:students.export');

        // Grade Management
        Route::apiResource('grades', 'GradeController');
        Route::post('grades/bulk', 'GradeController@bulkStore')->middleware('permission:grades.create');
        Route::put('grades/bulk', 'GradeController@bulkUpdate')->middleware('permission:grades.update');
        Route::post('grades/import', 'GradeController@import')->middleware('permission:imports.create');
        Route::get('grades/export', 'GradeController@export')->middleware('permission:grades.export');

        // Attendance Management
        Route::apiResource('attendance', 'AttendanceController');
        Route::post('attendance/mark', 'AttendanceController@markAttendance')->middleware('permission:attendance.create');
        Route::post('attendance/bulk', 'AttendanceController@bulkStore')->middleware('permission:attendance.create');
        Route::get('attendance/class/{class}/date/{date}', 'AttendanceController@getByClassAndDate');
        Route::post('attendance/import', 'AttendanceController@import')->middleware('permission:imports.create');
        Route::get('attendance/export', 'AttendanceController@export')->middleware('permission:attendance.export');

        // Class Management (View & Assigned Classes)
        Route::get('classes', 'ClassController@index');
        Route::get('classes/{class}', 'ClassController@show');
        Route::put('classes/{class}', 'ClassController@update')->middleware('permission:classes.update');
        Route::get('classes/{class}/students', 'ClassController@students');
        Route::get('classes/{class}/subjects', 'ClassController@subjects');
        Route::get('classes/{class}/analytics', 'ClassController@analytics')->middleware('permission:analytics.view');

        // Analytics & Reports
        Route::get('analytics/overview', 'AnalyticsController@overview')->middleware('permission:analytics.view');
        Route::get('analytics/grades', 'AnalyticsController@grades')->middleware('permission:analytics.view');
        Route::get('analytics/attendance', 'AnalyticsController@attendance')->middleware('permission:analytics.view');
        Route::get('analytics/performance-trends', 'AnalyticsController@performanceTrends')->middleware('permission:analytics.view');

        // Reports
        Route::get('reports', 'ReportController@index')->middleware('permission:reports.view');
        Route::post('reports', 'ReportController@store')->middleware('permission:reports.create');
        Route::get('reports/{report}', 'ReportController@show')->middleware('permission:reports.view');
        Route::delete('reports/{report}', 'ReportController@destroy')->middleware('permission:reports.delete');
        Route::get('reports/{report}/download', 'ReportController@download')->middleware('permission:reports.view');

        // Notifications
        Route::get('notifications', 'NotificationController@index')->middleware('permission:notifications.view');
        Route::post('notifications', 'NotificationController@store')->middleware('permission:notifications.create');
        Route::put('notifications/{notification}/read', 'NotificationController@markAsRead');
        Route::delete('notifications/{notification}', 'NotificationController@destroy');

        // Feedback Management (Teachers can view feedback about them)
        Route::get('feedback/received', 'FeedbackController@received')->middleware('permission:feedback.view');
        Route::post('feedback/respond', 'FeedbackController@respond')->middleware('permission:feedback.respond');
    });

    // Teacher only routes
    Route::middleware(['role:teacher'])->group(function () {

        // Teacher Dashboard
        Route::get('teacher/dashboard', 'Teacher\DashboardController@index');
        Route::get('teacher/my-classes', 'Teacher\ClassController@myClasses');
        Route::get('teacher/my-students', 'Teacher\StudentController@myStudents');

        // Performance Alerts
        Route::get('teacher/alerts', 'Teacher\AlertController@index');
        Route::post('teacher/alerts', 'Teacher\AlertController@store');
        Route::put('teacher/alerts/{alert}/resolve', 'Teacher\AlertController@resolve');

        // Teacher Feedback (Give feedback to students)
        Route::post('teacher/feedback/students', 'Teacher\FeedbackController@giveToStudent')->middleware('permission:feedback.respond');

        // Feedback Forms Management
        Route::apiResource('teacher/feedback-forms', FeedbackFormController::class);
        Route::get('teacher/feedback-classes', [FeedbackFormController::class, 'getMyClasses']);
        Route::post('teacher/form-assignments', [FeedbackFormController::class, 'assignToClasses']);

        // Individual User Assignments
        Route::get('teacher/available-users', [FeedbackFormController::class, 'getAvailableUsers']);
        Route::post('teacher/user-assignments', [FeedbackFormController::class, 'assignToUsers']);
        Route::get('teacher/feedback-forms/{formId}/individual-assignments', [FeedbackFormController::class, 'getIndividualAssignments']);

        // Analytics Routes
        Route::get('teacher/analytics', [FeedbackFormController::class, 'getTeacherAnalytics']);
        Route::get('teacher/feedback-forms/{formId}/analytics', [FeedbackFormController::class, 'getFormAnalytics']);

        // Enhanced Management Routes
        Route::post('teacher/bulk-assign-surveys', [FeedbackFormController::class, 'bulkAssignSurveys']);
        Route::get('teacher/survey-templates', [FeedbackFormController::class, 'getSurveyTemplates']);
        Route::post('teacher/create-from-template', [FeedbackFormController::class, 'createFromTemplate']);
        Route::post('teacher/schedule-survey', [FeedbackFormController::class, 'scheduleSurvey']);
    });

    // Student routes
    Route::middleware(['role:student'])->group(function () {

        // Student Dashboard
        Route::get('student/dashboard', 'Student\DashboardController@index');

        // Own Data Access
        Route::get('student/grades', [GradeController::class, 'index'])->middleware('permission:grades.view.own');
        Route::get('student/attendance', 'Student\AttendanceController@index')->middleware('permission:attendance.view.own');
        Route::get('student/reports', 'Student\ReportController@index')->middleware('permission:reports.view.own');
        Route::get('student/performance', 'Student\PerformanceController@index')->middleware('permission:grades.view.own');

        // Notifications (own only)
        Route::get('student/notifications', 'Student\NotificationController@index')->middleware('permission:notifications.view.own');
        Route::put('student/notifications/{notification}/read', 'Student\NotificationController@markAsRead');

        // Feedback (Submit feedback about teachers/system)
        Route::post('student/feedback', 'Student\FeedbackController@submit')->middleware('permission:feedback.create');
        Route::get('student/feedback/history', 'Student\FeedbackController@history')->middleware('permission:feedback.create');

        // Feedback Surveys
        Route::get('student/surveys', [FeedbackSurveyController::class, 'index']);
        Route::get('student/surveys/{assignmentId}', [FeedbackSurveyController::class, 'show']);
        Route::post('student/surveys/{assignmentId}/complete', [FeedbackSurveyController::class, 'markCompleted']);
        Route::get('student/survey-stats', [FeedbackSurveyController::class, 'getStats']);

        Route::get('/student/my-attendance', [App\Http\Controllers\Student\AttendanceController::class, 'index']);
    });

    // Shared routes (role-specific access handled by policies)
    Route::get('subjects', 'SubjectController@index');
    Route::get('subjects/{subject}', 'SubjectController@show');
    Route::get('terms', 'TermController@index');
    Route::get('terms/{term}', 'TermController@show');
    Route::get('terms/current', 'TermController@current');

    // General notifications and alerts
    Route::get('my-notifications', 'NotificationController@my');
    Route::put('my-notifications/mark-all-read', 'NotificationController@markAllAsRead');

    // Search endpoints
    Route::get('search/students', 'SearchController@students')->middleware('permission:students.view');
    Route::get('search/teachers', 'SearchController@teachers')->middleware('permission:teachers.view');
    Route::get('search/classes', 'SearchController@classes');
});

// Routes for testing role-based access
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/permissions', function (Request $request) {
        $user = $request->user();
        return response()->json([
            'user' => $user,
            'role' => $user->role,
            'permissions' => \App\Http\Middleware\PermissionMiddleware::getRolePermissions($user->role)
        ]);
    });
});
