<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\Concerns\EnforcesPlanLimits;
use App\Http\Controllers\Api\Concerns\ScopesToEstate;
use App\Models\Emergency;
use App\Models\Notification;
use App\Models\Pass;
use App\Models\Resident;
use App\Models\User;
use App\Models\Admin;
use App\Models\Estate;
use App\Models\Unit;
use App\Models\Fee;
use App\Models\BillingTransaction;
use Illuminate\Support\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    use ScopesToEstate, EnforcesPlanLimits;

    // ── Users ──────────────────────────────────────────────────────────────

    /**
     * GET /api/v1/admin/users
     */
    public function listUsers(Request $request)
    {
        $all = collect();

        // Include Admins unless specifically filtering for resident or security
        $includeAdmins = !$request->filled('type') || in_array($request->type, ['admin', 'staff']);
        if ($includeAdmins) {
            $adminQuery = Admin::query();
            if ($request->filled('search')) {
                $s = $request->search;
                $adminQuery->where(function ($q) use ($s) {
                    $q->where('name', 'like', "%{$s}%")
                      ->orWhere('email', 'like', "%{$s}%");
                });
            }
            if ($request->filled('status')) {
                if ($request->status === 'active') {
                    $adminQuery->where('is_active', true);
                } elseif ($request->status === 'inactive') {
                    $adminQuery->where('is_active', false);
                }
            }
            $admins = $adminQuery->latest()->get()->map(function (Admin $a) {
                return [
                    'id'           => 'admin_' . $a->id,
                    'rawId'        => $a->id,
                    'name'         => $a->name,
                    'phone'        => null,
                    'email'        => $a->email,
                    'avatar'       => null,
                    'type'         => 'admin',
                    'isActive'     => (bool) $a->is_active,
                    'residentId'   => null,
                    'residentRole' => null,
                    'unitAddress'  => null,
                    'createdAt'    => $a->created_at?->toIso8601String(),
                ];
            });
            $all = $all->concat($admins);
        }

        // Include Users table records
        if ($request->type !== 'admin') {
            $query = User::with(['resident.unit', 'resident.estate']);
            $this->scopeEstate($query, $request);

            if ($request->filled('type')) {
                if ($request->type === 'staff') {
                    $query->whereIn('type', ['admin', 'security']);
                } else {
                    $query->where('type', $request->type);
                }
            }
            if ($request->filled('status')) {
                if ($request->status === 'active') {
                    $query->where('is_active', true);
                } elseif ($request->status === 'inactive') {
                    $query->where('is_active', false);
                }
            }
            if ($request->filled('search')) {
                $query->where(function ($q) use ($request) {
                    $q->where('name', 'like', '%' . $request->search . '%')
                      ->orWhere('phone', 'like', '%' . $request->search . '%')
                      ->orWhere('email', 'like', '%' . $request->search . '%');
                });
            }

            $users = $query->latest()->get()->map(fn(User $u) => $this->formatUser($u));
            $all = $all->concat($users);
        }

        return response()->json(['users' => $all->values()]);
    }

    /**
     * POST /api/v1/admin/users
     */
    public function createUser(Request $request)
    {
        $data = $request->validate([
            'name'     => 'required|string|max:255',
            'phone'    => 'nullable|string',
            'password' => 'required|string|min:6',
            'type'     => 'required|in:admin,security,resident',
            'email'    => 'nullable|email',
            'estate_id'=> 'nullable|exists:estates,id',
        ]);

        if ($data['type'] === 'admin') {
            if (!empty($data['email'])) {
                $exists = Admin::where('email', $data['email'])->exists();
                if ($exists) {
                    return response()->json(['message' => 'An administrator with this email already exists.'], 422);
                }
            }
            $admin = Admin::create([
                'name'      => $data['name'],
                'email'     => $data['email'] ?? strtolower(str_replace(' ', '', $data['name'])) . '@checkpass.com',
                'password'  => Hash::make($data['password']),
                'role'      => 'estate_admin',
                'is_active' => true,
            ]);
            return response()->json(['user' => [
                'id'        => 'admin_' . $admin->id,
                'name'      => $admin->name,
                'email'     => $admin->email,
                'phone'     => null,
                'type'      => 'admin',
                'isActive'  => true,
                'createdAt' => $admin->created_at?->toIso8601String(),
            ]], 201);
        }

        if (empty($data['phone'])) {
            return response()->json(['message' => 'Phone number is required for security personnel and residents.'], 422);
        }

        $existsPhone = User::where('phone', $data['phone'])->exists();
        if ($existsPhone) {
            return response()->json(['message' => 'A user with this phone number already exists.'], 422);
        }

        $estateId = $this->resolveEstateId($request, $data['estate_id'] ?? null);
        $this->assertEstateAccess($request, $estateId);

        $user = User::create([
            'name'      => $data['name'],
            'phone'     => $data['phone'],
            'email'     => $data['email'] ?? null,
            'password'  => Hash::make($data['password']),
            'type'      => $data['type'],
            'estate_id' => $estateId,
            'is_active' => true,
        ]);

        return response()->json(['user' => $this->formatUser($user)], 201);
    }

    /**
     * GET /api/v1/admin/users/{id}
     */
    public function showUser(Request $request, $id)
    {
        if (str_starts_with((string)$id, 'admin_')) {
            $realId = (int) str_replace('admin_', '', (string)$id);
            $admin = Admin::findOrFail($realId);
            return response()->json(['user' => [
                'id'        => 'admin_' . $admin->id,
                'name'      => $admin->name,
                'email'     => $admin->email,
                'phone'     => null,
                'type'      => 'admin',
                'isActive'  => (bool) $admin->is_active,
                'createdAt' => $admin->created_at?->toIso8601String(),
            ]]);
        }

        $user = User::findOrFail($id);
        $this->assertEstateAccess($request, $user->estate_id);
        return response()->json(['user' => $this->formatUser($user)]);
    }

    /**
     * PATCH /api/v1/admin/users/{id}
     */
    public function updateUser(Request $request, $id)
    {
        if (str_starts_with((string)$id, 'admin_')) {
            $realId = (int) str_replace('admin_', '', (string)$id);
            $admin = Admin::findOrFail($realId);

            $data = $request->validate([
                'name'      => 'sometimes|string|max:255',
                'email'     => ['sometimes', 'nullable', 'email', Rule::unique('admins', 'email')->ignore($realId)],
                'password'  => 'sometimes|string|min:6',
                'is_active' => 'sometimes|boolean',
            ]);

            if (isset($data['password'])) {
                $data['password'] = Hash::make($data['password']);
            }

            $admin->update($data);

            return response()->json(['user' => [
                'id'        => 'admin_' . $admin->id,
                'name'      => $admin->name,
                'email'     => $admin->email,
                'phone'     => null,
                'type'      => 'admin',
                'isActive'  => (bool) $admin->is_active,
                'createdAt' => $admin->created_at?->toIso8601String(),
            ]]);
        }

        $user = User::findOrFail($id);
        $this->assertEstateAccess($request, $user->estate_id);

        $data = $request->validate([
            'name'      => 'sometimes|string|max:255',
            'phone'     => ['sometimes', 'nullable', 'string', Rule::unique('users', 'phone')->ignore($id)],
            'password'  => 'sometimes|string|min:6',
            'type'      => 'sometimes|in:resident,security,admin',
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
    public function deleteUser(Request $request, $id)
    {
        if (str_starts_with((string)$id, 'admin_')) {
            $realId = (int) str_replace('admin_', '', (string)$id);
            $admin = Admin::findOrFail($realId);
            $admin->delete();
            return response()->json(['message' => 'Admin deleted.']);
        }

        $user = User::findOrFail($id);
        $this->assertEstateAccess($request, $user->estate_id);
        $user->delete();
        return response()->json(['message' => 'User deleted.']);
    }

    // ── Residents ──────────────────────────────────────────────────────────

    /**
     * GET /api/v1/admin/residents
     */
    public function listResidents(Request $request)
    {
        $query = Resident::with(['user', 'unit', 'estate']);
        $this->scopeEstate($query, $request);

        if ($request->filled('search')) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('phone', 'like', '%' . $request->search . '%');
            });
        }

        $residents = $query->latest()->get()->map(fn(Resident $r) => $this->formatResident($r));

        return response()->json(['residents' => $residents]);
    }

    /**
     * GET /api/v1/admin/residents/{id}
     */
    public function showResident(Request $request, int $id)
    {
        $resident = Resident::with(['user', 'unit', 'estate'])->findOrFail($id);
        $this->assertEstateAccess($request, $resident->estate_id);

        // Fetch co-residents in the same unit
        $coResidents = [];
        if ($resident->unit_id) {
            $coResidents = Resident::where('unit_id', $resident->unit_id)
                ->where('id', '!=', $resident->id)
                ->with(['user', 'unit'])
                ->get()
                ->map(fn($cr) => $this->formatResident($cr));
        }

        // Fetch recent passes issued by this resident
        $passes = Pass::where('resident_id', $resident->id)
            ->latest()
            ->take(20)
            ->get()
            ->map(fn($p) => $this->formatPassAdmin($p));

        // Fetch dues / fee records
        $dues = [];
        if ($resident->user_id) {
            try {
                $dues = \DB::table('fee_user')
                    ->where('fee_user.user_id', $resident->user_id)
                    ->join('fees', 'fees.id', '=', 'fee_user.fee_id')
                    ->select(
                        'fee_user.id',
                        'fees.name as fee_name',
                        'fees.amount',
                        \DB::raw("COALESCE(fee_user.payment_status, 'due') as status"),
                        \DB::raw("fee_user.verified_at as paid_at"),
                        'fee_user.created_at'
                    )
                    ->latest('fee_user.created_at')
                    ->take(20)
                    ->get();
            } catch (\Throwable $e) {
                \Log::warning('Failed to fetch resident dues: ' . $e->getMessage());
                $dues = [];
            }
        }

        // Sibling resident IDs in current estate for fast navigation
        $navIds = Resident::where('estate_id', $resident->estate_id)
            ->orderBy('id')
            ->pluck('id');

        return response()->json([
            'resident'    => $this->formatResident($resident),
            'coResidents' => $coResidents,
            'passes'      => $passes,
            'dues'        => $dues,
            'navIds'      => $navIds,
        ]);
    }

    /**
     * POST /api/v1/admin/residents
     * Creates a user + resident record together
     */
    public function createResident(Request $request)
    {
        $data = $request->validate([
            'name'      => 'required|string|max:255',
            'phone'     => 'required|string|max:30',
            'password'  => 'nullable|string|min:6',
            'email'     => 'nullable|email',
            'unit_id'   => 'nullable|exists:units,id',
            'estate_id' => 'nullable|exists:estates,id',
            'role'      => 'nullable|string|in:owner,tenant',
            'lane'      => 'nullable|string|max:100',
            'house'     => 'nullable|string|max:100',
            'flat'      => 'nullable|string|max:100',
            'avatar'    => 'nullable|string',
        ]);

        $estateId = $this->resolveEstateId($request, $data['estate_id'] ?? null);
        $this->assertEstateAccess($request, $estateId);
        if (! $estateId) {
            abort(422, 'estate_id is required.');
        }

        $estate = Estate::findOrFail($estateId);
        $this->assertNotPastDue($estate, $request->user());

        $unit = $this->resolveResidentUnit($estate, $data);
        $this->assertCanAddResident($unit);

        return DB::transaction(function () use ($data, $estateId, $unit) {
            $user = User::where('phone', $data['phone'])->first();

            if ($user) {
                // If email provided, verify it does not conflict with another user
                if (! empty($data['email']) && User::where('email', $data['email'])->where('id', '!=', $user->id)->exists()) {
                    abort(422, 'The email address is already in use by another user.');
                }
                // Check if user is already a resident in this unit
                if (Resident::where('user_id', $user->id)->where('unit_id', $unit->id)->exists()) {
                    abort(422, 'A resident with this phone number is already registered in this unit.');
                }
                // Update user details if needed
                $userUpdates = [];
                if (! empty($data['name']) && (empty($user->name) || $user->name === 'Resident')) {
                    $userUpdates['name'] = $data['name'];
                }
                if (! empty($data['email']) && empty($user->email)) {
                    $userUpdates['email'] = $data['email'];
                }
                if (! empty($data['avatar'])) {
                    $userUpdates['avatar'] = $data['avatar'];
                }
                if (! empty($userUpdates)) {
                    $user->update($userUpdates);
                }

                $plainPassword = $data['password'] ?? null;
            } else {
                if (! empty($data['email']) && User::where('email', $data['email'])->exists()) {
                    abort(422, 'The email address is already in use by another user.');
                }

                $plainPassword = $data['password'] ?? Str::random(10);

                $user = User::create([
                    'name'      => $data['name'],
                    'phone'     => $data['phone'],
                    'email'     => $data['email'] ?? null,
                    'avatar'    => $data['avatar'] ?? null,
                    'password'  => Hash::make($plainPassword),
                    'type'      => 'resident',
                    'estate_id' => $estateId,
                    'is_active' => true,
                ]);
            }

            $resident = Resident::create([
                'user_id'   => $user->id,
                'unit_id'   => $unit->id,
                'estate_id' => $estateId,
                'role'      => $data['role'] ?? 'tenant',
                'is_active' => true,
            ]);

            $resident->load(['user', 'unit', 'estate']);

            return response()->json([
                'resident' => $this->formatResident($resident),
                'temporaryPassword' => $plainPassword,
            ], 201);
        });
    }

    /**
     * Resolve the unit for a new resident: an explicit unit_id wins, otherwise
     * find-or-create a unit within the estate matching lane/house/flat.
     */
    private function resolveResidentUnit(Estate $estate, array $data): Unit
    {
        if (! empty($data['unit_id'])) {
            $unit = Unit::findOrFail($data['unit_id']);
            if ($unit->estate_id !== $estate->id) {
                abort(422, 'The selected unit does not belong to this estate.');
            }
            return $unit;
        }

        $lane = $data['lane'] ?? null;
        $house = $data['house'] ?? null;
        $flat = $data['flat'] ?? null;

        if (! $lane && ! $house && ! $flat) {
            abort(422, 'A unit is required — provide unit_id or lane/house/flat.');
        }

        $unit = Unit::where('estate_id', $estate->id)
            ->where('lane', $lane)
            ->where('house', $house)
            ->where('flat', $flat)
            ->first();

        if ($unit) {
            return $unit;
        }

        $this->assertCanAddUnit($estate);

        $flatAddress = collect([$flat, $house, $lane])->filter()->implode(', ') ?: 'Unit';

        return Unit::create([
            'estate_id'    => $estate->id,
            'lane'         => $lane,
            'house'        => $house,
            'flat'         => $flat,
            'flat_address' => $flatAddress,
            'is_active'    => true,
        ]);
    }

    /**
     * PATCH /api/v1/admin/residents/{id}
     */
    public function updateResident(Request $request, int $id)
    {
        $resident = Resident::with(['user', 'unit', 'estate'])->findOrFail($id);
        $this->assertEstateAccess($request, $resident->estate_id);

        $data = $request->validate([
            'name'      => 'sometimes|string|max:255',
            'phone'     => 'sometimes|string|max:30',
            'email'     => 'nullable|email|max:255',
            'avatar'    => 'nullable|string',
            'unit_id'   => 'nullable|exists:units,id',
            'estate_id' => 'sometimes|exists:estates,id',
            'role'      => 'sometimes|string|in:owner,tenant',
            'is_active' => 'sometimes|boolean',
        ]);

        \DB::transaction(function () use ($resident, $data) {
            // Update user details if supplied
            $userFields = array_intersect_key($data, array_flip(['name', 'phone', 'email', 'avatar', 'is_active']));
            if (! empty($userFields) && $resident->user) {
                // If phone is being changed, ensure it doesn't collide with another user
                if (isset($userFields['phone'])) {
                    $existingUser = User::where('phone', $userFields['phone'])->where('id', '!=', $resident->user_id)->first();
                    if ($existingUser) {
                        abort(422, 'Another account with this phone number already exists.');
                    }
                }
                $resident->user->update($userFields);
            }

            // Update resident fields
            $residentFields = array_intersect_key($data, array_flip(['unit_id', 'estate_id', 'role', 'is_active']));
            if (! empty($residentFields)) {
                $resident->update($residentFields);
            }
        });

        $resident->refresh()->load(['user', 'unit', 'estate']);

        return response()->json([
            'resident' => $this->formatResident($resident),
            'message'  => 'Resident updated successfully.',
        ]);
    }

    /**
     * DELETE /api/v1/admin/residents/{id}
     */
    public function deleteResident(Request $request, int $id)
    {
        $resident = Resident::findOrFail($id);
        $this->assertEstateAccess($request, $resident->estate_id);
        $resident->delete();
        return response()->json(['message' => 'Resident deleted.']);
    }

    // ── Passes ─────────────────────────────────────────────────────────────

    /**
     * GET /api/v1/admin/passes
     */
    public function listPasses(Request $request)
    {
        $query = Pass::with(['resident.user', 'resident.unit', 'flaggedItems']);
        $this->scopeEstate($query, $request);

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
        $pass = Pass::where('ulid', $ulid)->with(['resident.user', 'resident.unit', 'estate', 'flaggedItems'])->firstOrFail();
        $this->assertEstateAccess($request, $pass->estate_id);
        return response()->json(['pass' => $this->formatPassAdmin($pass)]);
    }

    /**
     * POST /api/v1/admin/passes
     */
    public function createPass(Request $request)
    {
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
        $this->assertEstateAccess($request, $resident->estate_id);

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
        $pass = Pass::where('ulid', $ulid)->firstOrFail();
        $this->assertEstateAccess($request, $pass->estate_id);
        $pass->update(['status' => 'Revoked', 'revoked_at' => now()]);
        return response()->json(['message' => 'Pass revoked.']);
    }

    /**
     * DELETE /api/v1/admin/passes/{ulid}
     */
    public function deletePass(Request $request, string $ulid)
    {
        $pass = Pass::where('ulid', $ulid)->firstOrFail();
        $this->assertEstateAccess($request, $pass->estate_id);
        $pass->delete();
        return response()->json(['message' => 'Pass deleted.']);
    }

    // ── Emergencies ────────────────────────────────────────────────────────

    /**
     * GET /api/v1/admin/emergencies
     */
    public function listEmergencies(Request $request)
    {
        $query = Emergency::with(['resident.user', 'estate']);
        $this->scopeEstate($query, $request);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $emergencies = $query->latest()->get()->map(fn(Emergency $e) => $this->formatEmergency($e));

        return response()->json(['emergencies' => $emergencies]);
    }

    /**
     * PATCH /api/v1/admin/emergencies/{id}/acknowledge
     */
    public function acknowledgeEmergency(Request $request, int $id)
    {
        $emergency = Emergency::findOrFail($id);
        $this->assertEstateAccess($request, $emergency->estate_id);
        $emergency->update(['status' => 'acknowledged', 'acknowledged_at' => now()]);
        return response()->json(['message' => 'Emergency acknowledged.']);
    }

    /**
     * PATCH /api/v1/admin/emergencies/{id}/resolve
     */
    public function resolveEmergency(Request $request, int $id)
    {
        $emergency = Emergency::findOrFail($id);
        $this->assertEstateAccess($request, $emergency->estate_id);
        $emergency->update(['status' => 'resolved', 'resolved_at' => now()]);
        return response()->json(['message' => 'Emergency resolved.']);
    }

    /**
     * DELETE /api/v1/admin/emergencies/{id}
     */
    public function deleteEmergency(Request $request, int $id)
    {
        $emergency = Emergency::findOrFail($id);
        $this->assertEstateAccess($request, $emergency->estate_id);
        $emergency->delete();
        return response()->json(['message' => 'Emergency deleted.']);
    }

    // ── Notifications ──────────────────────────────────────────────────────

    /**
     * GET /api/v1/admin/notifications
     */
    public function listNotifications(Request $request)
    {
        $admin = $request->user();

        $query = Notification::with(['resident.user']);

        if ($admin instanceof \App\Models\Admin && $admin->isEstateAdmin()) {
            $query->whereHas('resident', fn($q) => $q->where('estate_id', $admin->currentEstateId()));
        } elseif ($request->filled('estate_id')) {
            $query->whereHas('resident', fn($q) => $q->where('estate_id', $request->estate_id));
        }

        $notifications = $query->latest()
            ->limit(100)
            ->get()
            ->map(fn(Notification $n) => $this->formatNotification($n));

        return response()->json(['notifications' => $notifications]);
    }

    /**
     * DELETE /api/v1/admin/notifications/{id}
     */
    public function deleteNotification(Request $request, int $id)
    {
        $notification = Notification::with('resident')->findOrFail($id);
        $this->assertEstateAccess($request, $notification->resident?->estate_id);
        $notification->delete();
        return response()->json(['message' => 'Notification deleted.']);
    }

    // ── Estates & Units ────────────────────────────────────────────────────

    /**
     * GET /api/v1/admin/estates
     */
    public function listEstates(Request $request)
    {
        $admin = $request->user();

        $query = Estate::query();
        if ($admin instanceof \App\Models\Admin && $admin->isEstateAdmin()) {
            if ($admin->estates()->exists()) {
                $query->whereIn('id', $admin->estates()->pluck('estates.id'));
            } else {
                $query->where('id', $admin->currentEstateId());
            }
        }

        $estates = $query->get()->map(fn($e) => [
            'id' => $e->id,
            'name' => $e->name,
            'address' => $e->address,
            'state' => $e->state,
            'city' => $e->city,
            'role' => ($admin instanceof \App\Models\Admin && $admin->isSuperAdmin()) ? 'Super Admin' : 'Estate Admin',
        ]);
        return response()->json(['estates' => $estates]);
    }

    /**
     * POST /api/v1/admin/estates
     */
    public function createEstate(Request $request)
    {
        $data = $request->validate([
            'name'    => 'required|string|max:255',
            'address' => 'nullable|string|max:255',
            'state'   => 'nullable|string|max:255',
            'city'    => 'nullable|string|max:255',
        ]);

        $estate = DB::transaction(function () use ($data, $request) {
            $estate = Estate::create([
                'name'      => $data['name'],
                'address'   => $data['address'] ?? null,
                'state'     => $data['state'] ?? null,
                'city'      => $data['city'] ?? null,
                'is_active' => true,
            ]);

            $admin = $request->user();
            if ($admin instanceof \App\Models\Admin) {
                $admin->estates()->syncWithoutDetaching([$estate->id]);
            }

            return $estate;
        });

        return response()->json([
            'success' => true,
            'estate'  => [
                'id'      => $estate->id,
                'name'    => $estate->name,
                'address' => $estate->address,
                'state'   => $estate->state,
                'role'    => 'Estate Admin',
            ],
            'message' => 'Estate registered successfully.',
        ], 201);
    }

    /**
     * GET /api/v1/admin/estates/{id}/units
     */
    public function listUnits(Request $request, int $estateId)
    {
        $this->assertEstateAccess($request, $estateId);

        $units = Unit::where('estate_id', $estateId)->get()->map(fn($u) => $this->formatUnit($u));
        return response()->json(['units' => $units]);
    }

    /**
     * POST /api/v1/admin/units
     */
    public function createUnit(Request $request)
    {
        $data = $request->validate([
            'estate_id'    => 'nullable|exists:estates,id',
            'lane'         => 'nullable|string|max:100',
            'house'        => 'nullable|string|max:100',
            'flat'         => 'nullable|string|max:100',
            'flat_address' => 'required|string|max:255',
        ]);

        $estateId = $this->resolveEstateId($request, $data['estate_id'] ?? null);
        $this->assertEstateAccess($request, $estateId);

        $estate = Estate::findOrFail($estateId);
        $this->assertNotPastDue($estate, $request->user());
        $this->assertCanAddUnit($estate);

        $unit = Unit::create([
            'estate_id'    => $estateId,
            'lane'         => $data['lane'] ?? null,
            'house'        => $data['house'] ?? null,
            'flat'         => $data['flat'] ?? null,
            'flat_address' => $data['flat_address'],
            'is_active'    => true,
        ]);

        return response()->json(['unit' => $this->formatUnit($unit)], 201);
    }

    // ── Dashboard summary ──────────────────────────────────────────────────

    /**
     * GET /api/v1/admin/dashboard
     */
    public function dashboard(Request $request)
    {
        $userQuery = User::query();
        $residentQuery = Resident::query();
        $passQuery = Pass::query();
        $onSiteQuery = Pass::query();
        $emergencyQuery = Emergency::query();
        $flaggedQuery = Pass::query();
        $recentPassQuery = Pass::with(['resident.user', 'resident.unit'])->latest();
        $recentEmergencyQuery = Emergency::with(['resident.user', 'estate'])->latest();

        foreach ([$userQuery, $residentQuery, $passQuery, $onSiteQuery, $emergencyQuery, $flaggedQuery, $recentPassQuery, $recentEmergencyQuery] as $query) {
            $this->scopeEstate($query, $request);
        }

        // ── 7-Day Passes & Influx Trend ──────────────────────────────────────
        $passTrend = [];
        for ($i = 6; $i >= 0; $i--) {
            $d = Carbon::today()->subDays($i);
            $startOfDay = $d->copy()->startOfDay();
            $endOfDay = $d->copy()->endOfDay();

            $pDayQuery = Pass::query()->whereBetween('created_at', [$startOfDay, $endOfDay]);
            $this->scopeEstate($pDayQuery, $request);
            $totalCount = $pDayQuery->count();

            $aDayQuery = Pass::query()
                ->whereBetween('created_at', [$startOfDay, $endOfDay])
                ->where(function ($q) {
                    $q->whereNotNull('arrived_at')
                      ->orWhereIn('status', ['On-site', 'Exited']);
                });
            $this->scopeEstate($aDayQuery, $request);
            $arrivedCount = $aDayQuery->count();

            $passTrend[] = [
                'day'      => $d->format('D'),
                'date'     => $d->toDateString(),
                'label'    => $d->format('d M'),
                'total'    => $totalCount,
                'arrived'  => $arrivedCount,
            ];
        }

        // ── Category Breakdown (Guest, Delivery, Artisan, Family) ────────────
        $allPassesQuery = Pass::query()->where('created_at', '>=', Carbon::today()->subDays(30));
        $this->scopeEstate($allPassesQuery, $request);
        $typeDistribution = [
            'guest'    => 0,
            'delivery' => 0,
            'artisan'  => 0,
            'family'   => 0,
        ];
        foreach ($allPassesQuery->pluck('type') as $pt) {
            $t = strtolower($pt ?? 'standard');
            if (str_contains($t, 'deliver')) {
                $typeDistribution['delivery']++;
            } elseif (str_contains($t, 'artisan')) {
                $typeDistribution['artisan']++;
            } elseif (str_contains($t, 'fam')) {
                $typeDistribution['family']++;
            } else {
                $typeDistribution['guest']++;
            }
        }

        // ── Dues & Payment Analytics ─────────────────────────────────────────
        $feeQuery = Fee::query();
        $this->scopeEstate($feeQuery, $request);
        $fees = $feeQuery->get();
        $feeIds = $fees->pluck('id');

        $totalDuesExpected = 0;
        $totalDuesCollected = 0;
        $totalDuesPending = 0;

        if ($feeIds->isNotEmpty()) {
            $feeUserRecords = DB::table('fee_user')
                ->join('fees', 'fees.id', '=', 'fee_user.fee_id')
                ->whereIn('fees.id', $feeIds)
                ->select('fee_user.payment_status', 'fees.amount')
                ->get();

            foreach ($feeUserRecords as $rec) {
                $amount = (float) $rec->amount;
                $totalDuesExpected += $amount;
                if ($rec->payment_status === 'paid') {
                    $totalDuesCollected += $amount;
                } else {
                    $totalDuesPending += $amount;
                }
            }
        }

        // 6-Month Payment / Dues History
        $paymentMonths = [];
        for ($m = 5; $m >= 0; $m--) {
            $mDate = Carbon::today()->startOfMonth()->subMonths($m);
            $mStart = $mDate->copy()->startOfMonth();
            $mEnd = $mDate->copy()->endOfMonth();

            $collectedMonth = 0;
            if ($feeIds->isNotEmpty()) {
                $collectedMonth = (float) DB::table('fee_user')
                    ->join('fees', 'fees.id', '=', 'fee_user.fee_id')
                    ->whereIn('fees.id', $feeIds)
                    ->where('fee_user.payment_status', 'paid')
                    ->whereBetween('fee_user.updated_at', [$mStart, $mEnd])
                    ->sum('fees.amount');
            }

            $btQuery = BillingTransaction::query()
                ->where('status', 'success')
                ->whereBetween('paid_at', [$mStart, $mEnd]);
            $this->scopeEstate($btQuery, $request);
            $collectedMonth += (float) $btQuery->sum('amount');

            $paymentMonths[] = [
                'month'  => $mDate->format('M'),
                'year'   => $mDate->year,
                'amount' => $collectedMonth,
            ];
        }

        // ── Property Occupancy & Topology ────────────────────────────────────
        $unitQuery = Unit::query();
        $this->scopeEstate($unitQuery, $request);
        $totalUnits = $unitQuery->count();
        $occupiedUnits = (clone $unitQuery)->whereHas('residents', function ($q) {
            $q->where('is_active', true);
        })->count();
        $vacantUnits = max(0, $totalUnits - $occupiedUnits);
        $occupancyRate = $totalUnits > 0 ? round(($occupiedUnits / $totalUnits) * 100, 1) : 0;

        // Peak Gate Hour Today
        $todayPassQuery = Pass::query()->where('created_at', '>=', Carbon::today());
        $this->scopeEstate($todayPassQuery, $request);
        $todayPassList = $todayPassQuery->get(['created_at', 'arrived_at']);

        $peakHourLabel = '08:00 - 10:00';
        if ($todayPassList->isNotEmpty()) {
            $hourBuckets = [];
            foreach ($todayPassList as $p) {
                $h = (int) Carbon::parse($p->created_at)->format('H');
                $hourBuckets[$h] = ($hourBuckets[$h] ?? 0) + 1;
            }
            arsort($hourBuckets);
            $peakHour = array_key_first($hourBuckets);
            if ($peakHour !== null) {
                $peakHourLabel = sprintf('%02d:00 - %02d:00', $peakHour, ($peakHour + 2) % 24);
            }
        }

        return response()->json([
            'stats' => [
                'totalUsers'        => $userQuery->count(),
                'totalResidents'    => $residentQuery->count(),
                'activePasses'      => $passQuery->whereIn('status', ['Pending', 'On-site'])->count(),
                'onSite'            => $onSiteQuery->where('status', 'On-site')->count(),
                'todayEmergencies'  => $emergencyQuery->whereDate('created_at', today())->count(),
                'flaggedItems'      => $flaggedQuery->where('items_flagged', true)->count(),
                'occupancyRate'     => $occupancyRate,
                'totalUnits'        => $totalUnits,
                'occupiedUnits'     => $occupiedUnits,
                'vacantUnits'       => $vacantUnits,
                'duesExpected'      => $totalDuesExpected,
                'duesCollected'     => $totalDuesCollected,
                'duesPending'       => $totalDuesPending,
                'collectionRate'    => ($totalDuesExpected > 0) ? round(($totalDuesCollected / $totalDuesExpected) * 100, 1) : 0,
                'peakHour'          => $peakHourLabel,
            ],
            'analytics' => [
                'passTrend'         => $passTrend,
                'typeDistribution'  => $typeDistribution,
                'paymentMonths'     => $paymentMonths,
            ],
            'recentPasses' => $recentPassQuery->limit(8)->get()
                ->map(fn(Pass $p) => $this->formatPassAdmin($p)),
            'recentEmergencies' => $recentEmergencyQuery->limit(5)->get()
                ->map(fn(Emergency $e) => $this->formatEmergency($e)),
        ]);
    }

    /**
     * GET /api/v1/platform-stats
     * Real-time platform aggregate counts for Checkpass landing page.
     * Accessible publicly without authentication.
     */
    public function publicPlatformStats()
    {
        $propertiesOnboarded = Estate::count();

        return response()->json([
            'propertiesOnboarded' => $propertiesOnboarded,
            'activeResidents'     => Resident::where('is_active', true)->count(),
            'passesCreated'       => Pass::count(),
            'entriesVerified'     => Pass::whereNotNull('arrived_at')->count(),
            'itemsDeclared'       => \App\Models\Item::count(),
        ]);
    }

    // ── Formatters ─────────────────────────────────────────────────────────

    private function formatUser(User $u): array
    {
        return [
            'id'           => $u->id,
            'name'         => $u->name,
            'phone'        => $u->phone,
            'email'        => $u->email,
            'avatar'       => $u->avatar,
            'type'         => $u->type,
            'isActive'     => (bool) $u->is_active,
            'residentId'   => $u->resident?->id,
            'residentRole' => $u->resident?->role,
            'unitAddress'  => $u->resident?->unit?->flat_address,
            'createdAt'    => $u->created_at?->toIso8601String(),
        ];
    }

    private function formatResident(Resident $r): array
    {
        return [
            'id'        => $r->id,
            'user'      => $r->user ? $this->formatUser($r->user) : null,
            'unit'      => $r->unit  ? $this->formatUnit($r->unit) : null,
            'estate'    => $r->estate? ['id' => $r->estate->id,'name'   => $r->estate->name]  : null,
            'role'      => $r->role,
            'isActive'  => $r->is_active,
            'createdAt' => $r->created_at?->toIso8601String(),
        ];
    }

    private function formatUnit(Unit $u): array
    {
        return [
            'id'          => $u->id,
            'estateId'    => $u->estate_id,
            'lane'        => $u->lane,
            'house'       => $u->house,
            'flat'        => $u->flat,
            'flatAddress' => $u->flat_address,
            'isActive'    => $u->is_active,
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
                'unit' => $p->resident->unit?->flat_address,
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
