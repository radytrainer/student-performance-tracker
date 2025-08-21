<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\User;
use App\Models\Classes;
use App\Models\Subject;
use App\Models\UploadedFile as UploadedFileModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\JsonResponse;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Str;

class DataImportController extends Controller
{
    /**
     * Constructor to ensure only admins can access these methods
     */
    public function __construct()
    {
        // Allow both admin and teacher to use these endpoints
        $this->middleware(['auth:sanctum', 'role:admin,teacher']);
    }

    /**
     * Import students from CSV/Excel file
     */
    public function importStudents(Request $request): JsonResponse
    {
        try {
            // Support either direct file upload or previously uploaded file by ID
            $validator = Validator::make($request->all(), [
                'file' => 'nullable|file|mimes:csv,xlsx,xls|max:2048',
                'uploaded_file_id' => 'nullable|exists:uploaded_files,id',
                'default_class_id' => 'required|exists:classes,id'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $defaultClassId = $request->default_class_id;

            // Determine data source
            $csvData = [];
            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $csvData = $this->readCsvFile($file);
            } elseif ($request->filled('uploaded_file_id')) {
                $uploaded = UploadedFileModel::find($request->input('uploaded_file_id'));
                if (!$uploaded) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Uploaded file not found'
                    ], 422);
                }
                $path = Storage::disk('public')->path($uploaded->stored_path);
                if (!file_exists($path)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Uploaded file is missing on server'
                    ], 422);
                }
                // Only CSV parsing supported here
                if (str_ends_with(strtolower($uploaded->original_name), '.csv')) {
                    $csvData = $this->readCsvPath($path);
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'Only CSV files are supported for import at the moment'
                    ], 422);
                }
            }
            
            if (empty($csvData)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No valid data found in file'
                ], 422);
            }

            // Validate headers
            $requiredHeaders = ['first_name', 'last_name', 'email', 'date_of_birth', 'gender'];
            $headers = array_keys($csvData[0]);
            $missingHeaders = array_diff($requiredHeaders, $headers);

            if (!empty($missingHeaders)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Missing required headers: ' . implode(', ', $missingHeaders)
                ], 422);
            }

            DB::beginTransaction();

            $successCount = 0;
            $errorCount = 0;
            $errors = [];

            foreach ($csvData as $index => $row) {
                try {
                    // Skip empty rows
                    if (empty(array_filter($row))) {
                        continue;
                    }

                    // Generate unique student code
                    $studentCode = $this->generateStudentCode();
                    
                    // Generate email if not provided
                    if (empty($row['email'])) {
                        $row['email'] = strtolower($row['first_name'] . '.' . $row['last_name'] . '@student.school.com');
                    }

                    // Check if email already exists
                    if (User::where('email', $row['email'])->exists()) {
                        $errors[] = "Row " . ($index + 1) . ": Email {$row['email']} already exists";
                        $errorCount++;
                        continue;
                    }

                    // Create user account
                    $user = User::create([
                        'first_name' => $row['first_name'],
                        'last_name' => $row['last_name'],
                        'email' => $row['email'],
                        'username' => $row['email'],
                        'password_hash' => Hash::make('password123'), // Default password
                        'role' => 'student',
                        'is_active' => true
                    ]);

                    // Create student profile
                    Student::create([
                        'user_id' => $user->id,
                        'student_code' => $studentCode,
                        'date_of_birth' => $row['date_of_birth'],
                        'gender' => $row['gender'],
                        'address' => $row['address'] ?? null,
                        'parent_name' => $row['parent_name'] ?? null,
                        'parent_phone' => $row['parent_phone'] ?? null,
                        'current_class_id' => $row['class_id'] ?? $defaultClassId,
                        'enrollment_date' => now()
                    ]);

                    $successCount++;

                } catch (\Exception $e) {
                    $errors[] = "Row " . ($index + 1) . ": " . $e->getMessage();
                    $errorCount++;
                }
            }

            DB::commit();

            // Log the import
            Log::info('Admin imported students from file', [
                'admin_id' => auth()->id(),
                'file_name' => $file->getClientOriginalName(),
                'success_count' => $successCount,
                'error_count' => $errorCount,
                'timestamp' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => "Import completed. {$successCount} students imported successfully.",
                'data' => [
                    'success_count' => $successCount,
                    'error_count' => $errorCount,
                    'errors' => $errors
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error importing students: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to import students: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get import template
     */
    public function getTemplate(Request $request): JsonResponse
    {
        try {
            $type = $request->get('type', 'students');

            $templates = [
                'students' => [
                    'headers' => [
                        'first_name',
                        'last_name', 
                        'email',
                        'date_of_birth',
                        'gender',
                        'address',
                        'parent_name',
                        'parent_phone',
                        'class_id'
                    ],
                    'sample_data' => [
                        [
                            'first_name' => 'John',
                            'last_name' => 'Doe',
                            'email' => 'john.doe@student.school.com',
                            'date_of_birth' => '2005-01-15',
                            'gender' => 'male',
                            'address' => '123 Main St',
                            'parent_name' => 'Jane Doe',
                            'parent_phone' => '+1-555-0123',
                            'class_id' => '1'
                        ]
                    ]
                ]
            ];

            return response()->json([
                'success' => true,
                'data' => $templates[$type] ?? $templates['students']
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting template: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to get template'
            ], 500);
        }
    }

    /**
     * Get import history
     */
    public function getImportHistory(): JsonResponse
    {
        try {
            // This would typically come from a dedicated imports table
            // For now, return mock data
            $history = [
                [
                    'id' => 1,
                    'file_name' => 'students_batch_1.csv',
                    'type' => 'students',
                    'records_imported' => 25,
                    'records_failed' => 2,
                    'imported_by' => auth()->user()->first_name . ' ' . auth()->user()->last_name,
                    'imported_at' => now()->subDays(2),
                    'status' => 'completed'
                ]
            ];

            return response()->json([
                'success' => true,
                'data' => $history
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching import history: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch import history'
            ], 500);
        }
    }

    /**
     * Read CSV file and return data
     */
    private function readCsvFile($file): array
    {
        return $this->readCsvPath($file->getRealPath());
    }

    private function readCsvPath(string $path): array
    {
        $data = [];
        $headers = [];

        if (($handle = fopen($path, 'r')) !== false) {
            $isFirstRow = true;
            while (($row = fgetcsv($handle, 1000, ',')) !== false) {
                if ($isFirstRow) {
                    $headers = array_map('trim', $row);
                    $isFirstRow = false;
                    continue;
                }
                if (count($row) === count($headers)) {
                    $data[] = array_combine($headers, array_map('trim', $row));
                }
            }
            fclose($handle);
        }
        return $data;
    }

    /**
     * Generate unique student code
     */
    private function generateStudentCode(): string
    {
        do {
            $code = 'STU' . date('Y') . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
        } while (Student::where('student_code', $code)->exists());

        return $code;
    }

    /**
     * Handle file upload (store only, no processing)
     */
    public function uploadFile(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'file' => 'required|file|mimes:csv,xlsx,xls|max:2048',
                'label' => 'nullable|string|max:255',
                'school_id' => 'nullable|integer'
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $file = $request->file('file');
            $storedPath = $file->store('imports', 'public');
            $url = Storage::disk('public')->url($storedPath);

            $uploaded = UploadedFileModel::create([
                'user_id' => auth()->id(),
                'school_id' => $request->input('school_id'),
                'label' => $request->input('label'),
                'original_name' => $file->getClientOriginalName(),
                'stored_path' => $storedPath,
                'mime_type' => $file->getMimeType(),
                'size_bytes' => $file->getSize(),
                'url' => $url,
                'uploaded_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'File uploaded successfully',
                'data' => $uploaded
            ]);
        } catch (\Exception $e) {
            Log::error('Upload failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload file'
            ], 500);
        }
    }

    /**
     * List uploaded files (paginated)
     */
    public function listUploads(Request $request): JsonResponse
    {
        $perPage = max(1, (int) $request->get('per_page', 10));
        $query = UploadedFileModel::query()
            ->where('user_id', auth()->id())
            ->orderByDesc('uploaded_at')
            ->orderByDesc('id');

        if ($request->filled('school_id')) {
            $query->where('school_id', $request->input('school_id'));
        }

        $paginator = $query->paginate($perPage);
        return response()->json([
            'success' => true,
            'data' => $paginator
        ]);
    }

    /**
     * Delete uploaded file by id
     */
    public function deleteUpload(int $id): JsonResponse
    {
        $uploaded = UploadedFileModel::find($id);
        if (!$uploaded) {
            return response()->json([
                'success' => false,
                'message' => 'File not found'
            ], 404);
        }
        $user = auth()->user();
        if ($uploaded->user_id !== $user->id && $user->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden'
            ], 403);
        }
        try {
            Storage::disk('public')->delete($uploaded->stored_path);
            $uploaded->delete();
            return response()->json([
                'success' => true,
                'message' => 'File deleted'
            ]);
        } catch (\Exception $e) {
            Log::error('Delete upload failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete file'
            ], 500);
        }
    }

    /**
     * Subjects list for import selectors
     */
    public function getSubjectsList(): JsonResponse
    {
        $subjects = Subject::select('id', 'subject_name')->orderBy('subject_name')->get();
        return response()->json([
            'success' => true,
            'data' => $subjects
        ]);
    }
}

