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
use App\Http\Controllers\TeacherImportController;

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

// Public routes (no authentication required)
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/register', [AuthController::class, 'register']);
Route::get('/schools', [App\Http\Controllers\PublicSchoolController::class, 'index']); // Public schools for registration

// Social Authentication routes (need web middleware for session)
Route::middleware('web')->group(function () {
    Route::get('/auth/social/{provider}', [AuthController::class, 'redirectToProvider']);
    Route::get('/auth/social/{provider}/callback', [AuthController::class, 'handleProviderCallback']);
});

// User routes are protected below under auth:sanctum


// Simple test route
Route::get('/test', function () {
    return response()->json(['message' => 'API is working', 'timestamp' => now()]);
});

// Protected routes (authentication required)
Route::middleware(['auth:sanctum'])->group(function () {
    // Common academic routes
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

    // Active users endpoint for sidebar (protected)
    Route::get('/active-users', [UserController::class, 'index']);

    // User Management (protected)
    Route::middleware(['role:admin'])->group(function () {
        Route::get('/users', [UserController::class, 'index']);
        Route::get('/users/{id}', [UserController::class, 'show']);
        Route::post('/users', [UserController::class, 'store']);
        Route::put('/users/{id}', [UserController::class, 'update']);
        Route::patch('/users/{id}/status', [UserController::class, 'toggleStatus']);
        Route::delete('/users/{id}', [UserController::class, 'destroy']);
    });

    // Auth routes
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/user', [AuthController::class, 'user']);
    Route::post('/auth/refresh', [AuthController::class, 'refresh']);

    // Profile routes (all authenticated users)
    Route::get('/profile', [ProfileController::class, 'show']);
    Route::put('/profile', [ProfileController::class, 'update']);

    // Profile Image CRUD routes
    Route::get('/profile/image', [ProfileImageController::class, 'show']);
    Route::post('/profile/image', [ProfileImageController::class, 'upload']);
    Route::put('/profile/image', [ProfileImageController::class, 'update']);
    Route::delete('/profile/image', [ProfileImageController::class, 'delete']);

    // Student Attendance routes
    Route::get('/student/my-attendance', [App\Http\Controllers\Student\AttendanceController::class, 'index']);

    // Teacher Import routes
    Route::post('/teacher/import-students', [TeacherImportController::class, 'importStudents']);
    Route::get('/teacher/import-history', [TeacherImportController::class, 'getImportHistory']);

    // Attendance routes
    Route::get('/attendance', [AttendanceController::class, 'index']);
    Route::get('/attendance/{id}', [AttendanceController::class, 'show']);
    Route::post('/attendance', [AttendanceController::class, 'store']);
    Route::get('/attendance/my-classes', [AttendanceController::class, 'getMyClasses']);
    Route::get('/attendance/recent', [AttendanceController::class, 'getRecentAttendance']);
    Route::get('/attendance/export', [AttendanceController::class, 'export']);
    Route::get('/attendance/stats', [AttendanceController::class, 'getStats']);

    // Admin only routes
    Route::middleware(['role:admin'])->group(function () {
        // User Management
        Route::apiResource('admin/users', UserController::class);
        Route::put('admin/users/{id}', [UserController::class, 'update']);
        Route::patch('admin/users/{id}/status', [UserController::class, 'toggleStatus']);

        // Admin User Management
        Route::post('admin/users/{user}/reset-password', [App\Http\Controllers\Admin\UserController::class, 'resetPassword']);
        Route::put('admin/users/{user}/role', [App\Http\Controllers\Admin\UserController::class, 'updateRole']);
        Route::patch('admin/users/{user}/toggle-status', [App\Http\Controllers\Admin\UserController::class, 'toggleStatus']);
        Route::post('admin/users/bulk-status', [App\Http\Controllers\Admin\UserController::class, 'bulkUpdateStatus']);
        Route::get('admin/users/{user}/access-logs', [App\Http\Controllers\Admin\UserController::class, 'getAccessLogs']);

        // Admin Academic Data Management
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

    // Super Admin routes
    Route::prefix('super-admin')->group(function () {
        Route::get('/schools', [App\Http\Controllers\SuperAdmin\SchoolController::class, 'index']);
        Route::post('/schools', [App\Http\Controllers\SuperAdmin\SchoolController::class, 'store']);
        Route::get('/schools/{school}', [App\Http\Controllers\SuperAdmin\SchoolController::class, 'show']);
        Route::put('/schools/{school}', [App\Http\Controllers\SuperAdmin\SchoolController::class, 'update']);
        Route::delete('/schools/{school}', [App\Http\Controllers\SuperAdmin\SchoolController::class, 'destroy']);
        Route::post('/schools/{school}/sub-admins', [App\Http\Controllers\SuperAdmin\SchoolController::class, 'createSubAdmin']);
        Route::get('/schools/{school}/sub-admins', [App\Http\Controllers\SuperAdmin\SchoolController::class, 'getSubAdmins']);
        Route::get('/stats', [App\Http\Controllers\SuperAdmin\SchoolController::class, 'getStats']);
    });

    // Teacher only routes
    Route::middleware(['role:teacher'])->group(function () {
        // Feedback Forms Management
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

        // Teacher Dashboard
        Route::get('/teacher/dashboard-students', [App\Http\Controllers\Teacher\DashboardController::class, 'students']);
    });

    // Student routes
    Route::middleware(['role:student'])->group(function () {
        // Student Reports
        Route::get('student/reports', [App\Http\Controllers\Student\ReportController::class, 'index']);
        Route::post('student/reports/generate', [App\Http\Controllers\Student\ReportController::class, 'generate']);
        Route::get('student/reports/{id}', [App\Http\Controllers\Student\ReportController::class, 'show']);
        Route::get('student/reports/{id}/download', [App\Http\Controllers\Student\ReportController::class, 'download']);

        // Feedback Surveys
        Route::get('student/surveys', [FeedbackSurveyController::class, 'index']);
        Route::get('student/surveys/{assignmentId}', [FeedbackSurveyController::class, 'show']);
        Route::post('student/surveys/{assignmentId}/complete', [FeedbackSurveyController::class, 'markCompleted']);
        Route::get('student/survey-stats', [FeedbackSurveyController::class, 'getStats']);
    });
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
