<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pass;
use App\Models\FlaggedItem;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PassController extends Controller
{
    /**
     * GET /api/v1/passes
     * Query: status?, search?, sort?
     */
    public function index(Request $request)
    {
        $resident = $request->user()->resident()->with(['unit', 'estate'])->firstOrFail();

        $query = Pass::where('resident_id', $resident->id)
                     ->with('flaggedItems');

        // Filter by status
        if ($request->filled('status') && $request->status !== 'all') {
            if ($request->status === 'Item Flagged') {
                $query->where('status', 'On-site')->where('items_flagged', true);
            } else {
                $query->where('status', $request->status);
            }
        }

        // Search by visitor name
        if ($request->filled('search')) {
            $query->where('visitor_name', 'like', '%' . $request->search . '%');
        }

        // Sort
        match ($request->get('sort', 'newest')) {
            'oldest' => $query->oldest(),
            'name'   => $query->orderBy('visitor_name'),
            default  => $query->latest(),
        };

        $passes = $query->get();

        return response()->json([
            'passes' => $passes->map(fn($p) => $this->formatPass($p, $resident)),
        ]);
    }

    /**
     * GET /api/v1/passes/{ulid}
     */
    public function show(Request $request, string $ulid)
    {
        $resident = $request->user()->resident()->firstOrFail();

        $pass = Pass::where('ulid', $ulid)
                    ->where('resident_id', $resident->id)
                    ->with('flaggedItems')
                    ->first();

        if (! $pass) {
            return response()->json([
                'error'   => true,
                'code'    => 'PASS_NOT_FOUND',
                'message' => 'Pass not found.',
            ], 404);
        }

        // Auto-expire if needed
        $this->maybeExpire($pass);

        return response()->json([
            'pass' => $this->formatPass($pass, $resident),
        ]);
    }

    /**
     * POST /api/v1/passes
     * Body: { visitorName, visitorPhone?, purpose, type, expiresAt, recurringDays?, vehiclePlate? }
     */
    public function store(Request $request)
    {
        $resident = $request->user()->resident()->with(['unit', 'estate'])->firstOrFail();

        $data = $request->validate([
            'visitorName'    => 'required|string|max:100',
            'visitorPhone'   => 'nullable|string|max:20',
            'purpose'        => 'required|string|in:Personal Visit,Delivery,Service,Business,Other',
            'type'           => 'required|string|in:One-time,Recurring',
            'expiresAt'      => 'required|date|after:now',
            'recurringDays'  => 'nullable|array',
            'recurringDays.*'=> 'string|in:Mon,Tue,Wed,Thu,Fri,Sat,Sun',
            'vehiclePlate'   => 'nullable|string|max:20',
        ]);

        if ($data['type'] === 'Recurring' && empty($data['recurringDays'])) {
            return response()->json([
                'error'   => true,
                'code'    => 'VALIDATION_ERROR',
                'message' => 'Recurring days are required for recurring passes.',
            ], 422);
        }

        $ulid = Str::ulid();

        $pass = Pass::create([
            'ulid'           => $ulid,
            'resident_id'    => $resident->id,
            'estate_id'      => $resident->estate_id,
            'visitor_name'   => $data['visitorName'],
            'visitor_phone'  => $data['visitorPhone'] ?? null,
            'vehicle_plate'  => $data['vehiclePlate'] ?? null,
            'purpose'        => $data['purpose'],
            'type'           => $data['type'],
            'recurring_days' => $data['recurringDays'] ?? null,
            'status'         => 'Pending',
            'items_flagged'  => false,
            'qr_data'        => (string) $ulid,
            'expires_at'     => $data['expiresAt'],
        ]);

        return response()->json([
            'pass' => $this->formatPass($pass->load('flaggedItems'), $resident),
        ], 201);
    }

    /**
     * PATCH /api/v1/passes/{ulid}/revoke
     */
    public function revoke(Request $request, string $ulid)
    {
        $resident = $request->user()->resident()->firstOrFail();

        $pass = Pass::where('ulid', $ulid)
                    ->where('resident_id', $resident->id)
                    ->first();

        if (! $pass) {
            return response()->json(['error' => true, 'code' => 'PASS_NOT_FOUND', 'message' => 'Pass not found.'], 404);
        }

        if (! $pass->canRevoke()) {
            return response()->json([
                'error'   => true,
                'code'    => 'CANNOT_REVOKE',
                'message' => 'Only pending or on-site passes can be revoked.',
            ], 422);
        }

        $pass->update([
            'status'     => 'Revoked',
            'revoked_at' => now(),
        ]);

        return response()->json([
            'pass' => $this->formatPass($pass->load('flaggedItems'), $resident),
        ]);
    }

    /**
     * PATCH /api/v1/passes/{ulid}/extend
     * Body: { newExpiresAt }
     */
    public function extend(Request $request, string $ulid)
    {
        $resident = $request->user()->resident()->firstOrFail();

        $data = $request->validate([
            'newExpiresAt' => 'required|date|after:now',
        ]);

        $pass = Pass::where('ulid', $ulid)
                    ->where('resident_id', $resident->id)
                    ->first();

        if (! $pass) {
            return response()->json(['error' => true, 'code' => 'PASS_NOT_FOUND', 'message' => 'Pass not found.'], 404);
        }

        if (! in_array($pass->status, ['Pending', 'On-site', 'Expired'])) {
            return response()->json([
                'error'   => true,
                'code'    => 'CANNOT_EXTEND',
                'message' => 'This pass cannot be extended.',
            ], 422);
        }

        $pass->update([
            'expires_at' => $data['newExpiresAt'],
            'status'     => in_array($pass->status, ['Expired']) ? 'Pending' : $pass->status,
        ]);

        return response()->json([
            'pass' => $this->formatPass($pass->load('flaggedItems'), $resident),
        ]);
    }

    /**
     * POST /api/v1/passes/{ulid}/flag-item
     * Body: { description, photoBase64? }
     */
    public function flagItem(Request $request, string $ulid)
    {
        $resident = $request->user()->resident()->firstOrFail();

        $data = $request->validate([
            'description'  => 'required|string|max:500',
            'photoBase64'  => 'nullable|string',
        ]);

        $pass = Pass::where('ulid', $ulid)
                    ->where('resident_id', $resident->id)
                    ->first();

        if (! $pass) {
            return response()->json(['error' => true, 'code' => 'PASS_NOT_FOUND', 'message' => 'Pass not found.'], 404);
        }

        if ($pass->status !== 'On-site') {
            return response()->json([
                'error'   => true,
                'code'    => 'PASS_NOT_ONSITE',
                'message' => 'Items can only be declared for on-site visitors.',
            ], 422);
        }

        // Store photo if provided (base64 → storage)
        $photoUrl  = null;
        $photoPath = null;
        if (! empty($data['photoBase64'])) {
            $imageData = base64_decode(preg_replace('/^data:image\/\w+;base64,/', '', $data['photoBase64']));
            $fileName  = 'flagged/' . $ulid . '/' . Str::uuid() . '.jpg';
            \Storage::disk('public')->put($fileName, $imageData);
            $photoPath = $fileName;
            $photoUrl  = asset('storage/' . $fileName);
        }

        $item = FlaggedItem::create([
            'pass_id'     => $pass->id,
            'description' => $data['description'],
            'photo_url'   => $photoUrl,
            'photo_path'  => $photoPath,
            'flagged_at'  => now(),
        ]);

        // Mark the pass as having flagged items
        $pass->update(['items_flagged' => true]);

        return response()->json([
            'item' => [
                'id'          => (string) $item->id,
                'description' => $item->description,
                'photoUrl'    => $item->photo_url,
                'flaggedAt'   => $item->flagged_at->toIso8601String(),
            ],
        ], 201);
    }

    // ─── Helpers ────────────────────────────────────────────────────────────

    private function maybeExpire(Pass $pass): void
    {
        if ($pass->status === 'Pending' && $pass->expires_at->isPast()) {
            $pass->update(['status' => 'Expired']);
        }
    }

    private function formatPass($pass, $resident): array
    {
        // Determine display status
        $displayStatus = ($pass->status === 'On-site' && $pass->items_flagged)
            ? 'Item Flagged'
            : $pass->status;

        return [
            'id'            => $pass->ulid,
            'visitorName'   => $pass->visitor_name,
            'visitorPhone'  => $pass->visitor_phone,
            'purpose'       => $pass->purpose,
            'type'          => $pass->type,
            'status'        => $displayStatus,
            'itemsFlagged'  => (bool) $pass->items_flagged,
            'hostUnit'      => $resident->unit?->flat_address ?? '',
            'hostName'      => $resident->user?->name ?? '',
            'qrData'        => $pass->qr_data,
            'vehiclePlate'  => $pass->vehicle_plate,
            'expiresAt'     => $pass->expires_at->toIso8601String(),
            'createdAt'     => $pass->created_at->toIso8601String(),
            'arrivedAt'     => $pass->arrived_at?->toIso8601String(),
            'exitedAt'      => $pass->exited_at?->toIso8601String(),
            'recurringDays' => $pass->recurring_days,
            'flaggedItems'  => $pass->flaggedItems->map(fn($item) => [
                'id'          => (string) $item->id,
                'photoUrl'    => env('APP_URL') . '/storage/' . $item->photo,
                'description' => $item->description,
                'flaggedAt'   => $item->created_at->toIso8601String(),
            ])->values()->all(),
        ];
    }
}
