<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\DataImport;
use Maatwebsite\Excel\Facades\Excel; // For Excel parsing, optional
use Illuminate\Support\Facades\Validator;

class TeacherImportController extends Controller
{
    public function import(Request $request)
    {
        // Validate input
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|mimes:csv,xlsx',
            'import_type' => 'required|in:students,grades,attendance,subjects',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Invalid input.', 'errors' => $validator->errors()], 422);
        }

        $file = $request->file('file');
        $importType = $request->input('import_type');
        $user = Auth::user();

        $totalRecords = 0;
        $successfulRecords = 0;
        $failedRecords = 0;
        $errorLog = [];

        // Load and parse the file (simple CSV example)
        if ($file->getClientOriginalExtension() === 'csv') {
            $data = array_map('str_getcsv', file($file->getRealPath()));

            // Assuming first row is header
            $headers = array_map('strtolower', $data[0]);
            unset($data[0]);

            $totalRecords = count($data);

            foreach ($data as $index => $row) {
                $row = array_combine($headers, $row);

                // Example validation and insert logic per row:
                // Adjust validation and insert according to import type and data structure

                // Example: If import_type is 'attendance', insert attendance record
                try {
                    if ($importType === 'attendance') {
                        // Validate required fields in $row e.g. student_id, date, status
                        if (empty($row['student_id']) || empty($row['date']) || empty($row['status'])) {
                            $failedRecords++;
                            $errorLog[] = "Row " . ($index + 2) . ": Missing required fields.";
                            continue;
                        }

                        // Insert attendance record logic here
                        // Example:
                        // \App\Models\Attendance::create([
                        //     'student_id' => $row['student_id'],
                        //     'date' => $row['date'],
                        //     'status' => $row['status'],
                        //     'imported_by' => $user->id,
                        // ]);

                        $successfulRecords++;
                    }
                    // Similar blocks for grades, students, subjects...
                    else {
                        // For demo, count all as successful if no logic
                        $successfulRecords++;
                    }
                } catch (\Exception $e) {
                    $failedRecords++;
                    $errorLog[] = "Row " . ($index + 2) . ": " . $e->getMessage();
                }
            }
        }
        // For Excel files, you can use Laravel Excel package (maatwebsite/excel)
        else if ($file->getClientOriginalExtension() === 'xlsx') {
            // Requires maatwebsite/excel package
            // More complex parsing - let me know if you want that
            return response()->json(['message' => 'Excel import not implemented yet.'], 501);
        } else {
            return response()->json(['message' => 'Unsupported file format.'], 415);
        }

        // Save import metadata in data_imports table
        DataImport::create([
            'user_id' => $user->id,
            'import_type' => $importType,
            'file_name' => $file->getClientOriginalName(),
            'total_records' => $totalRecords,
            'successful_records' => $successfulRecords,
            'failed_records' => $failedRecords,
            'error_log' => !empty($errorLog) ? implode("\n", $errorLog) : null,
            'imported_at' => now(),
        ]);

        return response()->json([
            'message' => "Import completed. Total: $totalRecords, Success: $successfulRecords, Failed: $failedRecords.",
            'errors' => $errorLog,
        ]);
    }
}
