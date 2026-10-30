<?php

namespace Tests\Feature;

use App\Models\Estate;
use App\Models\Notification;
use App\Models\Pass;
use App\Models\Resident;
use App\Models\Unit;
use App\Models\UnitAccessPhrase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AccessPhraseTest extends TestCase
{
    use RefreshDatabase;

    private Estate $estate;
    private Unit $unit;
    private User $residentUser;
    private User $guard;

    protected function setUp(): void
    {
        parent::setUp();

        $this->estate = Estate::create(['name' => 'Test Estate']);
        $this->unit = $this->makeUnit('L1');
        $this->residentUser = $this->makeResident($this->unit, '08000000001');
        $this->guard = User::create([
            'name' => 'Guard', 'email' => 'guard@test.com', 'phone' => '08000000009',
            'type' => 'security', 'estate_id' => $this->estate->id, 'password' => 'secret', 'is_active' => true,
        ]);
    }

    public function test_resident_gets_a_stable_phrase_for_today(): void
    {
        Sanctum::actingAs($this->residentUser);

        $first = $this->getJson('/api/v1/access-phrase')->assertOk()->json('accessPhrase.phrase');
        $second = $this->getJson('/api/v1/access-phrase')->json('accessPhrase.phrase');

        $this->assertMatchesRegularExpression('/^[A-Z]+ [A-Z]+ \d{2}$/', $first);
        $this->assertSame($first, $second);
    }

    public function test_regenerate_changes_the_phrase(): void
    {
        Sanctum::actingAs($this->residentUser);

        $old = $this->getJson('/api/v1/access-phrase')->json('accessPhrase.phrase');
        $new = $this->postJson('/api/v1/access-phrase/regenerate')->assertOk()->json('accessPhrase.phrase');

        $this->assertNotSame($old, $new);
        $this->assertSame(1, UnitAccessPhrase::count());
    }

    public function test_no_two_units_share_a_phrase_on_the_same_day(): void
    {
        $phrases = collect(range(1, 200))
            ->map(fn ($i) => UnitAccessPhrase::forUnit($this->makeUnit("X{$i}"))->phrase);

        $this->assertSame($phrases->count(), $phrases->unique()->count());
    }

    public function test_guard_admits_visitor_with_correct_phrase(): void
    {
        $phrase = UnitAccessPhrase::forUnit($this->unit)->phrase;
        Sanctum::actingAs($this->guard);

        $res = $this->postJson('/api/v1/access-phrase/verify', [
            'unitId'      => $this->unit->id,
            'phrase'      => strtolower(str_replace(' ', '-', $phrase)),
            'visitorName' => 'Mama Nkechi',
        ])->assertCreated();

        $pass = Pass::where('ulid', $res->json('pass.id'))->firstOrFail();
        $this->assertSame('On-site', $pass->status);
        $this->assertNotNull($pass->arrived_at);
        $this->assertSame(1, Notification::where('type', 'arrival')->count());
    }

    public function test_wrong_phrase_is_rejected_and_locks_after_max_attempts(): void
    {
        config(['access.max_failed_attempts' => 2]);
        UnitAccessPhrase::forUnit($this->unit);
        Sanctum::actingAs($this->guard);

        $body = ['unitId' => $this->unit->id, 'phrase' => 'WRONG WORDS 00', 'visitorName' => 'X'];

        $this->postJson('/api/v1/access-phrase/verify', $body)->assertStatus(422)->assertJsonPath('attemptsRemaining', 1);
        $this->postJson('/api/v1/access-phrase/verify', $body)->assertStatus(422)->assertJsonPath('attemptsRemaining', 0);
        $this->postJson('/api/v1/access-phrase/verify', $body)->assertStatus(429)->assertJsonPath('code', 'PHRASE_LOCKED');

        $this->assertSame(0, Pass::count());
    }

    public function test_phrase_from_another_unit_does_not_work(): void
    {
        $otherUnit = $this->makeUnit('L2');
        $this->makeResident($otherUnit, '08000000002');
        UnitAccessPhrase::forUnit($this->unit);
        $otherPhrase = UnitAccessPhrase::forUnit($otherUnit)->phrase;
        Sanctum::actingAs($this->guard);

        $this->postJson('/api/v1/access-phrase/verify', [
            'unitId' => $this->unit->id, 'phrase' => $otherPhrase, 'visitorName' => 'X',
        ])->assertStatus(422)->assertJsonPath('code', 'PHRASE_INVALID');
    }

    public function test_residents_cannot_verify_and_guards_cannot_cross_estates(): void
    {
        Sanctum::actingAs($this->residentUser);
        $this->postJson('/api/v1/access-phrase/verify', [
            'unitId' => $this->unit->id, 'phrase' => 'A B 10', 'visitorName' => 'X',
        ])->assertForbidden();

        $otherEstate = Estate::create(['name' => 'Other']);
        $this->guard->update(['estate_id' => $otherEstate->id]);
        Sanctum::actingAs($this->guard->fresh());
        $this->postJson('/api/v1/access-phrase/verify', [
            'unitId' => $this->unit->id, 'phrase' => 'A B 10', 'visitorName' => 'X',
        ])->assertNotFound();
    }

    private function makeUnit(string $flat): Unit
    {
        return Unit::create([
            'estate_id' => $this->estate->id, 'lane' => 'FA', 'house' => 'H1',
            'flat' => $flat, 'flat_address' => "{$flat}, H1, FA",
        ]);
    }

    private function makeResident(Unit $unit, string $phone): User
    {
        $user = User::create([
            'name' => "Resident {$phone}", 'email' => "{$phone}@test.com", 'phone' => $phone,
            'type' => 'resident', 'estate_id' => $this->estate->id, 'password' => 'secret', 'is_active' => true,
        ]);

        // SQLite keeps the original enum('primary','member') check; MySQL uses owner/tenant.
        Resident::create([
            'user_id' => $user->id, 'unit_id' => $unit->id, 'estate_id' => $this->estate->id, 'role' => 'primary',
        ]);

        return $user;
    }
}
