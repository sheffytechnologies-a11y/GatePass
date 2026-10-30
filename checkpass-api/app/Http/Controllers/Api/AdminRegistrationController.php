<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\Concerns\IssuesAdminTokens;
use App\Models\Admin;
use App\Models\Estate;
use App\Models\Plan;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminRegistrationController extends Controller
{
    use IssuesAdminTokens;

    /**
     * POST /api/v1/admin/auth/register
     * Self-service account creation. No estate yet — step two creates it.
     * Body: { name, email, password, phone }
     */
    public function register(Request $request)
    {
        $data = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:admins,email',
            'password' => 'required|string|min:6',
            'phone'    => 'nullable|string|max:30',
        ]);

        $admin = Admin::create([
            'name'      => $data['name'],
            'email'     => $data['email'],
            'password'  => Hash::make($data['password']),
            'role'      => 'estate_admin',
            'is_active' => true,
        ]);

        $tokens = $this->issueTokenPair($admin);

        return response()->json([
            'token' => $tokens['token'],
            'refreshToken' => $tokens['refreshToken'],
            'admin' => $this->formatAdmin($admin),
        ], 201);
    }

    /**
     * POST /api/v1/admin/auth/register/estate
     * Authenticated step two: create the one estate this estate_admin will
     * manage, and auto-activate the free trial.
     * Body: { name, address, city, state }
     */
    public function registerEstate(Request $request)
    {
        /** @var Admin $admin */
        $admin = $request->user();

        if (! $admin instanceof Admin || ! $admin->isEstateAdmin()) {
            return response()->json([
                'error' => true,
                'code' => 'FORBIDDEN',
                'message' => 'Only self-registered estate admins can create an estate this way.',
            ], 403);
        }

        if ($admin->estates()->exists()) {
            return response()->json([
                'error' => true,
                'code' => 'ESTATE_ALREADY_EXISTS',
                'message' => 'You already have an estate registered.',
            ], 422);
        }

        $data = $request->validate([
            'name'    => 'required|string|max:255',
            'address' => 'nullable|string|max:255',
            'city'    => 'nullable|string|max:255',
            'state'   => 'nullable|string|max:255',
        ]);

        $result = DB::transaction(function () use ($data, $admin) {
            $estate = Estate::create([
                'name'    => $data['name'],
                'address' => $data['address'] ?? null,
                'city'    => $data['city'] ?? null,
                'state'   => $data['state'] ?? null,
                'is_active' => true,
            ]);

            $admin->estates()->attach($estate->id);

            $plan = Plan::where('code', 'free_trial')->firstOrFail();

            $subscription = Subscription::create([
                'estate_id' => $estate->id,
                'plan_id' => $plan->id,
                'billing_cycle' => 'none',
                'status' => 'trialing',
                'current_period_start' => now(),
                'current_period_end' => null,
                'extra_units' => 0,
            ]);

            return [$estate, $subscription->setRelation('plan', $plan)];
        });

        [$estate, $subscription] = $result;

        return response()->json([
            'estate' => [
                'id' => $estate->id,
                'name' => $estate->name,
                'address' => $estate->address,
                'city' => $estate->city,
                'state' => $estate->state,
            ],
            'subscription' => [
                'status' => $subscription->status,
                'plan' => [
                    'code' => $subscription->plan->code,
                    'name' => $subscription->plan->name,
                    'unitLimit' => $subscription->plan->unit_limit,
                    'residentLimit' => $subscription->plan->resident_limit,
                ],
            ],
        ], 201);
    }
}
