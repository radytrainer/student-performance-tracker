<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Carbon\Carbon;

class TeacherImportController extends Controller
{
    /**
     * POST /api/teacher/import-students
     * Accepts .csv/.xlsx/.xls and upserts students by student_code.
     */
    public function importStudents(Request $request)
    {
        // 1) Validate file
        $request->validate([
            'file' => 'required|file|mimes:csv,txt,xlsx,xls|max:20480', // up to 20MB
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|mimes:csv,txt,xlsx,xls',
            'default_class_id' => 'nullable|exists:classes,id',
            'subject_ids' => 'array',
            'subject_ids.*' => 'exists:subjects,id',
        ]);

        // 2) Save the uploaded file
        $path = $request->file('file')->store('imports/students');
        $absolutePath = Storage::path($path);
        $ext = strtolower($request->file('file')->getClientOriginalExtension());

        // 3) Read rows -> associative arrays keyed by normalized headers
        try {
            if (in_array($ext, ['csv', 'txt'])) {
                $rows = $this->readCsv($absolutePath);
            } else {
                // Try Laravel Excel first
                if (class_exists(\Maatwebsite\Excel\Facades\Excel::class)) {
                    $rows = $this->readExcelViaLaravelExcel($absolutePath);
                } else {
                    // Fallback requires PhpSpreadsheet (normally already installed by Laravel Excel)
                    if (!class_exists(\PhpOffice\PhpSpreadsheet\IOFactory::class)) {
                        return response()->json([
                            'message' => 'Import failed: Excel support not installed. Install "maatwebsite/excel" or upload a CSV.',
                        ], 422);
                    }
                    $rows = $this->readExcelViaPhpSpreadsheet($absolutePath);
                }
            }
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Could not read the file. '.$e->getMessage(),
            ], 422);
        }

        if (empty($rows)) {
        try {
            $file = $request->file('file');
            $defaultClassId = $request->input('default_class_id');
            $user = Auth::user();

            $extension = $file->getClientOriginalExtension();
            $importData = $this->processFile($file, $extension);

            $results = $this->importStudentsData($importData, $defaultClassId, $user);

            // Optionally assign selected subjects to the class
            if ($request->has('subject_ids') && is_array($request->subject_ids) && $defaultClassId) {
                foreach ($request->subject_ids as $subjectId) {
                    if (!\App\Models\ClassSubject::where('class_id', $defaultClassId)->where('subject_id', $subjectId)->exists()) {
                        \App\Models\ClassSubject::create([
                            'class_id' => $defaultClassId,
                            'subject_id' => $subjectId,
                            'teacher_id' => null,
                        ]);
                    }
                }
            }

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
                'message' => 'No data found in the file.',
            ], 422);
        }

        // 4) Expected columns (header names can vary; we normalize to these keys)
        $allowed = [
            'student_code', 'date_of_birth', 'gender', 'address',
            'parent_name', 'parent_phone', 'enrollment_date', 'current_class_id',
            // optional depending on your schema:
            'user_id',
        ];

        $now = now();
        $total = count($rows);
        $created = 0;
        $updated = 0;
        $failed  = 0;
        $errors  = [];

        // If you want to enforce that class IDs exist but don't know table name, we’ll check dynamically.
        $checkClassExists = Schema::hasTable('class_rooms') || Schema::hasTable('classes');

        foreach ($rows as $index => $row) {
            // Keep only allowed keys
            $data = [];
            foreach ($allowed as $key) {
                if (array_key_exists($key, $row)) {
                    $data[$key] = is_string($row[$key]) ? trim($row[$key]) : $row[$key];
                }
            }
                    $student = Student::create([
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

                    // Create enrollment record for real-time class membership tracking
                    if ($student->current_class_id) {
                        \App\Models\StudentClass::create([
                            'student_id' => $student->user_id,
                            'class_id' => $student->current_class_id,
                            'enrollment_date' => now(),
                            'status' => 'active',
                        ]);
                    }
                });

            // Normalize / coerce values
            if (isset($data['gender'])) {
                $g = strtolower((string)$data['gender']);
                $data['gender'] = in_array($g, ['male', 'm']) ? 'male' : (in_array($g, ['female', 'f']) ? 'female' : $g);
            }
            foreach (['date_of_birth', 'enrollment_date'] as $dcol) {
                if (!empty($data[$dcol])) {
                    $data[$dcol] = $this->toDate($data[$dcol]); // returns Y-m-d or null
                }
            }

            // Row-level validation (lightweight; we upsert by student_code)
            $rules = [
                'student_code'     => ['required', 'string'],
                'date_of_birth'    => ['nullable', 'date'],
                'gender'           => ['nullable', 'in:male,female'],
                'address'          => ['nullable', 'string'],
                'parent_name'      => ['nullable', 'string'],
                'parent_phone'     => ['nullable', 'string'],
                'enrollment_date'  => ['nullable', 'date'],
                'current_class_id' => ['nullable'],
                'user_id'          => ['nullable'],
            ];

            // Optionally verify class ID exists (if table present)
            if ($checkClassExists && !empty($data['current_class_id'])) {
                $rules['current_class_id'][] = function ($attr, $value, $fail) {
                    $exists = false;
                    if (Schema::hasTable('class_rooms')) {
                        $exists = DB::table('class_rooms')->where('id', $value)->exists();
                    } elseif (Schema::hasTable('classes')) {
                        $exists = DB::table('classes')->where('id', $value)->exists();
                    }
                    if (!$exists) $fail('current_class_id not found.');
                };
            }

            $validator = Validator::make($data, $rules);

            if ($validator->fails()) {
                $failed++;
                $errors[] = [
                    'row' => $index + 2, // +2 because arrays are 0-based and row 1 is header
                    'student_code' => $data['student_code'] ?? null,
                    'errors' => $validator->errors()->all(),
                ];
                continue;
            }

            // Build payload for DB
            $payload = array_intersect_key($data, array_flip($allowed));
            // timestamps
            $payload['updated_at'] = $now;
            if (empty($payload['enrollment_date'])) unset($payload['enrollment_date']);
            if (empty($payload['date_of_birth'])) unset($payload['date_of_birth']);

            try {
                // Upsert by unique business key 'student_code'
                // If new, also set created_at
                $exists = DB::table('students')->where('student_code', $data['student_code'])->exists();

                if ($exists) {
                    DB::table('students')->where('student_code', $data['student_code'])->update($payload);
                    $updated++;
                } else {
                    $payload['student_code'] = $data['student_code'];
                    $payload['created_at'] = $now;
                    DB::table('students')->insert($payload);
                    $created++;
                }
            } catch (\Throwable $e) {
                $failed++;
                $errors[] = [
                    'row' => $index + 2,
                    'student_code' => $data['student_code'] ?? null,
                    'errors' => ['DB error: '.$e->getMessage()],
                ];
            }
        }

        // 5) Optional: log into data_imports table if it exists
        if (Schema::hasTable('data_imports')) {
            try {
                DB::table('data_imports')->insert([
                    'user_id'     => auth()->id(),
                    'import_type' => 'students',
                    'file'        => $path,
                    'created_at'  => $now,
                    'updated_at'  => $now,
                ]);
            } catch (\Throwable $e) {
                // swallow logging errors
            }
        }

        $totalProcessed = $created + $updated + $failed;
        $msg = "Import completed. Total: {$total}, Success: ".($created + $updated).", Failed: {$failed}";

        return response()->json([
            'message' => $msg,
            'stats' => [
                'total_rows'   => $total,
                'processed'    => $totalProcessed,
                'created'      => $created,
                'updated'      => $updated,
                'failed'       => $failed,
            ],
            'errors' => $errors,
        ]);
    }

    /**
     * GET /api/teacher/import-history
     * If data_imports table exists, returns that. Otherwise returns latest students.
     */
    public function getImportHistory(Request $request)
    {
        if (Schema::hasTable('data_imports')) {
            $q = DB::table('data_imports')
                ->where('import_type', 'students')
                ->orderByDesc('created_at');

            if (auth()->check()) {
                $q->where(function ($sub) {
                    $sub->whereNull('user_id')
                        ->orWhere('user_id', auth()->id());
                });
            }

            return response()->json($q->limit(50)->get());
        }

        // Fallback: return latest students
        $students = DB::table('students')
            ->select('student_code', 'current_class_id', 'created_at')
            ->orderByDesc('created_at')
            ->limit(50)
            ->get();

        return response()->json($students);
    }

    /* ----------------------- Helpers ----------------------- */

    /** Convert a variety of inputs to Y-m-d or null. */
    private function toDate($value): ?string
    {
        if ($value instanceof \DateTimeInterface) {
            return Carbon::instance($value)->format('Y-m-d');
        }
        if (is_numeric($value)) {
            // Excel serial date (rough handling)
            try {
                if (class_exists(\PhpOffice\PhpSpreadsheet\Shared\Date::class)) {
                    $dt = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value);
                    return Carbon::instance($dt)->format('Y-m-d');
                }
            } catch (\Throwable $e) {}
        }
        $str = trim((string)$value);
        if ($str === '') return null;
        try {
            return Carbon::parse($str)->format('Y-m-d');
        } catch (\Throwable $e) {
            return null;
        }
    }

    /** Normalize a header to snake_case (e.g., "Student Code" -> "student_code"). */
    private function normalizeHeader(string $header): string
    {
        $header = trim($header);
        $header = str_replace(['-', '.', "\xC2\xA0"], ' ', $header); // replace non-breaking space too
        $header = preg_replace('/\s+/', ' ', $header);
        return Str::snake(mb_strtolower($header));
    }

    /** Build assoc arrays from CSV file. */
    private function readCsv(string $absolutePath): array
    {
        $result = [];
        if (!is_readable($absolutePath)) {
            throw new \RuntimeException('CSV file is not readable.');
        }
        if (($handle = fopen($absolutePath, 'r')) === false) {
            throw new \RuntimeException('Unable to open CSV.');
        }

        $headers = null;
        while (($row = fgetcsv($handle)) !== false) {
            // Skip empty lines
            if (count($row) === 1 && trim((string)$row[0]) === '') continue;

            if ($headers === null) {
                $headers = array_map(fn($h) => $this->normalizeHeader((string)$h), $row);
                continue;
            }
            $assoc = [];
            foreach ($headers as $i => $h) {
                $assoc[$h] = $row[$i] ?? null;
            }
            $result[] = $assoc;
        }
        fclose($handle);
        return $result;
    }

    /** Build assoc arrays using Laravel Excel (maatwebsite/excel). */
    private function readExcelViaLaravelExcel(string $absolutePath): array
    {
        $sheets = \Maatwebsite\Excel\Facades\Excel::toArray(new \stdClass(), $absolutePath);
        if (empty($sheets) || empty($sheets[0])) return [];
        $rows = $sheets[0]; // first sheet
        $headers = array_map(fn($h) => $this->normalizeHeader((string)$h), array_shift($rows));
        $out = [];
        foreach ($rows as $row) {
            // Some rows may be shorter; pad them
            $assoc = [];
            foreach ($headers as $i => $h) {
                $assoc[$h] = $row[$i] ?? null;
            }
            $out[] = $assoc;
        }
        return $out;
    }

    /** Build assoc arrays using PhpSpreadsheet directly. */
    private function readExcelViaPhpSpreadsheet(string $absolutePath): array
    {
        $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($absolutePath);
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($absolutePath);
        $sheet = $spreadsheet->getSheet(0);
        $rows = $sheet->toArray(null, true, true, true);

        if (empty($rows)) return [];
        // First row = headers
        $headerRow = array_shift($rows);
        $headers = [];
        foreach ($headerRow as $cell) {
            $headers[] = $this->normalizeHeader((string)$cell);
        }

        $out = [];
        foreach ($rows as $row) {
            $assoc = [];
            $i = 0;
            foreach ($headerRow as $colKey => $_) {
                $h = $headers[$i] ?? 'col_'.$i;
                $assoc[$h] = $row[$colKey] ?? null;
                $i++;
            }
            $out[] = $assoc;
        }
        return $out;
    }
}
