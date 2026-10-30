<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Emergency;
use App\Models\Notification;
use Illuminate\Http\Request;

class EmergencyController extends Controller
{
    /**
     * POST /api/v1/emergencies
     * Body: { type, notes? }
     */
    public function store(Request $request)
    {
        $resident = $request->user()->resident()->with(['estate'])->firstOrFail();

        $data = $request->validate([
            'type'  => 'required|string|in:Security Incident,Fire,Medical,Intruder',
            'notes' => 'nullable|string|max:1000',
        ]);

        $emergency = Emergency::create([
            'resident_id' => $resident->id,
            'estate_id'   => $resident->estate_id,
            'type'        => $data['type'],
            'notes'       => $data['notes'] ?? null,
            'status'      => 'sent',
        ]);

        // Create a notification for the resident (and ideally broadcast to security staff)
        Notification::create([
            'resident_id' => $resident->id,
            'pass_id'     => null,
            'type'        => 'emergency',
            'message'     => "Emergency alert sent: {$data['type']}",
            'read'        => false,
        ]);

        return response()->json([
            'emergency' => [
                'id'        => (string) $emergency->id,
                'type'      => $emergency->type,
                'status'    => $emergency->status,
                'createdAt' => $emergency->created_at->toIso8601String(),
            ],
            'message' => 'Emergency alert sent to security.',
        ], 201);
    }

    /**
     * GET /api/v1/emergencies
     * Returns the resident's recent emergency alerts
     */
    public function index(Request $request)
    {
        $resident = $request->user()->resident()->firstOrFail();

        $emergencies = Emergency::where('resident_id', $resident->id)
            ->latest()
            ->limit(20)
            ->get();

        return response()->json([
            'incidents' => $emergencies->map(fn($e) => [
                'id'        => (string) $e->id,
                'type'      => $e->type,
                'status'    => $e->status,
                'createdAt' => $e->created_at->toIso8601String(),
            ]),
        ]);
    }
}
