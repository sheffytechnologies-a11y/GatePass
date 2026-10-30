<?php

namespace App\Http\Controllers\Api\Concerns;

use App\Models\Admin;
use App\Models\Estate;
use App\Models\Unit;
use Illuminate\Http\Exceptions\HttpResponseException;

/**
 * Plan-capacity and subscription-status enforcement for estate_admin
 * mutations. super_admin accounts (the internal team) always bypass
 * these checks.
 */
trait EnforcesPlanLimits
{
    protected function assertNotPastDue(Estate $estate, mixed $admin): void
    {
        if ($admin instanceof Admin && $admin->isSuperAdmin()) {
            return;
        }

        $subscription = $estate->subscription;
        if (! $subscription) {
            return;
        }

        if ($subscription->status === 'past_due') {
            $this->abortPlanLimit('SUBSCRIPTION_PAST_DUE', 'Your subscription payment is past due. Please renew to continue making changes.');
        }

        $graceDays = (int) config('billing.grace_period_days', 3);

        if ($subscription->status === 'active'
            && $subscription->current_period_end
            && $subscription->current_period_end->copy()->addDays($graceDays)->isPast()) {
            $subscription->update(['status' => 'past_due']);
            $this->abortPlanLimit('SUBSCRIPTION_PAST_DUE', 'Your subscription payment is past due. Please renew to continue making changes.');
        }
    }

    protected function assertCanAddUnit(Estate $estate): void
    {
        $subscription = $estate->subscription;
        $plan = $subscription?->plan;

        if (! $plan || $plan->unit_limit === null) {
            return; // unlimited / custom plan
        }

        $limit = $plan->unit_limit + ($subscription->extra_units ?? 0);
        $current = $estate->units()->count();

        if ($current >= $limit) {
            $this->abortPlanLimit(
                'PLAN_LIMIT_REACHED',
                "You've reached your plan's unit limit. Upgrade your plan to add more units.",
                ['type' => 'units', 'current' => $current, 'max' => $limit]
            );
        }
    }

    protected function assertCanAddResident(Unit $unit): void
    {
        $current = $unit->residents()->count();

        if ($current >= 3) {
            $this->abortPlanLimit(
                'PLAN_LIMIT_REACHED',
                'This unit already has the maximum of 3 residents.',
                ['type' => 'residents', 'current' => $current, 'max' => 3]
            );
        }
    }

    protected function abortPlanLimit(string $code, string $message, ?array $limit = null): never
    {
        $body = [
            'error' => true,
            'code' => $code,
            'message' => $message,
        ];

        if ($limit) {
            $body['limit'] = $limit;
            $body['upgradeUrl'] = '/billing';
        }

        throw new HttpResponseException(response()->json($body, 402));
    }
}
