<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Notification;

class NotificationController extends Controller
{
    /**
     * List current user's notifications (paginated optional)
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = max(1, (int)$request->query('per_page', 10));
        $q = Notification::where('user_id', $request->user()->id)
            ->orderByDesc('sent_at')
            ->orderByDesc('id');
        $data = $q->paginate($perPage);
        return response()->json(['success' => true, 'data' => $data]);
    }

    /**
     * Mark notifications as read
     */
    public function markRead(Request $request): JsonResponse
    {
        $ids = (array) $request->input('ids', []);
        if (empty($ids)) {
            Notification::where('user_id', $request->user()->id)->update(['is_read' => true]);
        } else {
            Notification::where('user_id', $request->user()->id)->whereIn('id', $ids)->update(['is_read' => true]);
        }
        return response()->json(['success' => true]);
    }
}
