<?php

namespace App\Http\Controllers\Api\Concerns;

use App\Models\Admin;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Shared estate-scoping helpers for admin-facing controllers.
 *
 * estate_admin accounts are always confined to the single estate they
 * created at registration. super_admin accounts see everything by
 * default, optionally narrowed with an ?estate_id= query param.
 */
trait ScopesToEstate
{
    protected function scopeEstate(Builder $query, Request $request, string $column = 'estate_id'): Builder
    {
        $admin = $request->user();

        if (! $admin instanceof Admin) {
            return $query;
        }

        if ($admin->isEstateAdmin()) {
            return $query->where($column, $admin->currentEstateId());
        }

        if ($request->filled('estate_id')) {
            return $query->where($column, $request->estate_id);
        }

        return $query;
    }

    /**
     * Resolve which estate_id a mutation should target: for estate_admin
     * this is always their own estate (ignoring/overriding anything the
     * client sent); for super_admin it's whatever was submitted.
     */
    protected function resolveEstateId(Request $request, mixed $requested = null): ?int
    {
        $admin = $request->user();

        if ($admin instanceof Admin && $admin->isEstateAdmin()) {
            return $admin->currentEstateId();
        }

        return $requested !== null ? (int) $requested : null;
    }

    /**
     * Guard against an estate_admin acting on a resource belonging to
     * another estate.
     */
    protected function assertEstateAccess(Request $request, ?int $estateId): void
    {
        $admin = $request->user();

        if ($admin instanceof Admin && $admin->isEstateAdmin() && $estateId !== $admin->currentEstateId()) {
            throw new HttpException(403, 'You do not have access to this estate.');
        }
    }
}
