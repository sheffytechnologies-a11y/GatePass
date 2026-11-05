<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Estate;
use App\Models\Pass;
use App\Models\Resident;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PropertyTopologyTest extends TestCase
{
    use RefreshDatabase;

    private Estate $estate;
    private Admin $estateAdmin;
    private Admin $superAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->estate = Estate::create([
            'name'           => 'Victoria Crest Estate',
            'address'        => 'Lekki Phase 1',
            'city'           => 'Lekki',
            'state'          => 'Lagos',
            'country'        => 'Nigeria',
            'account_name'   => 'Victoria Crest Ltd',
            'account_number' => '0123456789',
            'bank_name'      => 'Zenith Bank',
            'is_active'      => true,
        ]);

        $this->estateAdmin = Admin::create([
            'name'      => 'Estate Manager',
            'email'     => 'admin@crest.com',
            'phone'     => '08012345678',
            'password'  => 'secret123',
            'role'      => 'estate_admin',
            'is_active' => true,
        ]);
        $this->estateAdmin->estates()->attach($this->estate->id);

        $this->superAdmin = Admin::create([
            'name'      => 'Super Auditor',
            'email'     => 'super@checkpass.com',
            'phone'     => '08099999999',
            'password'  => 'supersecret',
            'role'      => 'super_admin',
            'is_active' => true,
        ]);
    }

    public function test_default_property_structure_is_lane_house_flat(): void
    {
        Sanctum::actingAs($this->estateAdmin);

        $res = $this->getJson("/api/v1/admin/estates/{$this->estate->id}/property-structure")
            ->assertOk()
            ->json('structure');

        $this->assertSame('lane_house_flat', $res['preset']);
        $this->assertCount(3, $res['levels']);
        $this->assertSame('Lane', $res['levels'][0]['name']);
        $this->assertSame('House', $res['levels'][1]['name']);
        $this->assertSame('Flat', $res['levels'][2]['name']);
    }

    public function test_estate_admin_can_update_topology_structure(): void
    {
        Sanctum::actingAs($this->estateAdmin);

        $payload = [
            'preset' => 'block_floor_suite',
            'levels' => [
                ['level' => 1, 'name' => 'Block', 'slug' => 'block', 'required' => true],
                ['level' => 2, 'name' => 'Floor', 'slug' => 'floor', 'required' => true],
                ['level' => 3, 'name' => 'Suite', 'slug' => 'suite', 'required' => true],
            ],
        ];

        $this->putJson("/api/v1/admin/estates/{$this->estate->id}/property-structure", $payload)
            ->assertOk()
            ->assertJsonPath('structure.preset', 'block_floor_suite');

        $this->assertDatabaseHas('estates', [
            'id' => $this->estate->id,
        ]);
    }

    public function test_batch_generate_properties_creates_cartesian_combinations(): void
    {
        Sanctum::actingAs($this->estateAdmin);

        $payload = [
            'estate_id' => $this->estate->id,
            'unit_type' => 'Apartment',
            'levels'    => [
                ['name' => 'Lane',  'values' => ['Lane 1', 'Lane 2']],
                ['name' => 'House', 'values' => ['House 1', 'House 2', 'House 3']],
                ['name' => 'Flat',  'values' => ['Flat A', 'Flat B']],
            ],
        ];

        // 2 lanes * 3 houses * 2 flats = 12 total units
        $res = $this->postJson('/api/v1/admin/properties/batch', $payload)
            ->assertStatus(201)
            ->json();

        $this->assertSame(12, $res['createdCount']);
        $this->assertSame(0, $res['skippedCount']);
        $this->assertSame(12, Unit::where('estate_id', $this->estate->id)->count());

        // Check sample formatted flat address (reverse order: "Flat A, House 1, Lane 1")
        $this->assertDatabaseHas('units', [
            'estate_id'    => $this->estate->id,
            'flat_address' => 'Flat A, House 1, Lane 1',
            'lane'         => 'Lane 1',
            'house'        => 'House 1',
            'flat'         => 'Flat A',
        ]);
    }

    public function test_batch_generation_skips_duplicates_safely(): void
    {
        Sanctum::actingAs($this->estateAdmin);

        // Pre-create 1 property
        Unit::create([
            'estate_id'    => $this->estate->id,
            'lane'         => 'Lane 1',
            'house'        => 'House 1',
            'flat'         => 'Flat A',
            'flat_address' => 'Flat A, House 1, Lane 1',
            'unit_type'    => 'Apartment',
        ]);

        $payload = [
            'estate_id' => $this->estate->id,
            'unit_type' => 'Apartment',
            'levels'    => [
                ['name' => 'Lane',  'values' => ['Lane 1']],
                ['name' => 'House', 'values' => ['House 1']],
                ['name' => 'Flat',  'values' => ['Flat A', 'Flat B']],
            ],
        ];

        // 2 total, 1 exists -> 1 created, 1 skipped
        $res = $this->postJson('/api/v1/admin/properties/batch', $payload)
            ->assertStatus(201)
            ->json();

        $this->assertSame(1, $res['createdCount']);
        $this->assertSame(1, $res['skippedCount']);
        $this->assertSame(2, Unit::where('estate_id', $this->estate->id)->count());
    }

    public function test_updating_topology_with_relabel_updates_existing_properties(): void
    {
        Sanctum::actingAs($this->estateAdmin);

        $unit = Unit::create([
            'estate_id'      => $this->estate->id,
            'lane'           => 'Old Lane 1',
            'house'          => 'Old House 5',
            'flat'           => 'Old Flat 2',
            'flat_address'   => 'Old Flat 2, Old House 5, Old Lane 1',
            'hierarchy_data' => ['Lane' => 'Old Lane 1', 'House' => 'Old House 5', 'Flat' => 'Old Flat 2'],
        ]);

        $updatePayload = [
            'preset'                 => 'custom',
            'relabel_existing_units' => true,
            'levels'                 => [
                ['level' => 1, 'name' => 'Street',    'slug' => 'street'],
                ['level' => 2, 'name' => 'Building',  'slug' => 'building'],
                ['level' => 3, 'name' => 'Apartment', 'slug' => 'apartment'],
            ],
        ];

        $this->putJson("/api/v1/admin/estates/{$this->estate->id}/property-structure", $updatePayload)
            ->assertOk()
            ->assertJsonPath('relabelledCount', 1);

        $freshUnit = $unit->fresh();
        $this->assertArrayHasKey('Street', $freshUnit->hierarchy_data);
        $this->assertSame('Old Lane 1', $freshUnit->hierarchy_data['Street']);
        $this->assertSame('Old House 5', $freshUnit->hierarchy_data['Building']);
        $this->assertSame('Old Flat 2', $freshUnit->hierarchy_data['Apartment']);
    }

    public function test_cannot_delete_property_with_active_residents(): void
    {
        Sanctum::actingAs($this->estateAdmin);

        $unit = Unit::create([
            'estate_id'    => $this->estate->id,
            'flat_address' => 'Flat 1, House 1, Lane 1',
        ]);

        $residentUser = User::create([
            'name'      => 'Amina Resident',
            'email'     => 'amina@test.com',
            'phone'     => '08055555555',
            'type'      => 'resident',
            'estate_id' => $this->estate->id,
            'password'  => 'secret',
            'is_active' => true,
        ]);

        Resident::create([
            'user_id'   => $residentUser->id,
            'unit_id'   => $unit->id,
            'estate_id' => $this->estate->id,
            'role'      => 'owner',
            'is_active' => true,
        ]);

        // Attempt delete
        $this->deleteJson("/api/v1/admin/properties/{$unit->id}")
            ->assertStatus(422)
            ->assertJsonPath('message', 'Cannot delete property: this property currently has assigned residents. Please remove or reassign them first.');

        $this->assertDatabaseHas('units', ['id' => $unit->id]);
    }

    public function test_property_with_gate_passes_is_deactivated_instead_of_hard_deleted(): void
    {
        Sanctum::actingAs($this->estateAdmin);

        $unit = Unit::create([
            'estate_id'    => $this->estate->id,
            'flat_address' => 'Suite 101, Tower A',
            'is_active'    => true,
        ]);

        $residentUser = User::create([
            'name'      => 'Former Resident',
            'email'     => 'former@test.com',
            'phone'     => '08077777777',
            'type'      => 'resident',
            'estate_id' => $this->estate->id,
            'password'  => 'secret',
            'is_active' => true,
        ]);

        $resident = Resident::create([
            'user_id'   => $residentUser->id,
            'unit_id'   => $unit->id,
            'estate_id' => $this->estate->id,
            'role'      => 'tenant',
            'is_active' => false, // inactive resident
        ]);

        // Historic pass exists
        Pass::create([
            'ulid'         => '01JABCD1234567890ABCDEFGH1',
            'resident_id'  => $resident->id,
            'estate_id'    => $this->estate->id,
            'visitor_name' => 'Delivery Rider',
            'type'         => 'once',
            'direction'    => 'inbound',
            'status'       => 'used',
            'access_code'  => '123456',
            'valid_from'   => now()->subDays(2),
            'valid_until'  => now()->subDays(1),
        ]);

        // Attempt delete: since pass exists, soft deactivate
        $this->deleteJson("/api/v1/admin/properties/{$unit->id}")
            ->assertOk()
            ->assertJsonPath('deactivated', true);

        $this->assertDatabaseHas('units', [
            'id'        => $unit->id,
            'is_active' => false,
        ]);
    }

    public function test_topology_tree_returns_hierarchical_nesting(): void
    {
        Sanctum::actingAs($this->estateAdmin);

        Unit::create([
            'estate_id'    => $this->estate->id,
            'lane'         => 'Lane 1',
            'house'        => 'House 1',
            'flat'         => 'Flat A',
            'flat_address' => 'Flat A, House 1, Lane 1',
            'unit_type'    => 'Apartment',
            'is_active'    => true,
        ]);

        $res = $this->getJson("/api/v1/admin/estates/{$this->estate->id}/properties/topology-tree")
            ->assertOk()
            ->json('tree');

        $this->assertArrayHasKey('Lane 1', $res);
        $this->assertArrayHasKey('House 1', $res['Lane 1']);
        $this->assertSame('Flat A', $res['Lane 1']['House 1'][0]['label']);
        $this->assertSame('Flat A, House 1, Lane 1', $res['Lane 1']['House 1'][0]['flatAddress']);
    }
}
