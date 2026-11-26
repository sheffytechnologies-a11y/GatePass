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
        $user = $request->user();

        // ── Security Officer Pass Feed ──
        if ($user->type === 'security') {
            $estateId = $user->estate_id;
            $query = Pass::where('estate_id', $estateId)
                ->whereIn('status', ['On-site', 'Exited', 'Declined'])
                ->with(['resident.unit', 'resident.user', 'flaggedItems']);

            // Filter by status
            if ($request->filled('status') && $request->status !== 'all') {
                $query->where('status', $request->status);
            }

            // Search by visitor name, phone, or car plate
            if ($request->filled('search')) {
                $term = $request->search;
                $query->where(function ($q) use ($term) {
                    $q->where('visitor_name', 'like', '%' . $term . '%')
                      ->orWhere('visitor_phone', 'like', '%' . $term . '%')
                      ->orWhere('vehicle_plate', 'like', '%' . $term . '%');
                });
            }

            // Sort
            match ($request->get('sort', 'newest')) {
                'oldest' => $query->oldest(),
                'name'   => $query->orderBy('visitor_name'),
                default  => $query->latest(),
            };

            $passes = $query->get();

            return response()->json([
                'passes' => $passes->map(fn($p) => $this->formatPassForSecurity($p)),
            ]);
        }

        $resident = $user->resident()->with(['unit', 'estate'])->firstOrFail();

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
        $user = $request->user();
        $resident = null;

        $query = Pass::where('ulid', $ulid)
            ->with(['flaggedItems', 'resident.unit', 'resident.user']);

        if ($user->type === 'security') {
            $query->where('estate_id', $user->estate_id);
        } else {
            $resident = $user->resident()->with(['user', 'unit', 'estate'])->first();

            if (! $resident) {
                return response()->json([
                    'error'   => true,
                    'code'    => 'NO_RESIDENT_PROFILE',
                    'message' => 'No resident profile found.',
                ], 404);
            }

            $query->where('resident_id', $resident->id);
        }

        $pass = $query->first();

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
            'pass' => $user->type === 'security'
                ? $this->formatPassForSecurity($pass)
                : $this->formatPass($pass, $resident),
        ]);
    }

    /**
     * GET /api/v1/passes/lookup?query=...
     * Unified security lookup by 6-digit pass code, visitor phone number, or pass ULID.
     */
    public function lookup(Request $request)
    {
        $user = $request->user();

        if ($user->type !== 'security') {
            return response()->json([
                'error'   => true,
                'code'    => 'FORBIDDEN',
                'message' => 'Only security users can look up visitor passes.',
            ], 403);
        }

        $data = $request->validate([
            'query' => 'required|string|max:120',
        ]);

        $raw = trim($data['query']);
        if (str_contains($raw, '/pass/')) {
            $parts = explode('/pass/', $raw, 2);
            $raw = explode('?', explode('/', $parts[1])[0])[0];
        }

        $digitsOnly = preg_replace('/\D+/', '', $raw);
        $pass = null;
        $allMatches = collect();

        // 1. Exact 6-digit numeric pass code
        if ($digitsOnly !== '' && strlen($digitsOnly) === 6 && preg_match('/^[\d\s-]+$/', $raw)) {
            $candidates = Pass::where('estate_id', $user->estate_id)
                ->where('pass_code', $digitsOnly)
                ->latest()
                ->with(['flaggedItems', 'resident.unit', 'resident.user'])
                ->get();

            foreach ($candidates as $c) {
                $this->maybeExpire($c);
            }

            $active = $candidates->filter(fn(Pass $c) => in_array($c->status, ['Pending', 'On-site'], true));
            $allMatches = $active->isNotEmpty() ? $active : $candidates;
            $pass = $allMatches->first();
        }
        // 2. Visitor phone number (7+ digits)
        elseif ($digitsOnly !== '' && strlen($digitsOnly) >= 7 && preg_match('/^[\d\s+\-()]+$/', $raw)) {
            $searchTail = $this->phoneTail($raw);
            $candidates = Pass::where('estate_id', $user->estate_id)
                ->whereNotNull('visitor_phone')
                ->latest()
                ->limit(300)
                ->with(['flaggedItems', 'resident.unit', 'resident.user'])
                ->get();

            $matchingCandidates = $candidates->filter(fn(Pass $c) => $this->phoneTail((string) $c->visitor_phone) === $searchTail);

            foreach ($matchingCandidates as $c) {
                $this->maybeExpire($c);
            }

            $active = $matchingCandidates->filter(fn(Pass $c) => in_array($c->status, ['Pending', 'On-site'], true));
            $allMatches = $active->isNotEmpty() ? $active : $matchingCandidates;
            $pass = $allMatches->first();
        }
        // 3. Pass ULID
        else {
            $pass = Pass::where('estate_id', $user->estate_id)
                ->where('ulid', $raw)
                ->with(['flaggedItems', 'resident.unit', 'resident.user'])
                ->first();

            if ($pass) {
                $this->maybeExpire($pass);
                $allMatches = collect([$pass]);
            }
        }

        if (! $pass) {
            return response()->json([
                'error'   => true,
                'code'    => 'PASS_NOT_FOUND',
                'message' => 'No active pass found for this code or phone number.',
            ], 404);
        }

        $allMatches = $allMatches->map(function (Pass $p) {
            return $p->refresh()->load(['flaggedItems', 'resident.unit', 'resident.user']);
        });

        $formattedPasses = $allMatches->map(fn(Pass $p) => $this->formatPassForSecurity($p))->values()->all();

        return response()->json([
            'pass'   => $formattedPasses[0] ?? $this->formatPassForSecurity($pass),
            'passes' => $formattedPasses,
        ]);
    }

    /**
     * GET /api/v1/passes/find-by-phone?phone=...
     */
    public function findByPhone(Request $request)
    {
        $user = $request->user();

        if ($user->type !== 'security') {
            return response()->json([
                'error'   => true,
                'code'    => 'FORBIDDEN',
                'message' => 'Only security users can search by phone.',
            ], 403);
        }

        $data = $request->validate([
            'phone' => 'required|string|max:30',
        ]);

        $searchTail = $this->phoneTail($data['phone']);
        if ($searchTail === '') {
            return response()->json([
                'error'   => true,
                'code'    => 'INVALID_PHONE',
                'message' => 'Provide a valid phone number.',
            ], 422);
        }

        $candidates = Pass::where('estate_id', $user->estate_id)
            ->whereNotNull('visitor_phone')
            ->latest()
            ->limit(300)
            ->with(['flaggedItems', 'resident.unit', 'resident.user'])
            ->get();

        $matchingCandidates = $candidates->filter(function (Pass $pass) use ($searchTail) {
            return $this->phoneTail((string) $pass->visitor_phone) === $searchTail;
        });

        foreach ($matchingCandidates as $c) {
            $this->maybeExpire($c);
        }

        $active = $matchingCandidates->filter(fn(Pass $c) => in_array($c->status, ['Pending', 'On-site'], true));
        $allMatches = $active->isNotEmpty() ? $active : $matchingCandidates;
        $pass = $allMatches->first();

        if (! $pass) {
            return response()->json([
                'error'   => true,
                'code'    => 'PASS_NOT_FOUND',
                'message' => 'No pass found for this phone number.',
            ], 404);
        }

        $allMatches = $allMatches->map(function (Pass $p) {
            return $p->refresh()->load(['flaggedItems', 'resident.unit', 'resident.user']);
        });

        $formattedPasses = $allMatches->map(fn(Pass $p) => $this->formatPassForSecurity($p))->values()->all();

        return response()->json([
            'pass'   => $formattedPasses[0] ?? $this->formatPassForSecurity($pass),
            'passes' => $formattedPasses,
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
            'recurringDays.*' => 'string|in:Mon,Tue,Wed,Thu,Fri,Sat,Sun',
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
     * PATCH /api/v1/passes/{ulid}/allow-entry
     */
    public function allowEntry(Request $request, string $ulid)
    {
        $user = $request->user();

        if ($user->type !== 'security') {
            return response()->json([
                'error'   => true,
                'code'    => 'FORBIDDEN',
                'message' => 'Only security users can allow entry.',
            ], 403);
        }

        $pass = Pass::where('ulid', $ulid)
            ->where('estate_id', $user->estate_id)
            ->with(['flaggedItems', 'resident.unit', 'resident.user'])
            ->first();

        if (! $pass) {
            return response()->json([
                'error'   => true,
                'code'    => 'PASS_NOT_FOUND',
                'message' => 'Pass not found.',
            ], 404);
        }

        $this->maybeExpire($pass);
        $pass->refresh();

        if ($pass->status === 'Expired') {
            return response()->json([
                'error'   => true,
                'code'    => 'PASS_EXPIRED',
                'message' => 'Expired passes cannot be allowed entry.',
            ], 422);
        }

        if (in_array($pass->status, ['Revoked', 'Exited'], true)) {
            return response()->json([
                'error'   => true,
                'code'    => 'CANNOT_ALLOW_ENTRY',
                'message' => 'This pass can no longer be used for entry.',
            ], 422);
        }

        if ($pass->status !== 'On-site' || ! $pass->arrived_at) {
            $pass->update([
                'status'      => 'On-site',
                'arrived_at'  => now(),
                'verified_by' => $user->id,
            ]);
        }

        $pass->load(['flaggedItems', 'resident.unit', 'resident.user']);

        return response()->json([
            'pass' => $this->formatPassForSecurity($pass),
        ]);
    }

    /**
     * PATCH /api/v1/passes/{ulid}/decline-entry
     */
    public function declineEntry(Request $request, string $ulid)
    {
        $user = $request->user();

        if ($user->type !== 'security') {
            return response()->json([
                'error'   => true,
                'code'    => 'FORBIDDEN',
                'message' => 'Only security users can decline entry.',
            ], 403);
        }

        $pass = Pass::where('ulid', $ulid)
            ->where('estate_id', $user->estate_id)
            ->with(['flaggedItems', 'resident.unit', 'resident.user'])
            ->first();

        if (! $pass) {
            return response()->json([
                'error'   => true,
                'code'    => 'PASS_NOT_FOUND',
                'message' => 'Pass not found.',
            ], 404);
        }

        if (in_array($pass->status, ['Revoked', 'Exited', 'Expired'], true)) {
            return response()->json([
                'error'   => true,
                'code'    => 'CANNOT_DECLINE_ENTRY',
                'message' => 'This pass can no longer be declined.',
            ], 422);
        }

        $pass->update([
            'status'      => 'Declined',
            'verified_by' => $user->id,
        ]);

        $pass->load(['flaggedItems', 'resident.unit', 'resident.user']);

        return response()->json([
            'pass' => $this->formatPassForSecurity($pass),
        ]);
    }

    /**
     * PATCH /api/v1/passes/{ulid}/mark-exited
     */
    public function markExited(Request $request, string $ulid)
    {
        $user = $request->user();

        if ($user->type !== 'security') {
            return response()->json([
                'error'   => true,
                'code'    => 'FORBIDDEN',
                'message' => 'Only security users can mark exit.',
            ], 403);
        }

        $pass = Pass::where('ulid', $ulid)
            ->where('estate_id', $user->estate_id)
            ->with(['flaggedItems', 'resident.unit', 'resident.user'])
            ->first();

        if (! $pass) {
            return response()->json([
                'error'   => true,
                'code'    => 'PASS_NOT_FOUND',
                'message' => 'Pass not found.',
            ], 404);
        }

        if (in_array($pass->status, ['Revoked', 'Expired'], true)) {
            return response()->json([
                'error'   => true,
                'code'    => 'CANNOT_MARK_EXITED',
                'message' => 'This pass cannot be marked as exited.',
            ], 422);
        }

        if ($pass->status !== 'On-site') {
            return response()->json([
                'error'   => true,
                'code'    => 'PASS_NOT_ONSITE',
                'message' => 'Only on-site visitors can be marked as exited.',
            ], 422);
        }

        if (! $pass->exited_at || $pass->status !== 'Exited') {
            $pass->update([
                'status' => 'Exited',
                'exited_at' => now(),
            ]);
        }

        $pass->load(['flaggedItems', 'resident.unit', 'resident.user']);

        return response()->json([
            'pass' => $this->formatPassForSecurity($pass),
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

    private function phoneTail(?string $value): string
    {
        $digits = preg_replace('/\D+/', '', (string) $value) ?? '';

        if ($digits === '') {
            return '';
        }

        return strlen($digits) > 10
            ? substr($digits, -10)
            : $digits;
    }

    private function formatPassForSecurity(Pass $pass): array
    {
        $resident = $pass->resident;
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
            'hostUnit'      => $resident?->unit?->flat_address ?? '',
            'hostName'      => $resident?->user?->name ?? '',
            'qrData'        => $pass->qr_data,
            'passCode'      => $pass->pass_code,
            'vehiclePlate'  => $pass->vehicle_plate,
            'expiresAt'     => $pass->expires_at->toIso8601String(),
            'createdAt'     => $pass->created_at->toIso8601String(),
            'arrivedAt'     => $pass->arrived_at?->toIso8601String(),
            'exitedAt'      => $pass->exited_at?->toIso8601String(),
            'recurringDays' => $pass->recurring_days,
            'resident'      => $pass->resident,
            'flaggedItems'  => $pass->flaggedItems->map(fn($item) => [
                'id'          => (string) $item->id,
                'photoUrl'    => $item->photo_url,
                'description' => $item->description,
                'flaggedAt'   => $item->flagged_at?->toIso8601String(),
            ])->values()->all(),
        ];
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
            'passCode'      => $pass->pass_code,
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
