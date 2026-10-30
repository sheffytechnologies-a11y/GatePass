<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\Pass;
use App\Models\Unit;
use App\Models\UnitAccessPhrase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Daily spoken access phrase for visitors without a smartphone.
 *
 * Residents read (and can rotate) their unit's phrase for today and pass it
 * to the visitor by call or SMS. At the gate, security picks the unit and
 * types what the visitor says; a match admits the visitor as an On-site pass.
 */
class AccessPhraseController extends Controller
{
    /**
     * GET /api/v1/access-phrase
     */
    public function show(Request $request)
    {
        $resident = $request->user()->resident()->with('unit')->first();

        if (! $resident || ! $resident->unit) {
            return $this->noResidentProfile();
        }

        $phrase = UnitAccessPhrase::forUnit($resident->unit);

        return response()->json([
            'accessPhrase' => $this->formatPhrase($phrase, $resident->unit),
        ]);
    }

    /**
     * POST /api/v1/access-phrase/regenerate
     */
    public function regenerate(Request $request)
    {
        $resident = $request->user()->resident()->with('unit')->first();

        if (! $resident || ! $resident->unit) {
            return $this->noResidentProfile();
        }

        $phrase = UnitAccessPhrase::forUnit($resident->unit)->rotate();

        return response()->json([
            'accessPhrase' => $this->formatPhrase($phrase, $resident->unit),
        ]);
    }

    /**
     * GET /api/v1/access-phrase/units
     * Units in the guard's estate that have residents to admit visitors to.
     */
    public function units(Request $request)
    {
        $user = $request->user();

        if ($error = $this->rejectNonSecurity($user)) {
            return $error;
        }

        $units = Unit::where('estate_id', $user->estate_id)
            ->where('is_active', true)
            ->whereHas('residents', fn ($q) => $q->where('is_active', true))
            ->orderBy('flat_address')
            ->get(['id', 'flat_address']);

        return response()->json([
            'units' => $units->map(fn (Unit $u) => [
                'id'          => (string) $u->id,
                'flatAddress' => $u->flat_address,
            ])->values(),
        ]);
    }

    /**
     * POST /api/v1/access-phrase/verify
     * Body: { unitId, phrase, visitorName, visitorPhone?, purpose?, vehiclePlate? }
     */
    public function verify(Request $request)
    {
        $user = $request->user();

        if ($error = $this->rejectNonSecurity($user)) {
            return $error;
        }

        $data = $request->validate([
            'unitId'       => 'required|integer',
            'phrase'       => 'required|string|max:100',
            'visitorName'  => 'required|string|max:100',
            'visitorPhone' => 'nullable|string|max:20',
            'purpose'      => 'nullable|string|in:Personal Visit,Delivery,Service,Business,Other',
            'vehiclePlate' => 'nullable|string|max:20',
        ]);

        $unit = Unit::where('id', $data['unitId'])
            ->where('estate_id', $user->estate_id)
            ->first();

        if (! $unit) {
            return response()->json([
                'error'   => true,
                'code'    => 'UNIT_NOT_FOUND',
                'message' => 'Unit not found in this estate.',
            ], 404);
        }

        $residents = $unit->residents()
            ->where('is_active', true)
            ->orderByRaw("CASE WHEN role = 'owner' THEN 0 ELSE 1 END")
            ->orderBy('id')
            ->get();

        if ($residents->isEmpty()) {
            return response()->json([
                'error'   => true,
                'code'    => 'NO_RESIDENTS',
                'message' => 'This unit has no active residents.',
            ], 422);
        }

        // Only an already-issued phrase can match: if no resident has opened
        // today's phrase, nobody could have given it to the visitor.
        $phrase = UnitAccessPhrase::findForToday($unit);

        if ($phrase?->isLocked()) {
            return response()->json([
                'error'   => true,
                'code'    => 'PHRASE_LOCKED',
                'message' => 'Too many wrong attempts for this unit today. Ask the resident to generate a new phrase.',
            ], 429);
        }

        if (! $phrase || ! $phrase->matches($data['phrase'])) {
            return $this->rejectWrongPhrase($phrase, $unit, $residents);
        }

        $host = $residents->first();

        $pass = DB::transaction(function () use ($data, $unit, $host, $residents) {
            $ulid = Str::ulid();
            $tz = config('access.phrase_timezone');

            $pass = Pass::create([
                'ulid'          => $ulid,
                'resident_id'   => $host->id,
                'estate_id'     => $unit->estate_id,
                'visitor_name'  => $data['visitorName'],
                'visitor_phone' => $data['visitorPhone'] ?? null,
                'vehicle_plate' => $data['vehiclePlate'] ?? null,
                'purpose'       => $data['purpose'] ?? 'Personal Visit',
                'type'          => 'One-time',
                'status'        => 'On-site',
                'items_flagged' => false,
                'qr_data'       => (string) $ulid,
                'expires_at'    => now($tz)->endOfDay()->setTimezone(config('app.timezone')),
                'arrived_at'    => now(),
            ]);

            foreach ($residents as $resident) {
                Notification::create([
                    'resident_id' => $resident->id,
                    'pass_id'     => $pass->id,
                    'type'        => 'arrival',
                    'message'     => "{$pass->visitor_name} was let in at the gate using your daily access phrase.",
                ]);
            }

            return $pass;
        });

        return response()->json([
            'pass' => [
                'id'          => $pass->ulid,
                'visitorName' => $pass->visitor_name,
                'status'      => $pass->status,
                'hostUnit'    => $unit->flat_address,
                'hostName'    => $host->user?->name ?? '',
                'arrivedAt'   => $pass->arrived_at->toIso8601String(),
            ],
        ], 201);
    }

    // ─── Helpers ────────────────────────────────────────────────────────────

    private function rejectWrongPhrase(?UnitAccessPhrase $phrase, Unit $unit, $residents)
    {
        $remaining = null;

        if ($phrase) {
            $phrase->increment('failed_attempts');
            $remaining = $phrase->attemptsRemaining();

            $message = $remaining > 0
                ? "Someone at the gate gave a wrong access phrase for {$unit->flat_address}."
                : "Access phrase for {$unit->flat_address} is locked after too many wrong attempts. Generate a new one if you are expecting a visitor.";

            foreach ($residents as $resident) {
                Notification::create([
                    'resident_id' => $resident->id,
                    'type'        => 'denied',
                    'message'     => $message,
                ]);
            }
        }

        return response()->json([
            'error'             => true,
            'code'              => 'PHRASE_INVALID',
            'message'           => 'The access phrase does not match this unit.',
            'attemptsRemaining' => $remaining,
        ], 422);
    }

    private function rejectNonSecurity($user)
    {
        if ($user->type !== 'security') {
            return response()->json([
                'error'   => true,
                'code'    => 'FORBIDDEN',
                'message' => 'Only security users can verify access phrases.',
            ], 403);
        }

        if (! $user->estate_id) {
            return response()->json([
                'error'   => true,
                'code'    => 'NO_ESTATE',
                'message' => 'Your account is not assigned to an estate.',
            ], 403);
        }

        return null;
    }

    private function noResidentProfile()
    {
        return response()->json([
            'error'   => true,
            'code'    => 'NO_RESIDENT_PROFILE',
            'message' => 'No resident profile found.',
        ], 403);
    }

    private function formatPhrase(UnitAccessPhrase $phrase, Unit $unit): array
    {
        return [
            'phrase'            => $phrase->phrase,
            'validFor'          => $phrase->phrase_date,
            'expiresAt'         => now(config('access.phrase_timezone'))->endOfDay()->toIso8601String(),
            'flatAddress'       => $unit->flat_address,
            'locked'            => $phrase->isLocked(),
            'attemptsRemaining' => $phrase->attemptsRemaining(),
        ];
    }
}
