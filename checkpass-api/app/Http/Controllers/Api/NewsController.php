<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\News;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class NewsController extends Controller
{
    /**
     * GET /api/v1/news
     * Resident-facing feed for the authenticated user's estate.
     * Admins also receive feed scoped to their assigned estates.
     */
    public function feed(Request $request)
    {
        $estateIds = $this->accessibleEstateIds($request->user());

        $query = News::query()
            ->with(['estate', 'admin'])
            ->where(function ($q) use ($estateIds) {
                if (!empty($estateIds)) {
                    $q->whereIn('estate_id', $estateIds)
                      ->orWhereNull('estate_id');
                } else {
                    $q->whereNull('estate_id');
                }
            });

        // If user is resident, scope by appropriate audience
        if ($request->user() instanceof User) {
            $resident = $request->user()->resident()->first();
            $role = strtolower($resident->role ?? 'resident');
            $allowedAudiences = ['all'];
            if (in_array($role, ['resident', 'tenant', 'occupant'])) {
                $allowedAudiences[] = 'residents';
            } elseif (in_array($role, ['homeowner', 'owner', 'landlord'])) {
                $allowedAudiences[] = 'homeowners';
                $allowedAudiences[] = 'residents';
            } else {
                $allowedAudiences[] = 'residents';
            }

            $query->where(function ($q) use ($allowedAudiences) {
                $q->whereIn('target_audience', $allowedAudiences)
                  ->orWhereNull('target_audience');
            });
        }

        $news = $query->latest()
            ->get()
            ->map(fn(News $item) => $this->formatNews($item));

        return response()->json(['news' => $news]);
    }

    /**
     * GET /api/v1/admin/news
     */
    public function index(Request $request)
    {
        /** @var Admin $admin */
        $admin = $request->user();
        $estateIds = $admin->estates()->pluck('estates.id');

        $query = News::query()
            ->with(['estate', 'admin'])
            ->whereIn('estate_id', $estateIds);

        if ($request->filled('estate_id')) {
            $query->where('estate_id', $request->estate_id);
        }

        if ($request->filled('target_audience') && $request->target_audience !== 'all') {
            $query->where('target_audience', $request->target_audience);
        }

        if ($request->filled('search')) {
            $query->where(function ($builder) use ($request) {
                $builder->where('title', 'like', '%' . $request->search . '%')
                    ->orWhere('content', 'like', '%' . $request->search . '%');
            });
        }

        $news = $query->latest()->get()->map(fn(News $item) => $this->formatNews($item));

        return response()->json(['news' => $news]);
    }

    /**
     * POST /api/v1/admin/news
     */
    public function store(Request $request)
    {
        /** @var Admin $admin */
        $admin = $request->user();
        $estateIds = $admin->estates()->pluck('estates.id')->all();

        $data = $request->validate([
            'estate_id' => ['required', 'integer', Rule::in($estateIds)],
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'target_audience' => 'nullable|string|in:all,security,staff,residents,homeowners',
            'image' => 'nullable|string',
        ]);

        $news = News::create([
            'estate_id' => $data['estate_id'],
            'admin_id' => $admin->id,
            'title' => $data['title'],
            'content' => $data['content'],
            'target_audience' => $data['target_audience'] ?? 'all',
            'image' => $data['image'] ?? null,
        ]);

        return response()->json(['news' => $this->formatNews($news->fresh())], 201);
    }

    /**
     * GET /api/v1/admin/news/{id}
     */
    public function show(Request $request, int $id)
    {
        /** @var Admin $admin */
        $admin = $request->user();
        $estateIds = $admin->estates()->pluck('estates.id');

        $news = News::with(['estate', 'admin'])
            ->whereIn('estate_id', $estateIds)
            ->findOrFail($id);

        return response()->json(['news' => $this->formatNews($news)]);
    }

    /**
     * PATCH /api/v1/admin/news/{id}
     */
    public function update(Request $request, int $id)
    {
        /** @var Admin $admin */
        $admin = $request->user();
        $estateIds = $admin->estates()->pluck('estates.id')->all();

        $news = News::whereIn('estate_id', $estateIds)->findOrFail($id);

        $data = $request->validate([
            'estate_id' => ['sometimes', 'integer', Rule::in($estateIds)],
            'title' => 'sometimes|string|max:255',
            'content' => 'sometimes|string',
            'target_audience' => 'sometimes|nullable|string|in:all,security,staff,residents,homeowners',
            'image' => 'sometimes|nullable|string',
        ]);

        if (array_key_exists('estate_id', $data)) {
            $news->estate_id = $data['estate_id'];
        }
        if (array_key_exists('title', $data)) {
            $news->title = $data['title'];
        }
        if (array_key_exists('content', $data)) {
            $news->content = $data['content'];
        }
        if (array_key_exists('target_audience', $data)) {
            $news->target_audience = $data['target_audience'] ?? 'all';
        }
        if (array_key_exists('image', $data)) {
            $news->image = $data['image'];
        }

        $news->admin_id = $admin->id;
        $news->save();

        return response()->json(['news' => $this->formatNews($news->fresh())]);
    }

    /**
     * DELETE /api/v1/admin/news/{id}
     */
    public function destroy(Request $request, int $id)
    {
        /** @var Admin $admin */
        $admin = $request->user();
        $estateIds = $admin->estates()->pluck('estates.id');

        $news = News::whereIn('estate_id', $estateIds)->findOrFail($id);
        $news->delete();

        return response()->json(['message' => 'News deleted.']);
    }

    private function formatNews(News $news): array
    {
        return [
            'id' => $news->id,
            'estateId' => $news->estate_id,
            'estateName' => $news->estate?->name,
            'adminId' => $news->admin_id,
            'adminName' => $news->admin?->name,
            'adminRole' => $news->admin?->role === 'super_admin' ? 'Super Admin' : 'Estate Admin',
            'title' => $news->title,
            'content' => $news->content,
            'targetAudience' => $news->target_audience ?? 'all',
            'image' => $news->image,
            'createdAt' => $news->created_at?->toIso8601String(),
            'updatedAt' => $news->updated_at?->toIso8601String(),
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
