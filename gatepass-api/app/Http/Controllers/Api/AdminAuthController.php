<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\PersonalAccessToken;

class AdminAuthController extends Controller
{
    /**
     * POST /api/v1/admin/auth/login
     * Body: { email, password }
     */
    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $admin = Admin::where('email', $data['email'])->first();

        if (! $admin || ! Hash::check($data['password'], $admin->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        if (! $admin->is_active) {
            return response()->json([
                'error' => true,
                'code' => 'ACCOUNT_SUSPENDED',
                'message' => 'Your admin account has been suspended.',
            ], 403);
        }

        $tokens = $this->issueTokenPair($admin);

        return response()->json([
            'token' => $tokens['token'],
            'refreshToken' => $tokens['refreshToken'],
            'admin' => [
                'id' => $admin->id,
                'name' => $admin->name,
                'email' => $admin->email,
                'isActive' => (bool) $admin->is_active,
            ],
        ]);
    }

    /**
     * POST /api/v1/admin/auth/refresh
     * Body: { refreshToken }
     */
    public function refresh(Request $request)
    {
        $data = $request->validate([
            'refreshToken' => 'required|string',
        ]);

        $token = PersonalAccessToken::findToken($data['refreshToken']);

        if (! $token || ! $token->can('refresh')) {
            return response()->json([
                'error' => true,
                'code' => 'AUTH_REFRESH_INVALID',
                'message' => 'Invalid or expired refresh token.',
            ], 401);
        }

        if ($token->expires_at && $token->expires_at->isPast()) {
            $token->delete();

            return response()->json([
                'error' => true,
                'code' => 'AUTH_REFRESH_EXPIRED',
                'message' => 'Refresh token expired. Please log in again.',
            ], 401);
        }

        $admin = $token->tokenable;

        if (! $admin instanceof Admin || ! $admin->is_active) {
            return response()->json([
                'error' => true,
                'code' => 'ACCOUNT_SUSPENDED',
                'message' => 'Admin account unavailable.',
            ], 403);
        }

        $token->delete();
        $tokens = $this->issueTokenPair($admin);

        return response()->json([
            'token' => $tokens['token'],
            'refreshToken' => $tokens['refreshToken'],
        ]);
    }

    /**
     * POST /api/v1/admin/auth/logout
     */
    public function logout(Request $request)
    {
        $data = $request->validate([
            'refreshToken' => 'nullable|string',
        ]);

        $admin = $request->user();
        if (! $admin instanceof Admin) {
            return response()->json([
                'error' => true,
                'code' => 'UNAUTHORIZED',
                'message' => 'Admin token required.',
            ], 401);
        }

        $admin->currentAccessToken()?->delete();

        if (! empty($data['refreshToken'])) {
            $refresh = PersonalAccessToken::findToken($data['refreshToken']);
            if ($refresh && $refresh->tokenable_id === $admin->id && $refresh->tokenable_type === $admin::class) {
                $refresh->delete();
            }
        }

        return response()->json(['message' => 'Logged out successfully.']);
    }

    /**
     * GET /api/v1/admin/auth/me
     */
    public function me(Request $request)
    {
        $admin = $request->user();

        if (! $admin instanceof Admin) {
            return response()->json([
                'error' => true,
                'code' => 'UNAUTHORIZED',
                'message' => 'Admin token required.',
            ], 401);
        }

        return response()->json([
            'admin' => [
                'id' => $admin->id,
                'name' => $admin->name,
                'email' => $admin->email,
                'isActive' => (bool) $admin->is_active,
            ],
        ]);
    }

    private function issueTokenPair(Admin $admin): array
    {
        $access = $admin->createToken('admin-access', ['*'], now()->addDays(7));
        $refresh = $admin->createToken('admin-refresh', ['refresh'], now()->addDays(30));

        return [
            'token' => $access->plainTextToken,
            'refreshToken' => $refresh->plainTextToken,
        ];
    }
}
