<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AuditLogController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:sanctum', 'role:admin']);
    }

    public function index(Request $request): JsonResponse
    {
        $q = AuditLog::query()->with('user');
        if ($request->filled('user_id')) $q->where('user_id', (int)$request->input('user_id'));
        if ($request->filled('action')) $q->where('action', $request->input('action'));
        if ($request->filled('model_type')) $q->where('model_type', $request->input('model_type'));
        if ($request->filled('date_from')) $q->whereDate('created_at', '>=', $request->input('date_from'));
        if ($request->filled('date_to')) $q->whereDate('created_at', '<=', $request->input('date_to'));
        $per = max(1, (int) $request->input('per_page', 20));
        return response()->json(['success' => true, 'data' => $q->orderByDesc('created_at')->paginate($per)]);
    }
}
