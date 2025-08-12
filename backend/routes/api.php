<?php



use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProfileImageController;
use App\Http\Controllers\Teacher\FeedbackFormController;
use App\Http\Controllers\Student\FeedbackSurveyController;
use App\Http\Controllers\GradeController;
use App\Http\Controllers\TermController;
use App\Http\Controllers\ClassController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\ClassSubjectController;







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






// Route::get('/class-subjects', [ClassSubjectController::class, 'index']);
// Public routes (no authentication required)
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/register', [AuthController::class, 'register']);
Route::get('/schools', [App\Http\Controllers\PublicSchoolController::class, 'index']); // Public schools for registration

// Social Authentication routes (need web middleware for session)
Route::middleware('web')->group(function () {
    Route::get('/auth/social/{provider}', [AuthController::class, 'redirectToProvider']);
    Route::get('/auth/social/{provider}/callback', [AuthController::class, 'handleProviderCallback']);
});

// Temporary public access for testing user management
Route::get('/users', [UserController::class, 'index']);
Route::get('/users/{id}', [UserController::class, 'show']);
Route::get('/students-users', [UserController::class, 'students']);

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


    Route::get('/grades', [GradeController::class, 'index']);
    Route::post('/grades', [GradeController::class, 'store']);
    Route::get('/grades/{id}', [GradeController::class, 'show']);
    Route::put('/grades/{id}', [GradeController::class, 'update']);
    Route::delete('/grades/{id}', [GradeController::class, 'destroy']);
    Route::post('/grades/bulk', [GradeController::class, 'bulkStore']);
    Route::get('/student-grades/{student_id}', [GradeController::class, 'studentGrades']);
    Route::get('/classes', [ClassController::class, 'index']);
    Route::get('/subjects', [SubjectController::class, 'index']);
    Route::get('/terms', [TermController::class, 'index']);
    Route::get('/students', [StudentController::class, 'index']);
    Route::get('/grades/assessment-types', [GradeController::class, 'assessmentTypes']);
    Route::get('/my-class-subjects', [ClassSubjectController::class, 'myClassSubjects']);

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

        // Admin User Management routes with Admin\UserController
        Route::post('admin/users/{user}/reset-password', [App\Http\Controllers\Admin\UserController::class, 'resetPassword']);
        Route::put('admin/users/{user}/role', [App\Http\Controllers\Admin\UserController::class, 'updateRole']);
        Route::patch('admin/users/{user}/toggle-status', [App\Http\Controllers\Admin\UserController::class, 'toggleStatus']);
        Route::post('admin/users/bulk-status', [App\Http\Controllers\Admin\UserController::class, 'bulkUpdateStatus']);
        Route::get('admin/users/{user}/access-logs', [App\Http\Controllers\Admin\UserController::class, 'getAccessLogs']);

        // Admin Academic Data Management
        // Student Management
        Route::apiResource('admin/students', App\Http\Controllers\Admin\StudentController::class);
        Route::post('admin/students/bulk-action', [App\Http\Controllers\Admin\StudentController::class, 'bulkAction']);
        Route::get('admin/classes/{classId}/students', [App\Http\Controllers\Admin\StudentController::class, 'getByClass']);

        // Class Management  
        Route::apiResource('admin/classes', App\Http\Controllers\Admin\ClassController::class);
        Route::post('admin/classes/{class}/assign-teacher', [App\Http\Controllers\Admin\ClassController::class, 'assignTeacher']);
        Route::get('admin/teachers/available', [App\Http\Controllers\Admin\ClassController::class, 'getAvailableTeachers']);
        Route::get('admin/classes-stats', [App\Http\Controllers\Admin\ClassController::class, 'getClassStats']);

        // Subject Management
        Route::apiResource('admin/subjects', App\Http\Controllers\Admin\SubjectController::class);
        Route::post('admin/subjects/bulk-action', [App\Http\Controllers\Admin\SubjectController::class, 'bulkAction']);
        Route::get('admin/subjects/departments', [App\Http\Controllers\Admin\SubjectController::class, 'getDepartments']);
        Route::get('admin/subjects-stats', [App\Http\Controllers\Admin\SubjectController::class, 'getSubjectStats']);

        // Term Management
        Route::apiResource('admin/terms', App\Http\Controllers\Admin\TermController::class);
        Route::post('admin/terms/{term}/set-current', [App\Http\Controllers\Admin\TermController::class, 'setCurrent']);
        Route::get('admin/terms/current', [App\Http\Controllers\Admin\TermController::class, 'getCurrent']);

        // Data Import
        Route::post('admin/import/students', [App\Http\Controllers\Admin\DataImportController::class, 'importStudents']);
        Route::get('admin/import/template', [App\Http\Controllers\Admin\DataImportController::class, 'getTemplate']);
        Route::get('admin/import/history', [App\Http\Controllers\Admin\DataImportController::class, 'getImportHistory']);
    });

    // Super Admin Routes - Only for admin@school.com
    Route::middleware(['auth:sanctum'])->prefix('super-admin')->group(function () {
        Route::apiResource('schools', App\Http\Controllers\SuperAdmin\SchoolController::class);
        Route::post('schools/{school}/sub-admins', [App\Http\Controllers\SuperAdmin\SchoolController::class, 'createSubAdmin']);
        Route::get('schools/{school}/sub-admins', [App\Http\Controllers\SuperAdmin\SchoolController::class, 'getSubAdmins']);
        Route::get('stats', [App\Http\Controllers\SuperAdmin\SchoolController::class, 'getStats']);
    });

    // Admin routes (restricted by school for sub-admins)
    Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
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
        Route::apiResource('grades', GradeController::class);

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
    // Route::get('subjects', 'SubjectController@index');
    // Route::get('subjects/{subject}', 'SubjectController@show');
    // Route::get('terms', 'TermController@index');
    // Route::get('terms/{term}', 'TermController@show');
    // Route::get('terms/current', 'TermController@current');

    // General notifications and alerts (commented out - controller needs to be created)
    // Route::get('my-notifications', 'NotificationController@my');
    // Route::put('my-notifications/mark-all-read', 'NotificationController@markAllAsRead');

    // Search endpoints (commented out - controller needs to be created)
    // Route::get('search/students', 'SearchController@students')->middleware('permission:students.view');
    // Route::get('search/teachers', 'SearchController@teachers')->middleware('permission:teachers.view');
    // Route::get('search/classes', 'SearchController@classes');
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

    Route::middleware(['auth:sanctum', 'role:teacher'])->get(
    '/teacher/dashboard-students', 
    [App\Http\Controllers\Teacher\DashboardController::class, 'students']
);

});


