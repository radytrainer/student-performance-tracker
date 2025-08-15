<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\DataImport;
use App\Models\Student;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TeacherImportController extends Controller
{
    public function importStudents(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|mimes:csv,txt,xlsx,xls',
            'default_class_id' => 'nullable|exists:classes,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $file = $request->file('file');
            $defaultClassId = $request->input('default_class_id');
            $user = Auth::user();

            $extension = $file->getClientOriginalExtension();
            $importData = $this->processFile($file, $extension);

            $results = $this->importStudentsData($importData, $defaultClassId, $user);

            // Save import record
            DataImport::create([
                'user_id' => $user->id,
                'import_type' => 'students',
                'file_name' => $file->getClientOriginalName(),
                'total_records' => $results['total'],
                'successful_records' => $results['successful'],
                'failed_records' => $results['failed'],
                'error_log' => !empty($results['errors']) ? json_encode($results['errors']) : null,
                'imported_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => "Import completed. Total: {$results['total']}, Success: {$results['successful']}, Failed: {$results['failed']}",
                'data' => $results
            ]);

        } catch (\Exception $e) {
            Log::error('Import failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Import failed: ' . $e->getMessage(),
                'error' => $e->getTraceAsString()
            ], 500);
        }
    }

    protected function processFile($file, $extension)
    {
        $data = [];
        
        if ($extension === 'csv' || $extension === 'txt') {
            $fileContents = file($file->getRealPath());
            $data = array_map('str_getcsv', $fileContents);
        } elseif ($extension === 'xlsx' || $extension === 'xls') {
            $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
            $spreadsheet = $reader->load($file->getRealPath());
            $worksheet = $spreadsheet->getActiveSheet();
            $data = $worksheet->toArray();
        }

        if (empty($data)) {
            throw new \Exception('Empty file or unsupported file format');
        }

        $headers = array_map('strtolower', array_shift($data));
        $processedData = [];

        foreach ($data as $row) {
            if (count($row) !== count($headers)) {
                continue; // Skip malformed rows
            }
            $processedData[] = array_combine($headers, $row);
        }

        return $processedData;
    }

    protected function importStudentsData($data, $defaultClassId, $user)
    {
        $results = [
            'total' => count($data),
            'successful' => 0,
            'failed' => 0,
            'errors' => []
        ];

        foreach ($data as $index => $row) {
            try {
                $this->validateRow($row);
                $this->checkDuplicates($row);

                DB::transaction(function () use ($row, $defaultClassId) {
                    $user = User::create([
                        'username' => $row['username'],
                        'email' => $row['email'],
                        'password_hash' => bcrypt('student123'),
                        'role' => 'student',
                        'first_name' => $row['first_name'],
                        'last_name' => $row['last_name'],
                        'is_active' => true,
                    ]);

                    Student::create([
                        'user_id' => $user->id,
                        'student_code' => $row['student_code'],
                        'date_of_birth' => isset($row['date_of_birth']) ? Carbon::parse($row['date_of_birth']) : null,
                        'gender' => $row['gender'] ?? null,
                        'address' => $row['address'] ?? null,
                        'parent_name' => $row['parent_name'] ?? null,
                        'parent_phone' => $row['parent_phone'] ?? null,
                        'enrollment_date' => isset($row['enrollment_date']) ? Carbon::parse($row['enrollment_date']) : now(),
                        'current_class_id' => $row['current_class_id'] ?? $defaultClassId,
                    ]);
                });

                $results['successful']++;
            } catch (\Exception $e) {
                $results['failed']++;
                $results['errors'][] = [
                    'row' => $index + 2, // +2 because header is row 1 and we're 0-indexed
                    'message' => $e->getMessage(),
                    'data' => $row
                ];
                Log::error("Import error on row {$index}: " . $e->getMessage());
            }
        }

        return $results;
    }

    protected function validateRow($row)
    {
        $requiredFields = ['first_name', 'last_name', 'username', 'email', 'student_code'];
        
        foreach ($requiredFields as $field) {
            if (empty($row[$field])) {
                throw new \Exception("Missing required field: {$field}");
            }
        }

        if (!filter_var($row['email'], FILTER_VALIDATE_EMAIL)) {
            throw new \Exception("Invalid email format: {$row['email']}");
        }
    }

    protected function checkDuplicates($row)
    {
        if (User::where('username', $row['username'])->exists()) {
            throw new \Exception("Username already exists: {$row['username']}");
        }

        if (User::where('email', $row['email'])->exists()) {
            throw new \Exception("Email already exists: {$row['email']}");
        }

        if (Student::where('student_code', $row['student_code'])->exists()) {
            throw new \Exception("Student code already exists: {$row['student_code']}");
        }
    }

    public function getImportHistory()
    {
        try {
            $imports = DataImport::where('import_type', 'students')
                ->where('user_id', Auth::id())
                ->orderBy('imported_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $imports
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to get import history: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve import history'
            ], 500);
        }
    }
}