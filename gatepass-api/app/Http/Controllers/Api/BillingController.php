<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\BillingTransaction;
use App\Models\Estate;
use App\Models\Plan;
use App\Models\Resident;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BillingController extends Controller
{
    /**
     * GET /api/v1/admin/billing/subscription
     */
    public function subscription(Request $request)
    {
        $estate = $this->resolveBillingEstate($request);
        $subscription = $estate->subscription()->with('plan')->firstOrFail();

        $unitsUsed = $estate->units()->count();
        $residentsUsed = Resident::where('estate_id', $estate->id)->count();
        $unitLimit = $subscription->plan->unit_limit === null
            ? null
            : $subscription->plan->unit_limit + $subscription->extra_units;

        $graceDays = (int) config('billing.grace_period_days', 3);
        $renewalOverdue = $subscription->status === 'active'
            && $subscription->current_period_end
            && $subscription->current_period_end->isPast();
        $inGracePeriod = $renewalOverdue
            && ! $subscription->current_period_end->copy()->addDays($graceDays)->isPast();

        return response()->json([
            'subscription' => [
                'status' => $subscription->status,
                'billingCycle' => $subscription->billing_cycle,
                'currentPeriodStart' => $subscription->current_period_start?->toIso8601String(),
                'currentPeriodEnd' => $subscription->current_period_end?->toIso8601String(),
                'daysUntilRenewal' => $subscription->daysUntilRenewal(),
                'extraUnits' => $subscription->extra_units,
                'isPastDue' => $subscription->status === 'past_due',
                'renewalOverdue' => $renewalOverdue,
                'inGracePeriod' => $inGracePeriod,
                'plan' => $this->formatPlan($subscription->plan),
            ],
            'usage' => [
                'unitsUsed' => $unitsUsed,
                'unitLimit' => $unitLimit,
                'residentsUsed' => $residentsUsed,
                'residentLimit' => $subscription->plan->resident_limit,
            ],
        ]);
    }

    /**
     * GET /api/v1/admin/billing/plans
     */
    public function plans(Request $request)
    {
        $plans = Plan::where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->map(fn(Plan $p) => $this->formatPlan($p));

        return response()->json(['plans' => $plans]);
    }

    /**
     * GET /api/v1/admin/billing/transactions
     */
    public function transactions(Request $request)
    {
        $estate = $this->resolveBillingEstate($request);

        $transactions = BillingTransaction::where('estate_id', $estate->id)
            ->latest()
            ->get()
            ->map(fn(BillingTransaction $t) => [
                'id' => $t->id,
                'reference' => $t->paystack_reference,
                'amount' => $t->amount,
                'currency' => $t->currency,
                'type' => $t->type,
                'status' => $t->status,
                'paidAt' => $t->paid_at?->toIso8601String(),
                'createdAt' => $t->created_at?->toIso8601String(),
            ]);

        return response()->json(['transactions' => $transactions]);
    }

    /**
     * POST /api/v1/admin/billing/checkout
     * Body: { planId, billingCycle } for a tier upgrade/renewal,
     *    or { extraUnits } for a free-trial pay-as-you-go top-up.
     */
    public function checkout(Request $request)
    {
        $estate = $this->resolveBillingEstate($request);
        $subscription = $estate->subscription()->with('plan')->firstOrFail();
        /** @var Admin $admin */
        $admin = $request->user();

        if ($request->filled('extraUnits')) {
            $data = $request->validate(['extraUnits' => 'required|integer|min:1|max:1000']);

            if ($subscription->plan->code !== 'free_trial' || ! $subscription->plan->price_addon_unit_monthly) {
                return response()->json([
                    'error' => true,
                    'code' => 'ADDON_NOT_AVAILABLE',
                    'message' => 'Extra units can only be purchased on the free trial plan.',
                ], 422);
            }

            $amount = $subscription->plan->price_addon_unit_monthly * $data['extraUnits'];
            $type = 'extra_units';
            $metadata = [
                'type' => $type,
                'estate_id' => $estate->id,
                'extra_units' => $data['extraUnits'],
            ];
        } else {
            $data = $request->validate([
                'planId' => 'required|exists:plans,id',
                'billingCycle' => 'required|in:monthly,yearly',
            ]);

            $plan = Plan::findOrFail($data['planId']);

            if ($plan->is_custom) {
                return response()->json([
                    'error' => true,
                    'code' => 'CUSTOM_PLAN_CONTACT_US',
                    'message' => 'This tier requires a custom quote — please contact us.',
                ], 422);
            }

            $amount = $data['billingCycle'] === 'yearly' ? $plan->price_yearly : $plan->price_monthly;

            if ($amount === null) {
                return response()->json([
                    'error' => true,
                    'code' => 'PLAN_PRICE_UNAVAILABLE',
                    'message' => 'This plan is not available for self-service checkout.',
                ], 422);
            }

            $type = ($subscription->plan_id === $plan->id
                && $subscription->billing_cycle === $data['billingCycle']
                && $subscription->status !== 'trialing')
                ? 'renewal'
                : 'plan_purchase';

            $metadata = [
                'type' => $type,
                'estate_id' => $estate->id,
                'plan_id' => $plan->id,
                'billing_cycle' => $data['billingCycle'],
            ];
        }

        $amountKobo = (int) round($amount * 100);
        $reference = 'GPSUB-' . strtoupper(uniqid());

        BillingTransaction::create([
            'estate_id' => $estate->id,
            'subscription_id' => $subscription->id,
            'paystack_reference' => $reference,
            'amount' => $amount,
            'currency' => 'NGN',
            'type' => $type,
            'status' => 'pending',
            'metadata' => $metadata,
        ]);

        $response = Http::withToken(config('services.paystack.secret_key'))
            ->post('https://api.paystack.co/transaction/initialize', [
                'email' => $admin->email,
                'amount' => $amountKobo,
                'reference' => $reference,
                'metadata' => $metadata,
            ]);

        if (! $response->successful() || ! $response->json('status')) {
            Log::error('Paystack subscription initialization failed', ['body' => $response->body()]);
            return response()->json([
                'error' => true,
                'message' => 'Payment initialization failed. Please try again.',
            ], 502);
        }

        return response()->json([
            'authorizationUrl' => $response->json('data.authorization_url'),
            'reference' => $reference,
            'amount' => $amountKobo,
            'email' => $admin->email,
        ]);
    }

    /**
     * POST /api/v1/admin/billing/verify
     * Body: { reference }
     */
    public function verify(Request $request)
    {
        $estate = $this->resolveBillingEstate($request);

        $data = $request->validate(['reference' => 'required|string']);

        $transaction = BillingTransaction::where('estate_id', $estate->id)
            ->where('paystack_reference', $data['reference'])
            ->firstOrFail();

        if ($transaction->status === 'success') {
            return response()->json(['message' => 'Payment already verified.']);
        }

        $response = Http::withToken(config('services.paystack.secret_key'))
            ->get("https://api.paystack.co/transaction/verify/{$data['reference']}");

        if (! $response->successful() || $response->json('data.status') !== 'success') {
            return response()->json([
                'error' => true,
                'code' => 'PAYMENT_NOT_VERIFIED',
                'message' => 'Payment could not be verified. Please contact support if funds were debited.',
            ], 422);
        }

        $this->finalizeTransaction($transaction);

        return response()->json(['message' => 'Payment verified and subscription updated.']);
    }

    /**
     * POST /api/v1/paystack/billing/webhook
     * Public — reconciliation fallback for subscription payments.
     */
    public function webhook(Request $request)
    {
        $secret = config('services.paystack.secret_key');
        $signature = $request->header('x-paystack-signature');
        $payload = $request->getContent();

        if ($signature !== hash_hmac('sha512', $payload, $secret)) {
            return response()->json(['error' => 'Invalid signature.'], 401);
        }

        if ($request->json('event') !== 'charge.success') {
            return response()->json(['message' => 'Event ignored.']);
        }

        $reference = $request->json('data.reference');
        $transaction = BillingTransaction::where('paystack_reference', $reference)->first();

        if (! $transaction || $transaction->status === 'success') {
            return response()->json(['message' => 'Nothing to reconcile.']);
        }

        $this->finalizeTransaction($transaction);

        return response()->json(['message' => 'Webhook processed.']);
    }

    /**
     * Apply a successfully-verified transaction to its subscription.
     */
    private function finalizeTransaction(BillingTransaction $transaction): void
    {
        DB::transaction(function () use ($transaction) {
            $subscription = Subscription::with('plan')->findOrFail($transaction->subscription_id);
            $metadata = $transaction->metadata ?? [];

            if ($transaction->type === 'extra_units') {
                $subscription->increment('extra_units', (int) ($metadata['extra_units'] ?? 0));
            } else {
                $cycle = $metadata['billing_cycle'] ?? $subscription->billing_cycle;
                $periodEnd = $cycle === 'yearly' ? now()->addYear() : now()->addMonth();

                $subscription->update([
                    'plan_id' => $metadata['plan_id'] ?? $subscription->plan_id,
                    'billing_cycle' => $cycle,
                    'status' => 'active',
                    'current_period_start' => now(),
                    'current_period_end' => $periodEnd,
                    'paystack_reference' => $transaction->paystack_reference,
                ]);
            }

            $transaction->update([
                'status' => 'success',
                'paid_at' => now(),
            ]);
        });
    }

    private function resolveBillingEstate(Request $request): Estate
    {
        /** @var Admin $admin */
        $admin = $request->user();

        if ($admin->isEstateAdmin()) {
            $estateId = $admin->currentEstateId();
            abort_unless($estateId, 404, 'No estate found for this account.');
            return Estate::findOrFail($estateId);
        }

        $estateId = $request->query('estate_id') ?? $request->input('estate_id');
        abort_unless($estateId, 422, 'estate_id is required.');

        return Estate::findOrFail($estateId);
    }

    private function formatPlan(Plan $plan): array
    {
        return [
            'id' => $plan->id,
            'code' => $plan->code,
            'name' => $plan->name,
            'unitLimit' => $plan->unit_limit,
            'residentLimit' => $plan->resident_limit,
            'priceMonthly' => $plan->price_monthly,
            'priceYearly' => $plan->price_yearly,
            'priceAddonUnitMonthly' => $plan->price_addon_unit_monthly,
            'isCustom' => $plan->is_custom,
        ];
    }
}
