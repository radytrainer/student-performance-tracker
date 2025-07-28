<?php

use App\Http\Controllers\Api\SubjectController;
// use App\Http\Controllers\Api\SubjectController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProfileImageController;
use App\Http\Controllers\Teacher\FeedbackFormController;
use App\Http\Controllers\Student\FeedbackSurveyController;
use App\Http\Controllers\Teacher\AttendanceController;

// Attendance routes for admin and teachers
// Route::middleware(['auth:sanctum', 'role:admin,teacher'])->group(function () {
    Route::get('/attendance', [AttendanceController::class, 'index']); 
    Route::get('/attendance/{id}', [AttendanceController::class, 'show']);               // Get attendance for class/date
    Route::post('/attendance', [AttendanceController::class, 'store']);              // Save attendance records
    Route::get('/attendance/my-classes', [AttendanceController::class, 'getMyClasses']); // Get teacher's classes
    Route::get('/attendance/recent', [AttendanceController::class, 'getRecentAttendance']); // Get recent attendance
    Route::get('/attendance/export', [AttendanceController::class, 'export']);       // Export CSV
    Route::get('/attendance/stats', [AttendanceController::class, 'getStats']);      // Get attendance statistics
// });

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


// Route::get('/subjects', [SubjectController::class, 'index']);


// Public routes (no authentication required)
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/register', [AuthController::class, 'register']);

// Temporary public access for testing user management
Route::get('/users', [UserController::class, 'index']);
Route::get('/users/{id}', [UserController::class, 'show']);

Route::delete('/users/{id}', [UserController::class, 'destroy']);
Route::post('/users', [UserController::class, 'store']);
Route::put('/users/{id}', [UserController::class, 'update']);
Route::patch('/users/{id}/status', [UserController::class, 'toggleStatus']);

// Active users endpoint for sidebar (public access for now)
Route::get('/active-users', [UserController::class, 'index']);

// Simple test route
Route::get('/test', function () {
    return response()->json(['message' => 'API is working', 'timestamp' => now()]);
});

// Protected routes (authentication required)
Route::middleware(['auth:sanctum'])->group(function () {
    
    // Auth routes
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/user', [AuthController::class, 'user']);
    Route::post('/auth/refresh', [AuthController::class, 'refresh']);
    
    // Profile routes (all authenticated users)
    Route::get('/profile', [App\Http\Controllers\ProfileController::class, 'show']);
    Route::put('/profile', [App\Http\Controllers\ProfileController::class, 'update']);
    
    // Profile Image CRUD routes
    Route::get('/profile/image', [ProfileImageController::class, 'show']);
    Route::post('/profile/image', [ProfileImageController::class, 'upload']);
    Route::put('/profile/image', [ProfileImageController::class, 'update']);
    Route::delete('/profile/image', [ProfileImageController::class, 'delete']);
    // Student Attendance routes
    Route::get('/student/my-attendance', [App\Http\Controllers\Student\AttendanceController::class, 'index']);




    // Admin only routes
    Route::middleware(['role:admin'])->group(function () {
        
        // User Management (admin access only)
        Route::apiResource('admin/users', UserController::class);
        Route::put('admin/users/{id}', [UserController::class, 'update']);
        Route::patch('admin/users/{id}/status', [UserController::class, 'toggleStatus']);
        
        // Legacy User Management routes (commented out - controllers don't exist)
        // Route::apiResource('admin/users', 'Admin\UserController');
        // Route::post('admin/users/{user}/reset-password', 'Admin\UserController@resetPassword');
        // Route::put('admin/users/{user}/role', 'Admin\UserController@updateRole');
        
        // Future Admin routes (controllers need to be created)
        // Route::get('admin/settings', 'Admin\SettingsController@index');
        // Route::put('admin/settings', 'Admin\SettingsController@update');
        // Route::get('admin/settings/{key}', 'Admin\SettingsController@show');
        
        // Route::get('admin/reports/overview', 'Admin\ReportsController@overview');
        // Route::get('admin/reports/students', 'Admin\ReportsController@students');
        // Route::get('admin/reports/teachers', 'Admin\ReportsController@teachers');
        // Route::get('admin/reports/performance', 'Admin\ReportsController@performance');
        // Route::get('admin/reports/attendance', 'Admin\ReportsController@attendance');
        
        // Route::get('admin/audit-logs', 'Admin\AuditLogController@index');
        // Route::get('admin/audit-logs/{log}', 'Admin\AuditLogController@show');
        
        // Route::get('admin/data-imports', 'Admin\DataImportController@index');
        // Route::get('admin/data-imports/{import}', 'Admin\DataImportController@show');
        // Route::delete('admin/data-imports/{import}', 'Admin\DataImportController@destroy');
        
        // Route::apiResource('admin/classes', 'Admin\ClassController');
        // Route::post('admin/classes/{class}/assign-teacher', 'Admin\ClassController@assignTeacher');
        // Route::post('admin/classes/{class}/enroll-student', 'Admin\ClassController@enrollStudent');
        
        // Route::apiResource('admin/subjects', 'Admin\SubjectController');
        // Route::post('admin/subjects/{subject}/assign-teachers', 'Admin\SubjectController@assignTeachers');
    });

    // Admin & Teacher routes (commented out for now - controllers need verification)
    Route::middleware(['role:admin,teacher'])->group(function () {
        
        // Future routes - need to verify controllers exist
        // Route::apiResource('students', StudentController::class);
        // Route::apiResource('grades', GradeController::class);
        
        // Future routes - controllers need to be verified/created
        // Route::apiResource('attendance', 'AttendanceController');
        // Route::apiResource('classes', 'ClassController');
        // Route::get('analytics/overview', 'AnalyticsController@overview');
        // Route::apiResource('reports', 'ReportController');
        // Route::apiResource('notifications', 'NotificationController');
        // Route::get('feedback/received', 'FeedbackController@received');
    });

    // Teacher only routes (commented out - controllers need verification)
    Route::middleware(['role:teacher'])->group(function () {
        
        // Future teacher routes
        // Route::get('teacher/dashboard', 'Teacher\DashboardController@index');
        // Route::get('teacher/my-classes', 'Teacher\ClassController@myClasses');
        // Route::get('teacher/my-students', 'Teacher\StudentController@myStudents');
        
        // More future teacher routes
        // Route::get('teacher/alerts', 'Teacher\AlertController@index');
        // Route::post('teacher/alerts', 'Teacher\AlertController@store');
        // Route::put('teacher/alerts/{alert}/resolve', 'Teacher\AlertController@resolve');
        // Route::post('teacher/feedback/students', 'Teacher\FeedbackController@giveToStudent');
        
        // Working Feedback Forms Management
        Route::apiResource('teacher/feedback-forms', FeedbackFormController::class);
        Route::get('teacher/feedback-classes', [FeedbackFormController::class, 'getMyClasses']);
        Route::post('teacher/form-assignments', [FeedbackFormController::class, 'assignToClasses']);
        Route::get('teacher/available-users', [FeedbackFormController::class, 'getAvailableUsers']);
        Route::post('teacher/user-assignments', [FeedbackFormController::class, 'assignToUsers']);
        Route::get('teacher/feedback-forms/{formId}/individual-assignments', [FeedbackFormController::class, 'getIndividualAssignments']);
        Route::get('teacher/analytics', [FeedbackFormController::class, 'getTeacherAnalytics']);
        Route::get('teacher/feedback-forms/{formId}/analytics', [FeedbackFormController::class, 'getFormAnalytics']);
        Route::post('teacher/bulk-assign-surveys', [FeedbackFormController::class, 'bulkAssignSurveys']);
        Route::get('teacher/survey-templates', [FeedbackFormController::class, 'getSurveyTemplates']);
        Route::post('teacher/create-from-template', [FeedbackFormController::class, 'createFromTemplate']);
        Route::post('teacher/schedule-survey', [FeedbackFormController::class, 'scheduleSurvey']);
    });

    // Student routes (commented out - controllers need verification)
    Route::middleware(['role:student'])->group(function () {
        
        // Future student routes
        // Route::get('student/dashboard', 'Student\DashboardController@index');
        
        // Student Reports Routes
        Route::get('student/reports', [App\Http\Controllers\Student\ReportController::class, 'index']);
        Route::post('student/reports/generate', [App\Http\Controllers\Student\ReportController::class, 'generate']);
        Route::get('student/reports/{id}', [App\Http\Controllers\Student\ReportController::class, 'show']);
        Route::get('student/reports/{id}/download', [App\Http\Controllers\Student\ReportController::class, 'download']);
        
        // More future student routes
        // Route::get('student/grades', 'Student\GradeController@index');
        // Route::get('student/performance', 'Student\PerformanceController@index');
        // Route::get('student/notifications', 'Student\NotificationController@index');
        // Route::post('student/feedback', 'Student\FeedbackController@submit');
        
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



