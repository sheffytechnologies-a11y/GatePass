<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\Concerns\EnforcesPlanLimits;
use App\Http\Controllers\Api\Concerns\ScopesToEstate;
use App\Models\Estate;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PropertyController extends Controller
{
    use ScopesToEstate, EnforcesPlanLimits;

    /**
     * GET /api/v1/admin/estates/{estateId}/properties
     */
    public function index(Request $request, int $estateId)
    {
        $this->assertEstateAccess($request, $estateId);

        $query = Unit::where('estate_id', $estateId)
            ->with(['residents.user']);

        if ($request->filled('search')) {
            $term = '%' . trim($request->search) . '%';
            $query->where(function ($q) use ($term) {
                $q->where('flat_address', 'like', $term)
                  ->orWhere('lane', 'like', $term)
                  ->orWhere('house', 'like', $term)
                  ->orWhere('flat', 'like', $term)
                  ->orWhereHas('residents.user', function ($uq) use ($term) {
                      $uq->where('name', 'like', $term)
                         ->orWhere('phone', 'like', $term);
                  });
            });
        }

        if ($request->filled('unit_type') && $request->unit_type !== 'all') {
            $query->where('unit_type', $request->unit_type);
        }

        if ($request->filled('occupancy') && $request->occupancy !== 'all') {
            if ($request->occupancy === 'occupied') {
                $query->whereHas('residents', fn($q) => $q->where('is_active', true));
            } elseif ($request->occupancy === 'vacant') {
                $query->whereDoesntHave('residents', fn($q) => $q->where('is_active', true));
            }
        }

        $units = $query->orderBy('id', 'desc')->get()->map(fn(Unit $u) => $this->formatProperty($u));

        return response()->json(['properties' => $units]);
    }

    /**
     * GET /api/v1/admin/estates/{estateId}/properties/stats
     */
    public function stats(Request $request, int $estateId)
    {
        $this->assertEstateAccess($request, $estateId);

        $total = Unit::where('estate_id', $estateId)->count();
        $occupied = Unit::where('estate_id', $estateId)
            ->whereHas('residents', fn($q) => $q->where('is_active', true))
            ->count();
        $vacant = max(0, $total - $occupied);
        $rate = $total > 0 ? round(($occupied / $total) * 100, 1) : 0;

        $typeCounts = Unit::where('estate_id', $estateId)
            ->select('unit_type', DB::raw('count(*) as count'))
            ->groupBy('unit_type')
            ->pluck('count', 'unit_type')
            ->toArray();

        return response()->json([
            'stats' => [
                'totalProperties'    => $total,
                'occupiedProperties' => $occupied,
                'vacantProperties'   => $vacant,
                'occupancyRate'      => $rate,
                'unitTypes'          => $typeCounts,
            ]
        ]);
    }

    /**
     * GET /api/v1/admin/estates/{estateId}/property-structure
     */
    public function getStructure(Request $request, int $estateId)
    {
        $this->assertEstateAccess($request, $estateId);
        $estate = Estate::findOrFail($estateId);

        return response()->json([
            'structure' => $estate->getPropertyStructureOrDefault()
        ]);
    }

    /**
     * PUT /api/v1/admin/estates/{estateId}/property-structure
     */
    public function updateStructure(Request $request, int $estateId)
    {
        $this->assertEstateAccess($request, $estateId);
        $estate = Estate::findOrFail($estateId);
        $this->assertNotPastDue($estate, $request->user());

        $data = $request->validate([
            'preset' => 'required|string|max:50',
            'levels' => 'required|array|min:1',
            'levels.*.level'    => 'required|integer',
            'levels.*.name'     => 'required|string|max:100',
            'levels.*.slug'     => 'nullable|string|max:100',
            'levels.*.required' => 'nullable|boolean',
        ]);

        $estate->property_structure = [
            'preset' => $data['preset'],
            'levels' => $data['levels'],
        ];
        $estate->save();

        // Optional: Relabel existing units to align with updated tier names
        $relabelledCount = 0;
        if ($request->boolean('relabel_existing_units')) {
            $levelNames = array_column($data['levels'], 'name');
            $units = Unit::where('estate_id', $estateId)->get();
            foreach ($units as $u) {
                $rawVals = [];
                if (! empty($u->hierarchy_data) && is_array($u->hierarchy_data)) {
                    $rawVals = array_values($u->hierarchy_data);
                } else {
                    $rawVals = array_values(array_filter([$u->lane, $u->house, $u->flat]));
                }

                $newHierarchy = [];
                foreach ($levelNames as $idx => $lvlName) {
                    if (isset($rawVals[$idx])) {
                        $newHierarchy[$lvlName] = $rawVals[$idx];
                    }
                }

                if (! empty($newHierarchy)) {
                    $u->hierarchy_data = $newHierarchy;
                    $u->save();
                    $relabelledCount++;
                }
            }
        }

        return response()->json([
            'structure'        => $estate->property_structure,
            'relabelledCount'  => $relabelledCount,
            'message'          => 'Property structure updated successfully.',
        ]);
    }

    /**
     * POST /api/v1/admin/properties
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'estate_id'      => 'nullable|exists:estates,id',
            'unit_type'      => 'nullable|string|max:50',
            'lane'           => 'nullable|string|max:100',
            'house'          => 'nullable|string|max:100',
            'flat'           => 'nullable|string|max:100',
            'flat_address'   => 'nullable|string|max:255',
            'hierarchy_data' => 'nullable|array',
            'notes'          => 'nullable|string|max:500',
        ]);

        $estateId = $this->resolveEstateId($request, $data['estate_id'] ?? null);
        $this->assertEstateAccess($request, $estateId);

        $estate = Estate::findOrFail($estateId);
        $this->assertNotPastDue($estate, $request->user());
        $this->assertCanAddUnit($estate);

        // Build flat_address if not directly provided
        $flatAddress = $data['flat_address'] ?? null;
        if (empty($flatAddress)) {
            if (! empty($data['hierarchy_data'])) {
                $flatAddress = implode(', ', array_reverse(array_filter(array_values($data['hierarchy_data']))));
            } else {
                $flatAddress = collect([$data['flat'] ?? null, $data['house'] ?? null, $data['lane'] ?? null])
                    ->filter()
                    ->implode(', ');
            }
        }

        if (empty($flatAddress)) {
            abort(422, 'A valid property address or hierarchy values are required.');
        }

        // Check uniqueness per estate
        $exists = Unit::where('estate_id', $estateId)->where('flat_address', $flatAddress)->exists();
        if ($exists) {
            abort(422, "A property with the address '{$flatAddress}' already exists in this estate.");
        }

        $unit = Unit::create([
            'estate_id'      => $estateId,
            'lane'           => $data['lane'] ?? null,
            'house'          => $data['house'] ?? null,
            'flat'           => $data['flat'] ?? null,
            'flat_address'   => $flatAddress,
            'unit_type'      => $data['unit_type'] ?? 'Apartment',
            'hierarchy_data' => $data['hierarchy_data'] ?? null,
            'notes'          => $data['notes'] ?? null,
            'is_active'      => true,
        ]);

        $unit->load(['residents.user']);

        return response()->json([
            'property' => $this->formatProperty($unit),
            'message'  => 'Property created successfully.',
        ], 201);
    }

    /**
     * POST /api/v1/admin/properties/batch
     * Generates multiple property units using Cartesian product of tiers
     */
    public function batchStore(Request $request)
    {
        $data = $request->validate([
            'estate_id'  => 'nullable|exists:estates,id',
            'unit_type'  => 'nullable|string|max:50',
            'levels'     => 'required|array|min:1',
            'levels.*.name'   => 'required|string',
            'levels.*.values' => 'required|array|min:1',
            'levels.*.values.*' => 'string|max:100',
        ]);

        $estateId = $this->resolveEstateId($request, $data['estate_id'] ?? null);
        $this->assertEstateAccess($request, $estateId);

        $estate = Estate::findOrFail($estateId);
        $this->assertNotPastDue($estate, $request->user());

        // Compute Cartesian product of level values
        $tierArrays = [];
        $levelNames = [];
        foreach ($data['levels'] as $tier) {
            $levelNames[] = $tier['name'];
            $tierArrays[] = array_values(array_filter(array_map('trim', $tier['values'])));
        }

        $combinations = $this->cartesianProduct($tierArrays);
        $totalUnitsToCreate = count($combinations);

        if ($totalUnitsToCreate === 0) {
            abort(422, 'No valid property combinations generated from supplied values.');
        }

        // Enforce plan quota limit
        $this->assertCanAddUnits($estate, $totalUnitsToCreate);

        $unitType = $data['unit_type'] ?? 'Apartment';
        $existingAddresses = Unit::where('estate_id', $estateId)
            ->pluck('flat_address')
            ->flip()
            ->all();

        $toInsert = [];
        $skipped = 0;
        $now = now();

        foreach ($combinations as $combo) {
            // $combo is [valLevel1, valLevel2, valLevel3, ...]
            // flat_address is reverse comma-delimited: e.g. "Flat 1, House 2, Lane 1"
            $flatAddress = implode(', ', array_reverse($combo));

            if (isset($existingAddresses[$flatAddress])) {
                $skipped++;
                continue;
            }

            // Build hierarchy_data map: {"Lane": "Lane 1", "House": "House 2", ...}
            $hierarchyData = [];
            foreach ($levelNames as $idx => $lvlName) {
                $hierarchyData[$lvlName] = $combo[$idx] ?? '';
            }

            // Map standard lane/house/flat backward compatibility fields if 1st, 2nd, 3rd tier exists
            $lane = $combo[0] ?? null;
            $house = $combo[1] ?? null;
            $flat = $combo[2] ?? null;

            $toInsert[] = [
                'estate_id'      => $estateId,
                'lane'           => $lane,
                'house'          => $house,
                'flat'           => $flat,
                'flat_address'   => $flatAddress,
                'unit_type'      => $unitType,
                'hierarchy_data' => json_encode($hierarchyData),
                'notes'          => null,
                'is_active'      => true,
                'created_at'     => $now,
                'updated_at'     => $now,
            ];

            // Mark as tracked to prevent duplicates within this batch
            $existingAddresses[$flatAddress] = true;
        }

        if (! empty($toInsert)) {
            // Chunk insert for performance and database packet limits
            DB::transaction(function () use ($toInsert) {
                foreach (array_chunk($toInsert, 100) as $chunk) {
                    Unit::insert($chunk);
                }
            });
        }

        return response()->json([
            'message'        => 'Batch property generation complete.',
            'createdCount'   => count($toInsert),
            'skippedCount'   => $skipped,
            'totalRequested' => $totalUnitsToCreate,
        ], 201);
    }

    /**
     * PUT /api/v1/admin/properties/{id}
     */
    public function update(Request $request, int $id)
    {
        $unit = Unit::findOrFail($id);
        $this->assertEstateAccess($request, $unit->estate_id);

        $estate = $unit->estate;
        $this->assertNotPastDue($estate, $request->user());

        $data = $request->validate([
            'flat_address'   => 'nullable|string|max:255',
            'unit_type'      => 'nullable|string|max:50',
            'lane'           => 'nullable|string|max:100',
            'house'          => 'nullable|string|max:100',
            'flat'           => 'nullable|string|max:100',
            'hierarchy_data' => 'nullable|array',
            'notes'          => 'nullable|string|max:500',
            'is_active'      => 'nullable|boolean',
        ]);

        if (isset($data['flat_address']) && $data['flat_address'] !== $unit->flat_address) {
            $conflict = Unit::where('estate_id', $unit->estate_id)
                ->where('flat_address', $data['flat_address'])
                ->where('id', '!=', $unit->id)
                ->exists();
            if ($conflict) {
                abort(422, "Another property with the address '{$data['flat_address']}' already exists.");
            }
            $unit->flat_address = $data['flat_address'];
        }

        if (isset($data['unit_type']))      $unit->unit_type      = $data['unit_type'];
        if (isset($data['lane']))           $unit->lane           = $data['lane'];
        if (isset($data['house']))          $unit->house          = $data['house'];
        if (isset($data['flat']))           $unit->flat           = $data['flat'];
        if (isset($data['hierarchy_data'])) $unit->hierarchy_data = $data['hierarchy_data'];
        if (isset($data['notes']))          $unit->notes          = $data['notes'];
        if (isset($data['is_active']))      $unit->is_active      = $data['is_active'];

        $unit->save();
        $unit->load(['residents.user']);

        return response()->json([
            'property' => $this->formatProperty($unit),
            'message'  => 'Property updated successfully.',
        ]);
    }

    /**
     * DELETE /api/v1/admin/properties/{id}
     */
    public function destroy(Request $request, int $id)
    {
        $unit = Unit::findOrFail($id);
        $this->assertEstateAccess($request, $unit->estate_id);

        $estate = $unit->estate;
        $this->assertNotPastDue($estate, $request->user());

        // Check if property has assigned residents
        if ($unit->residents()->exists()) {
            abort(422, 'Cannot delete property: this property currently has assigned residents. Please remove or reassign them first.');
        }

        // Check if property has historic gate passes
        if ($unit->passes()->exists()) {
            // Soft deactivate rather than hard delete to preserve gate audit history
            $unit->is_active = false;
            $unit->save();
            return response()->json([
                'message' => 'Property has gate audit history. It has been deactivated instead of deleted.',
                'deactivated' => true,
            ]);
        }

        $unit->delete();

        return response()->json([
            'message' => 'Property deleted successfully.',
            'deleted' => true,
        ]);
    }

    /**
     * GET /api/v1/admin/estates/{estateId}/properties/topology-tree
     * Returns structured nested tree of existing properties for cascading pickers
     */
    public function topologyTree(Request $request, int $estateId)
    {
        $this->assertEstateAccess($request, $estateId);

        $units = Unit::where('estate_id', $estateId)
            ->where('is_active', true)
            ->with(['residents.user'])
            ->get();

        $tree = [];
        foreach ($units as $u) {
            $l1 = $u->lane ?: 'General Area';
            $l2 = $u->house ?: 'Building';
            $l3 = $u->flat ?: $u->flat_address;

            if (! isset($tree[$l1])) {
                $tree[$l1] = [];
            }
            if (! isset($tree[$l1][$l2])) {
                $tree[$l1][$l2] = [];
            }

            $tree[$l1][$l2][] = [
                'id'          => $u->id,
                'label'       => $l3,
                'flatAddress' => $u->flat_address,
                'unitType'    => $u->unit_type,
                'isOccupied'  => $u->isOccupied(),
                'residents'   => $u->residents->map(fn($r) => [
                    'id'   => $r->id,
                    'name' => $r->user?->name ?? 'Unknown',
                    'role' => $r->role,
                ])->all(),
            ];
        }

        return response()->json(['tree' => $tree]);
    }

    // ── Helper Formatter ──────────────────────────────────────────────────

    private function formatProperty(Unit $u): array
    {
        $activeResidents = $u->residents->filter(fn($r) => $r->is_active);

        // Fallback for legacy units created before the topology module
        $hierarchyData = $u->hierarchy_data;
        if (empty($hierarchyData)) {
            $hierarchyData = array_filter([
                'Lane'  => $u->lane,
                'House' => $u->house,
                'Flat'  => $u->flat,
            ]);
        }

        return [
            'id'             => $u->id,
            'estateId'       => $u->estate_id,
            'flatAddress'    => $u->flat_address,
            'unitType'       => $u->unit_type ?? 'Apartment',
            'lane'           => $u->lane,
            'house'          => $u->house,
            'flat'           => $u->flat,
            'hierarchyData'  => ! empty($hierarchyData) ? $hierarchyData : null,
            'notes'          => $u->notes,
            'isActive'       => (bool) $u->is_active,
            'isOccupied'     => $activeResidents->isNotEmpty(),
            'residentsCount' => $activeResidents->count(),
            'residents'      => $activeResidents->map(fn($r) => [
                'id'    => $r->id,
                'name'  => $r->user?->name ?? 'Resident',
                'phone' => $r->user?->phone,
                'email' => $r->user?->email,
                'role'  => $r->role,
            ])->values()->all(),
            'createdAt'      => $u->created_at?->toIso8601String(),
        ];
    }

    /**
     * Compute Cartesian product of an array of arrays
     */
    private function cartesianProduct(array $arrays): array
    {
        $result = [[]];
        foreach ($arrays as $list) {
            $append = [];
            foreach ($result as $product) {
                foreach ($list as $item) {
                    $append[] = array_merge($product, [$item]);
                }
            }
            $result = $append;
        }
        return $result;
    }
}

