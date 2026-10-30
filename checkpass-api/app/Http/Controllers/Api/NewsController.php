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

        $news = News::query()
            ->with(['estate', 'admin'])
            ->whereIn('estate_id', $estateIds)
            ->latest()
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
            'image' => 'nullable|string',
        ]);

        $news = News::create([
            'estate_id' => $data['estate_id'],
            'admin_id' => $admin->id,
            'title' => $data['title'],
            'content' => $data['content'],
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
            'title' => $news->title,
            'content' => $news->content,
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

            return $resident ? [$resident->estate_id] : [];
        }

        return [];
    }
}
