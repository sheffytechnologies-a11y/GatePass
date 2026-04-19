<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pass;
use App\Models\User;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * GET /api/v1/home/summary
     * Returns dashboard data: resident info, stats, active passes, recent notifications
     */
    public function summary(Request $request)
    {
        $user = $request->user();

        if ($user->type === 'security') {
            return $this->securitySummary();
        }

        return $this->residentSummary($user);
    }

    private function securitySummary()
    {
        $todayPasses = Pass::where(function ($q) {
                $q->whereDate('created_at', today())
                  ->orWhere('status', 'On-site');
            })
            ->with(['flaggedItems', 'resident.unit', 'resident.user'])
            ->latest()
            ->get()
            ->sortByDesc(fn($p) => match ($p->status) {
                'On-site' => 3, 'Pending' => 2, default => 1
            })
            ->values();

        return response()->json([
            'userType' => 'security',
            'resident' => null,
            'stats' => [
                'onPremises'   => Pass::where('status', 'On-site')->count(),
                'activePasses' => Pass::whereIn('status', ['Pending', 'On-site'])->count(),
                'newToday'     => Pass::whereDate('created_at', today())->count(),
            ],
            'recentPasses'  => $todayPasses->map(fn($pass) => $this->formatPassForSecurity($pass)),
            'notifications' => [],
        ]);
    }

    private function residentSummary(User $user)
    {
        $resident = $user->resident()->with(['unit', 'estate'])->firstOrFail();
        // Stats
        $onPremises  = Pass::where('resident_id', $resident->id)->where('status', 'On-site')->count();
        $activePasses = Pass::where('resident_id', $resident->id)->whereIn('status', ['Pending', 'On-site'])->count();
        $newToday    = Pass::where('resident_id', $resident->id)
                          ->whereDate('created_at', today())
                          ->count();

        // Active passes (for home screen cards)
        $activePasses_list = Pass::where('resident_id', $resident->id)
            ->whereIn('status', ['Pending', 'On-site'])
            ->with('flaggedItems')
            ->latest()
            ->limit(5)
            ->get();

        // Recent notifications
        $notifications = $resident->notifications()
            ->latest()
            ->limit(10)
            ->get();

        return response()->json([
            'userType' => 'resident',
            'resident' => [
                'id'          => (string) $resident->id,
                'unitId'      => (string) $resident->unit_id,
                'name'        => $user->name,
                'phone'       => $user->phone,
                'flatAddress' => $resident->unit?->flat_address ?? '',
                'estateName'  => $resident->estate?->name ?? '',
                'lane'        => $resident->unit?->lane ?? '',
                'house'       => $resident->unit?->house ?? '',
                'flat'        => $resident->unit?->flat ?? '',
                'role'        => $resident->role,
                'pushEnabled' => (bool) $resident->push_enabled,
                'isActive'    => (bool) $resident->is_active,
            ],
            'stats' => [
                'onPremises'  => $onPremises,
                'activePasses'=> $activePasses,
                'newToday'    => $newToday,
            ],
            'recentPasses' => $activePasses_list->map(function ($pass) use ($resident) {
                return $this->formatPass($pass, $resident);
            }),
            'notifications' => $notifications->map(fn($n) => [
                'id'        => (string) $n->id,
                'type'      => $n->type,
                'message'   => $n->message,
                'passId'    => $n->pass_id ? (string) $n->pass?->ulid : null,
                'read'      => (bool) $n->read,
                'createdAt' => $n->created_at->toIso8601String(),
            ]),
        ]);
    }

    private function formatPassForSecurity($pass): array
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
            'vehiclePlate'  => $pass->vehicle_plate,
            'expiresAt'     => $pass->expires_at->toIso8601String(),
            'createdAt'     => $pass->created_at->toIso8601String(),
            'arrivedAt'     => $pass->arrived_at?->toIso8601String(),
            'exitedAt'      => $pass->exited_at?->toIso8601String(),
            'recurringDays' => $pass->recurring_days,
            'flaggedItems'  => $pass->flaggedItems->map(fn($item) => [
                'id'          => (string) $item->id,
                'photoUrl'    => $item->photo_url,
                'description' => $item->description,
                'flaggedAt'   => $item->flagged_at->toIso8601String(),
            ])->values()->all(),
        ];
    }

    private function formatPass($pass, $resident): array
    {
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
                'photoUrl'    => $item->photo_url,
                'description' => $item->description,
                'flaggedAt'   => $item->created_at->toIso8601String(),
            ])->values()->all(),
        ];
    }
}
