<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\User;
use App\Models\Classes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\JsonResponse;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use App\Models\UploadedFile;

class DataImportController extends Controller
{
    /**
     * Constructor to ensure only admins can access these methods
     */
    public function __construct()
    {
        $this->middleware(['auth:sanctum', 'role:admin']);
    }

    /**
     * Import students from CSV/Excel file
     */
    public function importStudents(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'file' => 'required|file|mimes:csv,xlsx,xls|max:5120',
                'default_class_id' => 'required|exists:classes,id'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $file = $request->file('file');
            $defaultClassId = $request->default_class_id;

            // Read file data (CSV or Excel)
            $csvData = $this->readUploadedFile($file);
            
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
                    $student = Student::create([
                        'user_id' => $user->id,
                        'student_code' => $studentCode,
                        'date_of_birth' => $row['date_of_birth'] ?? null,
                        'gender' => $row['gender'] ?? null,
                        'address' => $row['address'] ?? null,
                        'parent_name' => $row['parent_name'] ?? null,
                        'parent_phone' => $row['parent_phone'] ?? null,
                        'current_class_id' => $row['class_id'] ?? $defaultClassId,
                        'enrollment_date' => now()
                    ]);

                    // Create enrollment record for real-time class membership tracking
                    if ($student->current_class_id) {
                        \App\Models\StudentClass::create([
                            'student_id' => $student->user_id,
                            'class_id' => $student->current_class_id,
                            'enrollment_date' => now(),
                            'status' => 'active',
                        ]);
                    }

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
    * Upload any file and store it without parsing
    */
    public function uploadFile(Request $request): JsonResponse
    {
    try {
    $validator = Validator::make($request->all(), [
                'file' => 'required|file|max:10240', // allow up to 10MB any type
                'label' => 'nullable|string|max:255',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $file = $request->file('file');
            $original = $file->getClientOriginalName();
            $safeName = time() . '_' . Str::random(8) . '_' . preg_replace('/[^A-Za-z0-9._-]/', '_', $original);

            $path = $file->storeAs('imports/uploads', $safeName, ['disk' => 'public']);
            $url = asset('storage/' . $path);

            // Persist metadata for real-time availability
            $uploaded = UploadedFile::create([
                'user_id' => auth()->id(),
                'school_id' => auth()->user()->school_id ?? null,
                'label' => $request->get('label'),
                'original_name' => $original,
                'stored_path' => $path,
                'mime_type' => $file->getClientMimeType(),
                'size_bytes' => $file->getSize(),
                'url' => $url,
                'uploaded_at' => now(),
            ]);

            Log::info('File uploaded for storage', [
                'admin_id' => auth()->id(),
                'file_name' => $original,
                'stored_as' => $path,
                'label' => $request->get('label'),
                'timestamp' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'File uploaded successfully',
                'data' => $uploaded
            ]);
        } catch (\Exception $e) {
            Log::error('Error uploading file: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload file'
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
            // Return recent uploaded files as history for now
            $uploads = UploadedFile::query()
                ->orderByDesc('uploaded_at')
                ->limit(25)
                ->get()
                ->map(function ($u) {
                    return [
                        'id' => $u->id,
                        'file_name' => $u->original_name,
                        'type' => 'file',
                        'records_imported' => 0,
                        'records_failed' => 0,
                        'imported_by' => $u->user?->first_name . ' ' . $u->user?->last_name,
                        'imported_at' => $u->uploaded_at,
                        'status' => 'uploaded',
                        'url' => $u->url,
                        'size_bytes' => $u->size_bytes,
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $uploads
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
     * Read an uploaded file (CSV or Excel) and return row data as associative arrays
     */
    private function readUploadedFile($file): array
    {
        $ext = strtolower($file->getClientOriginalExtension());
        if (in_array($ext, ['xlsx', 'xls'])) {
            $sheetName = request()->get('sheet_name');
            return $this->readExcelFile($file, $sheetName);
        }
        // default to CSV
        return $this->readCsvFile($file);
    }

    /**
     * Read CSV file and return data
     */
    private function readCsvFile($file): array
    {
        $data = [];
        $headers = [];

        if (($handle = fopen($file->getRealPath(), 'r')) !== false) {
            $isFirstRow = true;
            
            while (($row = fgetcsv($handle, 1000, ',')) !== false) {
                if ($isFirstRow) {
                    $rawHeaders = array_map('trim', $row);
                    $headers = $this->canonicalizeHeaders($rawHeaders);
                    $isFirstRow = false;
                    continue;
                }

                if (count($row) === count($headers)) {
                    $assoc = array_combine($headers, array_map('trim', $row));
                    $data[] = $this->normalizeRow($assoc);
                }
            }
            
            fclose($handle);
        }

        return $data;
    }

    /**
     * Read Excel file and return data
     */
    private function readExcelFile($file, ?string $sheetName = null): array
    {
        $spreadsheet = IOFactory::load($file->getRealPath());
        $sheet = $sheetName ? $spreadsheet->getSheetByName($sheetName) : $spreadsheet->getActiveSheet();
        if (!$sheet) {
            // fallback to first sheet if not found
            $sheet = $spreadsheet->getSheet(0);
        }
        $rows = $sheet->toArray(null, true, true, false); // rows as [ [cells...] ]

        if (empty($rows)) {
            return [];
        }

        $rawHeaders = array_map(fn($h) => trim((string) $h), array_shift($rows));
        $headers = $this->canonicalizeHeaders($rawHeaders);
        $data = [];
        foreach ($rows as $row) {
            if (count($row) === 0 || count(array_filter($row, fn($v) => $v !== null && $v !== '')) === 0) {
                continue; // skip empty rows
            }
            // pad/truncate to headers length
            $row = array_slice(array_pad($row, count($headers), ''), 0, count($headers));
            $assoc = array_combine($headers, array_map(fn($v) => is_string($v) ? trim($v) : $v, $row));
            $data[] = $this->normalizeRow($assoc);
        }
        return $data;
    }

    /**
     * Normalize a single row (date formats, gender casing, etc.)
     */
    private function normalizeRow(array $row): array
    {
        // Normalize gender to lowercase if provided
        if (isset($row['gender']) && is_string($row['gender'])) {
            $row['gender'] = strtolower(trim($row['gender']));
        }

        // Normalize date_of_birth (handle Excel serial dates)
        if (isset($row['date_of_birth']) && $row['date_of_birth'] !== null && $row['date_of_birth'] !== '') {
            $dob = $row['date_of_birth'];
            try {
                if (is_numeric($dob)) {
                    $dt = ExcelDate::excelToDateTimeObject($dob);
                    $row['date_of_birth'] = $dt->format('Y-m-d');
                } else {
                    // try to parse common formats
                    $ts = strtotime((string) $dob);
                    if ($ts !== false) {
                        $row['date_of_birth'] = date('Y-m-d', $ts);
                    }
                }
            } catch (\Throwable $e) {
                // leave as-is; validation later may catch issues
            }
        }

        return $row;
    }

    /**
     * Convert provided headers to canonical snake_case lowercase keys
     */
    private function canonicalizeHeaders(array $headers): array
    {
        return array_map(function ($h) {
            $h = trim((string) $h);
            $h = strtolower($h);
            // replace common separators with underscore
            $h = str_replace([' ', '-', '.', '__'], ['_', '_', '_', '_'], $h);
            // collapse multiple underscores
            $h = preg_replace('/_+/', '_', $h);
            // common synonyms
            $map = [
                'firstname' => 'first_name',
                'lastname' => 'last_name',
                'first_name' => 'first_name',
                'last_name' => 'last_name',
                'email_address' => 'email',
                'dob' => 'date_of_birth',
                'dateofbirth' => 'date_of_birth',
                'sex' => 'gender',
                'class' => 'class_id',
            ];
            return $map[$h] ?? $h;
        }, $headers);
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
}
