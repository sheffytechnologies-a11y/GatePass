<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * GET /api/v1/notifications
     */
    public function index(Request $request)
    {
        $resident = $request->user()->resident()->firstOrFail();

        $notifications = Notification::where('resident_id', $resident->id)
            ->latest()
            ->get();

        return response()->json([
            'notifications' => $notifications->map(fn($n) => $this->format($n)),
        ]);
    }

    /**
     * PATCH /api/v1/notifications/read-all
     */
    public function markAllRead(Request $request)
    {
        $resident = $request->user()->resident()->firstOrFail();

        Notification::where('resident_id', $resident->id)
            ->where('read', false)
            ->update([
                'read'    => true,
                'read_at' => now(),
            ]);

        return response()->json(['message' => 'All notifications marked as read.']);
    }

    /**
     * PATCH /api/v1/notifications/{id}/read
     */
    public function markOneRead(Request $request, int $id)
    {
        $resident = $request->user()->resident()->firstOrFail();

        $notification = Notification::where('id', $id)
            ->where('resident_id', $resident->id)
            ->first();

        if (! $notification) {
            return response()->json(['error' => true, 'code' => 'NOT_FOUND', 'message' => 'Notification not found.'], 404);
        }

        $notification->update([
            'read'    => true,
            'read_at' => now(),
        ]);

        return response()->json([
            'notification' => $this->format($notification),
        ]);
    }

    // ─── Helpers ────────────────────────────────────────────────────────────

    private function format($n): array
    {
        return [
            'id'        => (string) $n->id,
            'type'      => $n->type,
            'message'   => $n->message,
            'passId'    => $n->pass_id ? (string) $n->pass?->ulid : null,
            'read'      => (bool) $n->read,
            'createdAt' => $n->created_at->toIso8601String(),
        ];
    }
}
