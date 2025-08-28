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
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\TeacherSubjectController;

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
Route::get('admin/stats', [App\Http\Controllers\Admin\DashboardController::class, 'stats']);
// Public routes (no authentication required)
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/register', [AuthController::class, 'register']);
Route::get('/schools', [App\Http\Controllers\PublicSchoolController::class, 'index']); // Public schools for registration

// Social Authentication routes (need web middleware for session)
Route::middleware('web')->group(function () {
    Route::get('/auth/social/{provider}', [AuthController::class, 'redirectToProvider']);
    Route::get('/auth/social/{provider}/callback', [AuthController::class, 'handleProviderCallback']);
    // Google Sheets OAuth callback
    Route::get('/google/oauth/callback', [App\Http\Controllers\GoogleAuthController::class, 'callback']);
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
    Route::post('/students', [StudentController::class, 'store']);
    Route::get('/grades/assessment-types', [GradeController::class, 'assessmentTypes']);
    Route::get('/my-class-subjects', [ClassSubjectController::class, 'myClassSubjects']);

    // Departments (accessible to all authenticated users)
    Route::get('/departments', [DepartmentController::class, 'index']);
    Route::get('/departments/{id}', [DepartmentController::class, 'show']);
    Route::get('/departments/{id}/teachers', [DepartmentController::class, 'teachers']);

    // Active users endpoint for sidebar (protected)
    Route::get('/active-users', [UserController::class, 'index']);

    // Notifications
    Route::get('/notifications', [App\Http\Controllers\NotificationController::class, 'index']);
    Route::post('/notifications/mark-read', [App\Http\Controllers\NotificationController::class, 'markRead']);

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

        // Analytics (advanced)
        Route::get('analytics/correlation', [App\Http\Controllers\Admin\AnalyticsController::class, 'correlation']);
        Route::get('analytics/heatmap', [App\Http\Controllers\Admin\AnalyticsController::class, 'heatmap']);

         // Dashboard Stats
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

        // Department Management (admin only)
        Route::post('/departments', [DepartmentController::class, 'store']);
        Route::put('/departments/{id}', [DepartmentController::class, 'update']);
        Route::delete('/departments/{id}', [DepartmentController::class, 'destroy']);

        // Teacher-Subject Assignment Management (admin only)
        Route::post('admin/teachers/{teacher}/subjects', [TeacherSubjectController::class, 'assignSubjects']);
        Route::delete('admin/teachers/{teacher}/subjects', [TeacherSubjectController::class, 'removeSubjects']);
        Route::get('admin/teachers/{teacher}/subjects', [TeacherSubjectController::class, 'getTeacherSubjects']);
        Route::get('admin/subjects/{subject}/teachers', [TeacherSubjectController::class, 'getSubjectTeachers']);
        Route::post('admin/teachers/bulk-assign-subjects', [TeacherSubjectController::class, 'bulkAssignSubjects']);

        // Student Notes (admin)
        Route::get('admin/notes', [App\Http\Controllers\Admin\StudentNoteController::class, 'index']);
        Route::post('admin/notes', [App\Http\Controllers\Admin\StudentNoteController::class, 'store']);
        Route::put('admin/notes/{id}', [App\Http\Controllers\Admin\StudentNoteController::class, 'update']);
        Route::delete('admin/notes/{id}', [App\Http\Controllers\Admin\StudentNoteController::class, 'destroy']);

        // Term Management
        Route::apiResource('admin/terms', App\Http\Controllers\Admin\TermController::class);
        Route::post('admin/terms/{term}/set-current', [App\Http\Controllers\Admin\TermController::class, 'setCurrent']);
        Route::get('admin/terms/current', [App\Http\Controllers\Admin\TermController::class, 'getCurrent']);

        // System Settings
        Route::get('admin/settings', [App\Http\Controllers\Admin\SettingsController::class, 'index']);
        Route::put('admin/settings', [App\Http\Controllers\Admin\SettingsController::class, 'update']);
        Route::post('admin/settings/reset', [App\Http\Controllers\Admin\SettingsController::class, 'reset']);
        Route::post('admin/settings/backup', [App\Http\Controllers\Admin\SettingsController::class, 'backup']);
        Route::post('admin/settings/maintenance', [App\Http\Controllers\Admin\SettingsController::class, 'maintenance']);

        // Audit Logs (admin)
        Route::get('admin/audit-logs', [App\Http\Controllers\Admin\AuditLogController::class, 'index']);

        // Backups (admin)
        Route::get('admin/backups', [App\Http\Controllers\Admin\BackupController::class, 'index']);
        Route::get('admin/backups/download', [App\Http\Controllers\Admin\BackupController::class, 'download']);

        // Performance Alerts (admin)
        Route::get('admin/alerts', [App\Http\Controllers\Admin\PerformanceAlertController::class, 'index']);
        Route::post('admin/alerts', [App\Http\Controllers\Admin\PerformanceAlertController::class, 'store']);
        Route::put('admin/alerts/{id}', [App\Http\Controllers\Admin\PerformanceAlertController::class, 'update']);
        Route::post('admin/alerts/generate', function (\Illuminate\Http\Request $req) {
            \App\Jobs\GeneratePerformanceAlerts::dispatch($req->input('term_id'));
            return response()->json(['success' => true]);
        });

        // Data Import (admin paths)
        Route::post('admin/import/students', [App\Http\Controllers\Admin\DataImportController::class, 'importStudents']);
        Route::post('admin/import/upload-file', [App\Http\Controllers\Admin\DataImportController::class, 'uploadFile']);
        Route::get('admin/import/template', [App\Http\Controllers\Admin\DataImportController::class, 'getTemplate']);
        Route::get('admin/import/subjects-list', [App\Http\Controllers\Admin\DataImportController::class, 'getSubjectsList']);
        Route::get('admin/import/history', [App\Http\Controllers\Admin\DataImportController::class, 'getImportHistory']);
        Route::get('admin/import/uploads', [App\Http\Controllers\Admin\DataImportController::class, 'listUploads']);
        Route::delete('admin/import/uploads/{id}', [App\Http\Controllers\Admin\DataImportController::class, 'deleteUpload']);
        // Google Sheets integration (admin)
        Route::get('admin/google/auth-url', [App\Http\Controllers\GoogleAuthController::class, 'getAuthUrl']);
        Route::get('admin/google/status', [App\Http\Controllers\GoogleAuthController::class, 'status']);
        Route::post('admin/google/sheets/preview', [App\Http\Controllers\GoogleAuthController::class, 'preview']);
        Route::post('admin/import/google', [App\Http\Controllers\Admin\DataImportController::class, 'importFromGoogleSheet']);
    });

    // Teacher routes for import and uploads (use same controller with role check)
    Route::middleware(['role:teacher'])->group(function () {
        Route::post('teacher/import/students', [App\Http\Controllers\Admin\DataImportController::class, 'importStudents']);
        Route::post('teacher/import/upload-file', [App\Http\Controllers\Admin\DataImportController::class, 'uploadFile']);
        Route::get('teacher/import/subjects-list', [App\Http\Controllers\Admin\DataImportController::class, 'getSubjectsList']);
        Route::get('teacher/import/template', [App\Http\Controllers\Admin\DataImportController::class, 'getTemplate']);
        Route::get('teacher/import/uploads', [App\Http\Controllers\Admin\DataImportController::class, 'listUploads']);
        Route::delete('teacher/import/uploads/{id}', [App\Http\Controllers\Admin\DataImportController::class, 'deleteUpload']);
        // Google Sheets integration (teacher)
        Route::get('google/auth-url', [App\Http\Controllers\GoogleAuthController::class, 'getAuthUrl']);
        Route::get('google/status', [App\Http\Controllers\GoogleAuthController::class, 'status']);
        Route::post('google/sheets/preview', [App\Http\Controllers\GoogleAuthController::class, 'preview']);
        Route::post('teacher/import/google', [App\Http\Controllers\Admin\DataImportController::class, 'importFromGoogleSheet']);

        // Performance Alerts (teacher)
        Route::get('teacher/alerts', [App\Http\Controllers\Admin\PerformanceAlertController::class, 'index']);
        Route::put('teacher/alerts/{id}', [App\Http\Controllers\Admin\PerformanceAlertController::class, 'update']);
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
        // Teacher Class Management
        Route::get('teacher/classes/{classId}/students', [App\Http\Controllers\Teacher\ClassController::class, 'getClassStudents']);
        Route::get('teacher/classes/{classId}/grade-distribution', [App\Http\Controllers\Teacher\ClassController::class, 'getGradeDistribution']);
        Route::get('teacher/classes/{classId}/performance', [App\Http\Controllers\Teacher\ClassController::class, 'getClassPerformance']);
        Route::post('teacher/classes/{classId}/students', [App\Http\Controllers\Teacher\ClassController::class, 'addStudentToClass']);
        Route::post('teacher/classes/{classId}/students/bulk', [App\Http\Controllers\Teacher\ClassController::class, 'bulkAddStudents']);
        
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
        // Student analytics for teachers
        Route::get('/teacher/students/{student}/comparison', [App\Http\Controllers\Teacher\StudentAnalyticsController::class, 'comparison']);
    });

    // Student routes
    Route::middleware(['role:student'])->group(function () {
        // Student Reports
        Route::get('student/reports', [App\Http\Controllers\Student\ReportController::class, 'index']);
        Route::post('student/reports/generate', [App\Http\Controllers\Student\ReportController::class, 'generate']);
        Route::get('student/reports/{id}', [App\Http\Controllers\Student\ReportController::class, 'show']);
        Route::get('student/reports/{id}/download', [App\Http\Controllers\Student\ReportController::class, 'download']);
        // Student vs Class average comparison
        Route::get('student/comparison', [App\Http\Controllers\Student\ReportController::class, 'comparison']);

        // Feedback Surveys
        Route::get('student/surveys', [FeedbackSurveyController::class, 'index']);
        Route::get('student/surveys/{assignmentId}', [FeedbackSurveyController::class, 'show']);
        Route::post('student/surveys/{assignmentId}/complete', [FeedbackSurveyController::class, 'markCompleted']);
        Route::get('student/survey-stats', [FeedbackSurveyController::class, 'getStats']);
        });
        
     // Teacher Notes
     Route::middleware(['role:teacher'])->group(function () {
         Route::get('teacher/notes', [App\Http\Controllers\Admin\StudentNoteController::class, 'index']);
         Route::post('teacher/notes', [App\Http\Controllers\Admin\StudentNoteController::class, 'store']);
         Route::put('teacher/notes/{id}', [App\Http\Controllers\Admin\StudentNoteController::class, 'update']);
         Route::delete('teacher/notes/{id}', [App\Http\Controllers\Admin\StudentNoteController::class, 'destroy']);
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
