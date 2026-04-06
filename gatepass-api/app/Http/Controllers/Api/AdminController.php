<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Emergency;
use App\Models\Notification;
use App\Models\Pass;
use App\Models\Resident;
use App\Models\User;
use App\Models\Estate;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    // ── Middleware helper ──────────────────────────────────────────────────

    private function requireAdmin(Request $request)
    {
        $user = $request->user();
        if (! $user || $user->type !== 'security') {
            abort(403, 'Admin access required.');
        }
        return $user;
    }

    // ── Users ──────────────────────────────────────────────────────────────

    /**
     * GET /api/v1/admin/users
     */
    public function listUsers(Request $request)
    {
        $this->requireAdmin($request);

        $query = User::query();

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('phone', 'like', '%' . $request->search . '%');
            });
        }

        $users = $query->latest()->get()->map(fn($u) => $this->formatUser($u));

        return response()->json(['users' => $users]);
    }

    /**
     * POST /api/v1/admin/users
     */
    public function createUser(Request $request)
    {
        $this->requireAdmin($request);

        $data = $request->validate([
            'name'     => 'required|string|max:255',
            'phone'    => 'required|string|unique:users,phone',
            'password' => 'required|string|min:6',
            'type'     => 'required|in:resident,security',
            'email'    => 'nullable|email|unique:users,email',
        ]);

        $user = User::create([
            'name'      => $data['name'],
            'phone'     => $data['phone'],
            'email'     => $data['email'] ?? null,
            'password'  => Hash::make($data['password']),
            'type'      => $data['type'],
            'is_active' => true,
        ]);

        return response()->json(['user' => $this->formatUser($user)], 201);
    }

    /**
     * GET /api/v1/admin/users/{id}
     */
    public function showUser(Request $request, int $id)
    {
        $this->requireAdmin($request);
        $user = User::findOrFail($id);
        return response()->json(['user' => $this->formatUser($user)]);
    }

    /**
     * PATCH /api/v1/admin/users/{id}
     */
    public function updateUser(Request $request, int $id)
    {
        $this->requireAdmin($request);
        $user = User::findOrFail($id);

        $data = $request->validate([
            'name'      => 'sometimes|string|max:255',
            'phone'     => ['sometimes', 'string', Rule::unique('users', 'phone')->ignore($id)],
            'password'  => 'sometimes|string|min:6',
            'type'      => 'sometimes|in:resident,security',
            'is_active' => 'sometimes|boolean',
            'email'     => ['sometimes', 'nullable', 'email', Rule::unique('users', 'email')->ignore($id)],
        ]);

        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        $user->update($data);

        return response()->json(['user' => $this->formatUser($user->fresh())]);
    }

    /**
     * DELETE /api/v1/admin/users/{id}
     */
    public function deleteUser(Request $request, int $id)
    {
        $this->requireAdmin($request);
        $user = User::findOrFail($id);
        $user->delete();
        return response()->json(['message' => 'User deleted.']);
    }

    // ── Residents ──────────────────────────────────────────────────────────

    /**
     * GET /api/v1/admin/residents
     */
    public function listResidents(Request $request)
    {
        $this->requireAdmin($request);

        $query = Resident::with(['user', 'unit', 'estate']);

        if ($request->filled('estate_id')) {
            $query->where('estate_id', $request->estate_id);
        }
        if ($request->filled('search')) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('phone', 'like', '%' . $request->search . '%');
            });
        }

        $residents = $query->latest()->get()->map(fn($r) => $this->formatResident($r));

        return response()->json(['residents' => $residents]);
    }

    /**
     * GET /api/v1/admin/residents/{id}
     */
    public function showResident(Request $request, int $id)
    {
        $this->requireAdmin($request);
        $resident = Resident::with(['user', 'unit', 'estate'])->findOrFail($id);
        return response()->json(['resident' => $this->formatResident($resident)]);
    }

    /**
     * POST /api/v1/admin/residents
     * Creates a user + resident record together
     */
    public function createResident(Request $request)
    {
        $this->requireAdmin($request);

        $data = $request->validate([
            'name'      => 'required|string|max:255',
            'phone'     => 'required|string|unique:users,phone',
            'password'  => 'required|string|min:6',
            'email'     => 'nullable|email|unique:users,email',
            'unit_id'   => 'required|exists:units,id',
            'estate_id' => 'required|exists:estates,id',
            'role'      => 'nullable|string|in:owner,tenant',
        ]);

        $user = User::create([
            'name'      => $data['name'],
            'phone'     => $data['phone'],
            'email'     => $data['email'] ?? null,
            'password'  => Hash::make($data['password']),
            'type'      => 'resident',
            'is_active' => true,
        ]);

        $resident = Resident::create([
            'user_id'   => $user->id,
            'unit_id'   => $data['unit_id'],
            'estate_id' => $data['estate_id'],
            'role'      => $data['role'] ?? 'tenant',
            'is_active' => true,
        ]);

        $resident->load(['user', 'unit', 'estate']);

        return response()->json(['resident' => $this->formatResident($resident)], 201);
    }

    /**
     * PATCH /api/v1/admin/residents/{id}
     */
    public function updateResident(Request $request, int $id)
    {
        $this->requireAdmin($request);
        $resident = Resident::with(['user', 'unit', 'estate'])->findOrFail($id);

        $data = $request->validate([
            'unit_id'   => 'sometimes|exists:units,id',
            'estate_id' => 'sometimes|exists:estates,id',
            'role'      => 'sometimes|string|in:owner,tenant',
            'is_active' => 'sometimes|boolean',
        ]);

        $resident->update($data);

        return response()->json(['resident' => $this->formatResident($resident->fresh()->load(['user', 'unit', 'estate']))]);
    }

    /**
     * DELETE /api/v1/admin/residents/{id}
     */
    public function deleteResident(Request $request, int $id)
    {
        $this->requireAdmin($request);
        $resident = Resident::findOrFail($id);
        $resident->delete();
        return response()->json(['message' => 'Resident deleted.']);
    }

    // ── Passes ─────────────────────────────────────────────────────────────

    /**
     * GET /api/v1/admin/passes
     */
    public function listPasses(Request $request)
    {
        $this->requireAdmin($request);

        $query = Pass::with(['resident.user', 'resident.unit', 'flaggedItems']);

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }
        if ($request->filled('search')) {
            $query->where('visitor_name', 'like', '%' . $request->search . '%');
        }

        $passes = $query->latest()->paginate(30);

        return response()->json([
            'passes' => collect($passes->items())->map(fn($p) => $this->formatPassAdmin($p)),
            'total'  => $passes->total(),
            'page'   => $passes->currentPage(),
            'pages'  => $passes->lastPage(),
        ]);
    }

    /**
     * GET /api/v1/admin/passes/{ulid}
     */
    public function showPass(Request $request, string $ulid)
    {
        $this->requireAdmin($request);
        $pass = Pass::where('ulid', $ulid)->with(['resident.user', 'resident.unit', 'resident.estate', 'flaggedItems'])->firstOrFail();
        return response()->json(['pass' => $this->formatPassAdmin($pass)]);
    }

    /**
     * POST /api/v1/admin/passes
     */
    public function createPass(Request $request)
    {
        $this->requireAdmin($request);

        $data = $request->validate([
            'resident_id'   => 'required|exists:residents,id',
            'visitor_name'  => 'required|string|max:255',
            'visitor_phone' => 'nullable|string|max:30',
            'vehicle_plate' => 'nullable|string|max:30',
            'purpose'       => 'nullable|string|max:500',
            'type'          => 'required|in:one-time,recurring',
            'expires_at'    => 'nullable|date',
        ]);

        $resident = Resident::findOrFail($data['resident_id']);

        $pass = Pass::create([
            'resident_id'   => $resident->id,
            'estate_id'     => $resident->estate_id,
            'visitor_name'  => $data['visitor_name'],
            'visitor_phone' => $data['visitor_phone'] ?? null,
            'vehicle_plate' => $data['vehicle_plate'] ?? null,
            'purpose'       => $data['purpose'] ?? null,
            'type'          => $data['type'],
            'expires_at'    => $data['expires_at'] ?? null,
            'status'        => 'Pending',
        ]);

        $pass->load(['resident.user', 'resident.unit', 'flaggedItems']);

        return response()->json(['pass' => $this->formatPassAdmin($pass)], 201);
    }

    /**
     * PATCH /api/v1/admin/passes/{ulid}/revoke
     */
    public function revokePass(Request $request, string $ulid)
    {
        $this->requireAdmin($request);
        $pass = Pass::where('ulid', $ulid)->firstOrFail();
        $pass->update(['status' => 'Revoked', 'revoked_at' => now()]);
        return response()->json(['message' => 'Pass revoked.']);
    }

    /**
     * DELETE /api/v1/admin/passes/{ulid}
     */
    public function deletePass(Request $request, string $ulid)
    {
        $this->requireAdmin($request);
        $pass = Pass::where('ulid', $ulid)->firstOrFail();
        $pass->delete();
        return response()->json(['message' => 'Pass deleted.']);
    }

    // ── Emergencies ────────────────────────────────────────────────────────

    /**
     * GET /api/v1/admin/emergencies
     */
    public function listEmergencies(Request $request)
    {
        $this->requireAdmin($request);

        $query = Emergency::with(['resident.user', 'estate']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $emergencies = $query->latest()->get()->map(fn($e) => $this->formatEmergency($e));

        return response()->json(['emergencies' => $emergencies]);
    }

    /**
     * PATCH /api/v1/admin/emergencies/{id}/acknowledge
     */
    public function acknowledgeEmergency(Request $request, int $id)
    {
        $this->requireAdmin($request);
        $emergency = Emergency::findOrFail($id);
        $emergency->update(['status' => 'acknowledged', 'acknowledged_at' => now()]);
        return response()->json(['message' => 'Emergency acknowledged.']);
    }

    /**
     * PATCH /api/v1/admin/emergencies/{id}/resolve
     */
    public function resolveEmergency(Request $request, int $id)
    {
        $this->requireAdmin($request);
        $emergency = Emergency::findOrFail($id);
        $emergency->update(['status' => 'resolved', 'resolved_at' => now()]);
        return response()->json(['message' => 'Emergency resolved.']);
    }

    /**
     * DELETE /api/v1/admin/emergencies/{id}
     */
    public function deleteEmergency(Request $request, int $id)
    {
        $this->requireAdmin($request);
        $emergency = Emergency::findOrFail($id);
        $emergency->delete();
        return response()->json(['message' => 'Emergency deleted.']);
    }

    // ── Notifications ──────────────────────────────────────────────────────

    /**
     * GET /api/v1/admin/notifications
     */
    public function listNotifications(Request $request)
    {
        $this->requireAdmin($request);

        $notifications = Notification::with(['resident.user'])
            ->latest()
            ->limit(100)
            ->get()
            ->map(fn($n) => $this->formatNotification($n));

        return response()->json(['notifications' => $notifications]);
    }

    /**
     * DELETE /api/v1/admin/notifications/{id}
     */
    public function deleteNotification(Request $request, int $id)
    {
        $this->requireAdmin($request);
        Notification::findOrFail($id)->delete();
        return response()->json(['message' => 'Notification deleted.']);
    }

    // ── Estates & Units ────────────────────────────────────────────────────

    /**
     * GET /api/v1/admin/estates
     */
    public function listEstates(Request $request)
    {
        $this->requireAdmin($request);
        $estates = Estate::all()->map(fn($e) => ['id' => $e->id, 'name' => $e->name]);
        return response()->json(['estates' => $estates]);
    }

    /**
     * GET /api/v1/admin/estates/{id}/units
     */
    public function listUnits(Request $request, int $estateId)
    {
        $this->requireAdmin($request);
        $units = Unit::where('estate_id', $estateId)->get()->map(fn($u) => ['id' => $u->id, 'number' => $u->number]);
        return response()->json(['units' => $units]);
    }

    // ── Dashboard summary ──────────────────────────────────────────────────

    /**
     * GET /api/v1/admin/dashboard
     */
    public function dashboard(Request $request)
    {
        $this->requireAdmin($request);

        return response()->json([
            'stats' => [
                'totalUsers'        => User::count(),
                'totalResidents'    => Resident::count(),
                'activePasses'      => Pass::whereIn('status', ['Pending', 'On-site'])->count(),
                'onSite'            => Pass::where('status', 'On-site')->count(),
                'todayEmergencies'  => Emergency::whereDate('created_at', today())->count(),
                'flaggedItems'      => Pass::where('items_flagged', true)->count(),
            ],
            'recentPasses' => Pass::with(['resident.user', 'resident.unit'])
                ->latest()->limit(10)->get()
                ->map(fn($p) => $this->formatPassAdmin($p)),
            'recentEmergencies' => Emergency::with(['resident.user', 'estate'])
                ->latest()->limit(5)->get()
                ->map(fn($e) => $this->formatEmergency($e)),
        ]);
    }

    // ── Formatters ─────────────────────────────────────────────────────────

    private function formatUser(User $u): array
    {
        return [
            'id'        => $u->id,
            'name'      => $u->name,
            'phone'     => $u->phone,
            'email'     => $u->email,
            'type'      => $u->type,
            'isActive'  => $u->is_active,
            'createdAt' => $u->created_at?->toIso8601String(),
        ];
    }

    private function formatResident(Resident $r): array
    {
        return [
            'id'        => $r->id,
            'user'      => $r->user ? $this->formatUser($r->user) : null,
            'unit'      => $r->unit  ? ['id' => $r->unit->id,  'number' => $r->unit->number]  : null,
            'estate'    => $r->estate? ['id' => $r->estate->id,'name'   => $r->estate->name]  : null,
            'role'      => $r->role,
            'isActive'  => $r->is_active,
            'createdAt' => $r->created_at?->toIso8601String(),
        ];
    }

    private function formatPassAdmin(Pass $p): array
    {
        return [
            'id'           => $p->id,
            'ulid'         => $p->ulid,
            'visitorName'  => $p->visitor_name,
            'visitorPhone' => $p->visitor_phone,
            'vehiclePlate' => $p->vehicle_plate,
            'purpose'      => $p->purpose,
            'type'         => $p->type,
            'status'       => $p->status,
            'itemsFlagged' => $p->items_flagged,
            'expiresAt'    => $p->expires_at?->toIso8601String(),
            'arrivedAt'    => $p->arrived_at?->toIso8601String(),
            'exitedAt'     => $p->exited_at?->toIso8601String(),
            'revokedAt'    => $p->revoked_at?->toIso8601String(),
            'createdAt'    => $p->created_at?->toIso8601String(),
            'resident'     => $p->resident ? [
                'id'   => $p->resident->id,
                'name' => $p->resident->user?->name,
                'unit' => $p->resident->unit?->number,
            ] : null,
        ];
    }

    private function formatEmergency(Emergency $e): array
    {
        return [
            'id'               => $e->id,
            'type'             => $e->type,
            'notes'            => $e->notes,
            'status'           => $e->status,
            'acknowledgedAt'   => $e->acknowledged_at?->toIso8601String(),
            'resolvedAt'       => $e->resolved_at?->toIso8601String(),
            'createdAt'        => $e->created_at?->toIso8601String(),
            'resident'         => $e->resident ? [
                'id'   => $e->resident->id,
                'name' => $e->resident->user?->name,
            ] : null,
            'estate'           => $e->estate ? ['id' => $e->estate->id, 'name' => $e->estate->name] : null,
        ];
    }

    private function formatNotification(Notification $n): array
    {
        return [
            'id'       => $n->id,
            'type'     => $n->type,
            'message'  => $n->message,
            'read'     => $n->read,
            'readAt'   => $n->read_at?->toIso8601String(),
            'createdAt'=> $n->created_at?->toIso8601String(),
            'resident' => $n->resident ? [
                'id'   => $n->resident->id,
                'name' => $n->resident->user?->name,
            ] : null,
        ];
    }
}
