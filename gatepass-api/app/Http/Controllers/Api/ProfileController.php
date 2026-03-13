<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    /**
     * GET /api/v1/profile
     */
    public function show(Request $request)
    {
        $user     = $request->user();
        $resident = $user->resident()->with(['unit', 'estate'])->firstOrFail();

        return response()->json([
            'resident' => [
                'id'             => (string) $resident->id,
                'unitId'         => (string) $resident->unit_id,
                'name'           => $user->name,
                'phone'          => $user->phone,
                'flatAddress'    => $resident->unit?->flat_address ?? '',
                'estateName'     => $resident->estate?->name ?? '',
                'lane'           => $resident->unit?->lane ?? '',
                'house'          => $resident->unit?->house ?? '',
                'flat'           => $resident->unit?->flat ?? '',
                'role'           => $resident->role,
                'pushEnabled'    => (bool) $resident->push_enabled,
                'arrivalAlerts'  => (bool) $resident->arrival_alerts,
                'expiryAlerts'   => (bool) $resident->expiry_alerts,
                'isActive'       => (bool) $resident->is_active,
            ],
        ]);
    }

    /**
     * PATCH /api/v1/profile
     * Body: { name? }
     */
    public function update(Request $request)
    {
        $user     = $request->user();
        $resident = $user->resident()->firstOrFail();

        $data = $request->validate([
            'name' => 'sometimes|string|max:100',
        ]);

        if (isset($data['name'])) {
            $user->update(['name' => $data['name']]);
        }

        $resident->load(['unit', 'estate']);

        return response()->json([
            'resident' => [
                'id'          => (string) $resident->id,
                'name'        => $user->fresh()->name,
                'phone'       => $user->phone,
                'flatAddress' => $resident->unit?->flat_address ?? '',
                'estateName'  => $resident->estate?->name ?? '',
            ],
        ]);
    }

    /**
     * PATCH /api/v1/profile/preferences
     * Body: { pushEnabled?, arrivalAlerts?, expiryAlerts?, pushToken? }
     */
    public function updatePreferences(Request $request)
    {
        $resident = $request->user()->resident()->firstOrFail();

        $data = $request->validate([
            'pushEnabled'   => 'sometimes|boolean',
            'arrivalAlerts' => 'sometimes|boolean',
            'expiryAlerts'  => 'sometimes|boolean',
            'pushToken'     => 'sometimes|nullable|string|max:500',
        ]);

        $resident->update(array_filter([
            'push_enabled'   => $data['pushEnabled']   ?? null,
            'arrival_alerts' => $data['arrivalAlerts']  ?? null,
            'expiry_alerts'  => $data['expiryAlerts']   ?? null,
            'push_token'     => $data['pushToken']      ?? null,
        ], fn($v) => $v !== null));

        return response()->json(['message' => 'Preferences updated.']);
    }
}
