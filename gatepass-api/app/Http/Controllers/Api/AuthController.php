<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\PersonalAccessToken;

class AuthController extends Controller
{
    /**
     * POST /api/v1/auth/login
     * Body: { phone, password }
     */
    public function login(Request $request)
    {
        $data = $request->validate([
            'phone' => 'required|string',
            'password' => 'required|string',
        ]);

        $phone = $this->normalisePhone($data['phone']);

        $user = User::where('phone', $phone)
            ->orWhere('phone', $data['phone'])
            ->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'phone' => ['The provided credentials are incorrect.'],
            ]);
        }

        if (! $user->is_active) {
            return response()->json([
                'error' => true,
                'code' => 'ACCOUNT_SUSPENDED',
                'message' => 'Your account has been suspended. Contact your estate manager.',
            ], 403);
        }

        // Security users don't need a resident profile
        if ($user->type === 'security') {
            $tokens = $this->issueTokenPair($user);

            return response()->json([
                'token'        => $tokens['token'],
                'refreshToken' => $tokens['refreshToken'],
                'userType'     => 'security',
                'data'         => $user,
                'resident'     => null,
            ]);
        }

        $resident = $user->resident()->with(['unit', 'estate'])->first();

        if (! $resident) {
            return response()->json([
                'error' => true,
                'code' => 'NO_RESIDENT_PROFILE',
                'message' => 'No resident profile found for this account.',
            ], 403);
        }

        $tokens = $this->issueTokenPair($user);

        return response()->json([
            'token'        => $tokens['token'],
            'refreshToken' => $tokens['refreshToken'],
            'userType'     => 'resident',
            'data'         => $user,
            'resident'     => $this->formatResident($resident, $user),
        ]);
    }

    /**
     * POST /api/v1/auth/refresh
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

        $user = $token->tokenable;

        if (! $user || ! $user->is_active) {
            return response()->json([
                'error' => true,
                'code' => 'ACCOUNT_SUSPENDED',
                'message' => 'Account unavailable.',
            ], 403);
        }

        // Rotate refresh token
        $token->delete();

        $tokens = $this->issueTokenPair($user);

        return response()->json([
            'token' => $tokens['token'],
            'refreshToken' => $tokens['refreshToken'],
        ]);
    }

    /**
     * POST /api/v1/auth/logout
     */
    public function logout(Request $request)
    {
        $data = $request->validate([
            'refreshToken' => 'nullable|string',
        ]);

        // Revoke current access token
        $request->user()->currentAccessToken()?->delete();

        // Revoke provided refresh token too (if belongs to same user)
        if (! empty($data['refreshToken'])) {
            $refresh = PersonalAccessToken::findToken($data['refreshToken']);
            if ($refresh && $refresh->tokenable_id === $request->user()->id && $refresh->tokenable_type === $request->user()::class) {
                $refresh->delete();
            }
        }

        return response()->json(['message' => 'Logged out successfully.']);
    }

    /**
     * POST /api/v1/auth/change-password
     */
    public function changePassword(Request $request)
    {
        $data = $request->validate([
            'currentPassword' => 'required|string',
            'newPassword' => 'required|string|min:8',
        ]);

        $user = $request->user();

        if (! Hash::check($data['currentPassword'], $user->password)) {
            throw ValidationException::withMessages([
                'currentPassword' => ['The current password is incorrect.'],
            ]);
        }

        $user->update(['password' => Hash::make($data['newPassword'])]);

        // Revoke all other tokens
        $currentId = $request->user()->currentAccessToken()->id;
        $user->tokens()->where('id', '!=', $currentId)->delete();

        return response()->json(['message' => 'Password changed successfully.']);
    }

    /**
     * GET /api/v1/auth/me
     */
    public function me(Request $request)
    {
        $user = $request->user();
        $resident = $user->resident()->with(['unit', 'estate'])->first();

        if (! $resident) {
            return response()->json([
                'error' => true,
                'code' => 'NO_RESIDENT_PROFILE',
                'message' => 'No resident profile found.',
            ], 404);
        }

        return response()->json([
            'resident' => $this->formatResident($resident, $user),
        ]);
    }

    private function issueTokenPair(User $user): array
    {
        $access = $user->createToken('mobile-access', ['*'], now()->addDays(7));
        $refresh = $user->createToken('mobile-refresh', ['refresh'], now()->addDays(30));

        return [
            'token' => $access->plainTextToken,
            'refreshToken' => $refresh->plainTextToken,
        ];
    }

    private function normalisePhone(string $phone): string
    {
        $digits = preg_replace('/\D/', '', $phone);
        if (str_starts_with($digits, '0')) {
            return '+234' . substr($digits, 1);
        }
        if (str_starts_with($digits, '234')) {
            return '+' . $digits;
        }
        return $phone;
    }

    private function formatResident($resident, $user): array
    {
        return [
            'id' => (string) $resident->id,
            'unitId' => (string) $resident->unit_id,
            'name' => $user->name,
            'phone' => $user->phone,
            'flatAddress' => $resident->unit?->flat_address ?? '',
            'estateName' => $resident->estate?->name ?? '',
            'lane' => $resident->unit?->lane ?? '',
            'house' => $resident->unit?->house ?? '',
            'flat' => $resident->unit?->flat ?? '',
            'role' => $resident->role,
            'pushEnabled' => (bool) $resident->push_enabled,
            'isActive' => (bool) $resident->is_active,
        ];
    }
}
