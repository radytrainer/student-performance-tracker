<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\DataImport;

class ImportDataController extends Controller
{
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,xlsx',
            'import_type' => 'required|string|in:attendance,grades'
        ]);

        $file = $request->file('file');
        $user = Auth::user();

        $fileName = time() . '_' . Str::slug($file->getClientOriginalName(), '_');
        $filePath = $file->storeAs('imports', $fileName, 'public');

        // Dummy record processing logic (replace with real logic)
        $total = 10;
        $successful = 9;
        $failed = 1;
        $errorLog = json_encode(['row_3' => 'Invalid data']);

        $import = DataImport::create([
            'user_id' => $user->id,
            'import_type' => $request->import_type,
            'file_name' => $fileName,
            'total_records' => $total,
            'successful_records' => $successful,
            'failed_records' => $failed,
            'error_log' => $errorLog,
            'imported_at' => now(),
        ]);

        return response()->json([
            'message' => 'Import successful',
            'data' => $import
        ]);
    }
}

