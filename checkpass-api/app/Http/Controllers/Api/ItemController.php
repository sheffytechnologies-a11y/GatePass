<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\Pass;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ItemController extends Controller
{
    /**
     * GET /api/v1/passes/{ulid}/items
     */
    public function index(Request $request, string $ulid)
    {
        $pass = $this->resolvePass($request, $ulid);
        if ($pass instanceof \Illuminate\Http\JsonResponse) return $pass;

        return response()->json([
            'items' => $pass->items->map(fn($item) => $this->formatItem($item)),
        ]);
    }

    /**
     * POST /api/v1/passes/{ulid}/items
     * Body: { name, description?, photo? }
     */
    public function store(Request $request, string $ulid)
    {
        $pass = $this->resolvePass($request, $ulid);
        if ($pass instanceof \Illuminate\Http\JsonResponse) return $pass;

        $data = $request->validate([
            'name'        => 'required|string|max:150',
            'description' => 'nullable|string',
            'photo'       => 'nullable|image|max:5120', // max 5 MB
        ]);

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('items', 'public');
        }

        $item = $pass->items()->create($data);

        return response()->json(['item' => $this->formatItem($item)], 201);
    }

    /**
     * GET /api/v1/passes/{ulid}/items/{id}
     */
    public function show(Request $request, string $ulid, int $id)
    {
        $pass = $this->resolvePass($request, $ulid);
        if ($pass instanceof \Illuminate\Http\JsonResponse) return $pass;

        $item = $pass->items()->find($id);

        if (! $item) {
            return response()->json(['error' => true, 'code' => 'ITEM_NOT_FOUND', 'message' => 'Item not found.'], 404);
        }

        return response()->json(['item' => $this->formatItem($item)]);
    }

    /**
     * PATCH /api/v1/passes/{ulid}/items/{id}
     * Body: { name?, description?, photo? }
     */
    public function update(Request $request, string $ulid, int $id)
    {
        $pass = $this->resolvePass($request, $ulid);
        if ($pass instanceof \Illuminate\Http\JsonResponse) return $pass;

        $item = $pass->items()->find($id);

        if (! $item) {
            return response()->json(['error' => true, 'code' => 'ITEM_NOT_FOUND', 'message' => 'Item not found.'], 404);
        }

        $data = $request->validate([
            'name'        => 'sometimes|required|string|max:150',
            'description' => 'nullable|string',
            'photo'       => 'nullable|image|max:5120',
        ]);

        if ($request->hasFile('photo')) {
            // Delete old photo if present
            if ($item->photo) {
                Storage::disk('public')->delete($item->photo);
            }
            $data['photo'] = $request->file('photo')->store('items', 'public');
        }

        $item->update($data);

        return response()->json(['item' => $this->formatItem($item)]);
    }

    /**
     * DELETE /api/v1/passes/{ulid}/items/{id}
     */
    public function destroy(Request $request, string $ulid, int $id)
    {
        $pass = $this->resolvePass($request, $ulid);
        if ($pass instanceof \Illuminate\Http\JsonResponse) return $pass;

        $item = $pass->items()->find($id);

        if (! $item) {
            return response()->json(['error' => true, 'code' => 'ITEM_NOT_FOUND', 'message' => 'Item not found.'], 404);
        }

        if ($item->photo) {
            Storage::disk('public')->delete($item->photo);
        }
        $item->delete();

        return response()->json(['message' => 'Item deleted.']);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function resolvePass(Request $request, string $ulid)
    {
        $resident = $request->user()->resident()->first();

        $query = Pass::where('ulid', $ulid)->with('items');

        // Residents can only access their own passes; security can access any
        if ($request->user()->type !== 'security') {
            if (! $resident) {
                return response()->json(['error' => true, 'code' => 'NO_RESIDENT_PROFILE', 'message' => 'No resident profile.'], 403);
            }
            $query->where('resident_id', $resident->id);
        }

        $pass = $query->first();

        if (! $pass) {
            return response()->json(['error' => true, 'code' => 'PASS_NOT_FOUND', 'message' => 'Pass not found.'], 404);
        }

        return $pass;
    }

    private function formatItem(Item $item): array
    {
        return [
            'id'          => $item->id,
            'passId'      => $item->pass_id,
            'name'        => $item->name,
            'description' => $item->description,
            'photo'       => $item->photo ? Storage::disk('public')->url($item->photo) : null,
            'createdAt'   => $item->created_at->toIso8601String(),
            'updatedAt'   => $item->updated_at->toIso8601String(),
        ];
    }
}
