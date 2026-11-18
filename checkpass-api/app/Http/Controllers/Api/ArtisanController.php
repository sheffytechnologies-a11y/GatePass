<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Artisan;
use App\Models\User;
use Illuminate\Http\Request;

class ArtisanController extends Controller
{
    /**
     * GET /api/v1/artisans
     * Resident-facing feed of verified estate artisans.
     */
    public function index(Request $request)
    {
        $estateIds = $this->accessibleEstateIds($request->user());

        $query = Artisan::query()
            ->where(function ($q) use ($estateIds) {
                if (!empty($estateIds)) {
                    $q->whereIn('estate_id', $estateIds)
                      ->orWhereNull('estate_id');
                } else {
                    $q->whereNull('estate_id');
                }
            })
            ->where('status', 'Active');

        if ($request->filled('category') && $request->category !== 'All') {
            $query->where('category', $request->category);
        }

        if ($request->filled('search')) {
            $term = '%' . $request->search . '%';
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', $term)
                  ->orWhere('trade', 'like', $term)
                  ->orWhere('category', 'like', $term)
                  ->orWhere('phone', 'like', $term);
            });
        }

        $artisans = $query->latest()->get()->map(fn(Artisan $a) => $this->formatArtisan($a));

        return response()->json([
            'artisans' => $artisans,
            'total' => $artisans->count(),
        ]);
    }

    /**
     * GET /api/v1/artisans/{id}
     */
    public function show($id)
    {
        $artisan = Artisan::findOrFail($id);
        return response()->json([
            'artisan' => $this->formatArtisan($artisan),
        ]);
    }

    /**
     * POST /api/v1/artisans/{id}/reviews
     * Resident submits neighbor review.
     */
    public function storeReview(Request $request, $id)
    {
        $artisan = Artisan::findOrFail($id);

        $data = $request->validate([
            'rating' => 'required|numeric|min:1|max:5',
            'comment' => 'required|string|max:1000',
            'resident_name' => 'nullable|string|max:120',
            'resident_unit' => 'nullable|string|max:120',
        ]);

        $user = $request->user();
        $residentName = $data['resident_name'] ?? ($user?->name ?? 'Resident Neighbor');
        $residentUnit = $data['resident_unit'] ?? ($user?->resident?->unit?->flatAddress ?? 'Verified Resident');

        $reviews = $artisan->reviews ?? [];
        $newReview = [
            'id' => 'rev-' . time() . '-' . rand(100, 999),
            'residentName' => $residentName,
            'residentUnit' => $residentUnit,
            'rating' => (float) $data['rating'],
            'comment' => $data['comment'],
            'date' => 'Just now',
        ];

        array_unshift($reviews, $newReview);

        // Recalculate average rating
        $sum = array_reduce($reviews, fn($carry, $r) => $carry + ($r['rating'] ?? 5), 0);
        $avg = round($sum / count($reviews), 1);

        $artisan->reviews = $reviews;
        $artisan->rating = $avg;
        $artisan->save();

        return response()->json([
            'message' => 'Review submitted successfully.',
            'artisan' => $this->formatArtisan($artisan->fresh()),
            'review' => $newReview,
        ], 201);
    }

    /**
     * GET /api/v1/admin/artisans
     * Admin management list.
     */
    public function adminIndex(Request $request)
    {
        $query = Artisan::query();

        if ($request->filled('estate_id')) {
            $query->where(function ($q) use ($request) {
                $q->where('estate_id', $request->estate_id)
                  ->orWhereNull('estate_id');
            });
        }

        if ($request->filled('category') && $request->category !== 'All') {
            $query->where('category', $request->category);
        }

        if ($request->filled('search')) {
            $term = '%' . $request->search . '%';
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', $term)
                  ->orWhere('trade', 'like', $term)
                  ->orWhere('category', 'like', $term)
                  ->orWhere('phone', 'like', $term);
            });
        }

        $artisans = $query->latest()->get()->map(fn(Artisan $a) => $this->formatArtisan($a));

        return response()->json([
            'artisans' => $artisans,
            'total' => $artisans->count(),
        ]);
    }

    /**
     * POST /api/v1/admin/artisans
     */
    public function adminStore(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'trade' => 'required|string|max:255',
            'category' => 'required|string|max:100',
            'phone' => 'required|string|max:50',
            'whatsapp' => 'nullable|string|max:50',
            'estate_id' => 'nullable|integer',
            'address' => 'nullable|string|max:255',
            'status' => 'nullable|string|in:Active,Under Review,Inactive',
            'experience_years' => 'nullable|integer',
            'skills' => 'nullable|array',
            'image_url' => 'nullable|string',
        ]);

        $initials = collect(explode(' ', trim($data['name'])))
            ->filter()
            ->map(fn($w) => strtoupper(substr($w, 0, 1)))
            ->slice(0, 2)
            ->join('');

        $colors = ['#0A5C38', '#1D4ED8', '#B45309', '#7C3AED', '#059669', '#DC2626', '#0891B2'];
        $avatarBg = $colors[array_rand($colors)];

        $artisan = Artisan::create([
            'estate_id' => $data['estate_id'] ?? null,
            'name' => $data['name'],
            'trade' => $data['trade'],
            'category' => $data['category'],
            'phone' => $data['phone'],
            'whatsapp' => $data['whatsapp'] ?? $data['phone'],
            'rating' => 5.0,
            'avatar_bg' => $avatarBg,
            'initials' => $initials ?: 'AR',
            'image_url' => $data['image_url'] ?? null,
            'status' => $data['status'] ?? 'Active',
            'address' => $data['address'] ?? 'Estate Service Directory',
            'verified_since' => date('M Y'),
            'experience_years' => $data['experience_years'] ?? 3,
            'gate_pass_cleared' => true,
            'police_cleared' => true,
            'id_verified' => true,
            'response_time' => '< 30 mins',
            'jobs_completed' => 0,
            'skills' => $data['skills'] ?? [$data['trade']],
            'reviews' => [],
            'recent_gate_logs' => [],
        ]);

        return response()->json([
            'message' => 'Artisan created successfully.',
            'artisan' => $this->formatArtisan($artisan->fresh()),
        ], 201);
    }

    /**
     * GET /api/v1/admin/artisans/{id}
     */
    public function adminShow($id)
    {
        $artisan = Artisan::findOrFail($id);
        return response()->json([
            'artisan' => $this->formatArtisan($artisan),
        ]);
    }

    /**
     * PATCH /api/v1/admin/artisans/{id}
     */
    public function adminUpdate(Request $request, $id)
    {
        $artisan = Artisan::findOrFail($id);

        $data = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'trade' => 'sometimes|required|string|max:255',
            'category' => 'sometimes|required|string|max:100',
            'phone' => 'sometimes|required|string|max:50',
            'whatsapp' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:255',
            'status' => 'nullable|string|in:Active,Under Review,Inactive',
            'experience_years' => 'nullable|integer',
            'skills' => 'nullable|array',
            'image_url' => 'nullable|string',
            'gate_pass_cleared' => 'nullable|boolean',
            'police_cleared' => 'nullable|boolean',
            'id_verified' => 'nullable|boolean',
        ]);

        $artisan->update($data);

        return response()->json([
            'message' => 'Artisan updated successfully.',
            'artisan' => $this->formatArtisan($artisan->fresh()),
        ]);
    }

    /**
     * DELETE /api/v1/admin/artisans/{id}
     */
    public function adminDestroy($id)
    {
        $artisan = Artisan::findOrFail($id);
        $artisan->delete();

        return response()->json([
            'message' => 'Artisan deleted successfully.',
        ]);
    }

    private function formatArtisan(Artisan $a): array
    {
        return [
            'id' => (string) $a->id,
            'estateId' => $a->estate_id,
            'name' => $a->name,
            'trade' => $a->trade,
            'category' => $a->category,
            'phone' => $a->phone,
            'whatsapp' => $a->whatsapp ?? $a->phone,
            'rating' => (float) $a->rating,
            'avatarBg' => $a->avatar_bg ?: '#0A5C38',
            'initials' => $a->initials ?: strtoupper(substr($a->name, 0, 2)),
            'imageUrl' => $a->image_url,
            'status' => $a->status ?: 'Active',
            'address' => $a->address,
            'verifiedSince' => $a->verified_since,
            'experienceYears' => (int) $a->experience_years,
            'gatePassCleared' => (bool) $a->gate_pass_cleared,
            'policeCleared' => (bool) $a->police_cleared,
            'idVerified' => (bool) $a->id_verified,
            'responseTime' => $a->response_time ?: '< 30 mins',
            'jobsCompleted' => (int) $a->jobs_completed,
            'skills' => $a->skills ?? [],
            'reviews' => $a->reviews ?? [],
            'recentGateLogs' => $a->recent_gate_logs ?? [],
            'createdAt' => $a->created_at?->toIso8601String(),
            'updatedAt' => $a->updated_at?->toIso8601String(),
        ];
    }

    private function accessibleEstateIds($user): array
    {
        if ($user instanceof Admin) {
            return $user->estates()->pluck('estates.id')->all();
        }

        if ($user instanceof User) {
            $resident = $user->resident()->first();
            $ids = [];
            if ($resident && $resident->estate_id) {
                $ids[] = (int) $resident->estate_id;
            }
            if (!empty($user->estate_id) && !in_array((int) $user->estate_id, $ids)) {
                $ids[] = (int) $user->estate_id;
            }

            return $ids;
        }

        return [];
    }
}
