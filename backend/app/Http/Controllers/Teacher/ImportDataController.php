<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

use App\Models\DataImport;
use App\Imports\StudentsImport;
use Maatwebsite\Excel\Facades\Excel;

class ImportDataController extends Controller
{
    /**
     * Handle CSV/XLSX upload and create Users + Students.
     * Saves a DataImport row with summary + error log.
     *
     * Expected headers in file (first row):
     * first_name,last_name,username,email,student_code,date_of_birth,gender,address,parent_name,parent_phone,enrollment_date,current_class_id
     */
    public function import(Request $request)
    {
        $request->validate([
            'file'              => 'required|file|mimes:csv,xlsx,xls',
            'default_class_id'  => 'nullable|exists:classes,id',
        ]);

        $user = Auth::user();

        // Store original file
        $file = $request->file('file');
        $fileName = time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME), '_') . '.' . $file->getClientOriginalExtension();
        $storedPath = $file->storeAs('imports', $fileName, 'public');

        // Run import
        $import = new StudentsImport(
            defaultClassId: $request->input('default_class_id'),
            createdByUserId: $user->id
        );

        Excel::import($import, $file);

        // Save history
        $history = DataImport::create([
            'user_id'            => $user->id,
            'import_type'        => 'students',
            'file_name'          => $fileName,
            'total_records'      => $import->total,
            'successful_records' => $import->successful,
            'failed_records'     => $import->failed,
            'error_log'          => json_encode($import->errors, JSON_PRETTY_PRINT),
            'imported_at'        => now(),
        ]);

        return response()->json([
            'message' => 'Student import completed',
            'data'    => $history,
            'summary' => [
                'total'      => $import->total,
                'successful' => $import->successful,
                'failed'     => $import->failed,
                'errors'     => $import->errors,
            ],
        ]);
    }
}
