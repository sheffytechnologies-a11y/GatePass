<?php

namespace App\Http\Controllers\Api\Concerns;

use App\Models\Admin;

trait IssuesAdminTokens
{
    private function issueTokenPair(Admin $admin): array
    {
        $access = $admin->createToken('admin-access', ['*'], now()->addDays(7));
        $refresh = $admin->createToken('admin-refresh', ['refresh'], now()->addDays(30));

        return [
            'token' => $access->plainTextToken,
            'refreshToken' => $refresh->plainTextToken,
        ];
    }

    private function formatAdmin(Admin $admin): array
    {
        return [
            'id' => $admin->id,
            'name' => $admin->name,
            'email' => $admin->email,
            'role' => $admin->role,
            'isActive' => (bool) $admin->is_active,
        ];
    }
}
