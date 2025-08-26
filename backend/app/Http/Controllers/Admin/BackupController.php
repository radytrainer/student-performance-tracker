<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BackupController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:sanctum', 'role:admin']);
    }

    public function index(Request $request)
    {
        $dir = storage_path('app/backups');
        if (!is_dir($dir)) {
            return response()->json(['success' => true, 'data' => []]);
        }
        $files = array_values(array_filter(scandir($dir), function($f) { return $f !== '.' && $f !== '..'; }));
        $list = [];
        foreach ($files as $f) {
            $path = $dir . DIRECTORY_SEPARATOR . $f;
            if (is_file($path)) {
                $list[] = [
                    'name' => $f,
                    'size_bytes' => filesize($path),
                    'modified_at' => date('c', filemtime($path))
                ];
            }
        }
        usort($list, fn($a,$b) => strcmp($b['modified_at'], $a['modified_at']));
        return response()->json(['success' => true, 'data' => $list]);
    }

    public function download(Request $request)
    {
        $file = $request->query('file');
        if (!$file) return response()->json(['success' => false, 'message' => 'File is required'], 422);
        $safe = basename($file);
        $path = storage_path('app/backups/' . $safe);
        if (!is_file($path)) return response()->json(['success' => false, 'message' => 'File not found'], 404);
        return response()->download($path, $safe);
    }
}
