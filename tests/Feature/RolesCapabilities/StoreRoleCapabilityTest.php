<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <Valentin Dufois>
 *
 * @neo/api - StoreRoleCapabilityTest.php
 */

namespace Tests\Feature\RolesCapabilities;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Mail;
use Neo\Enums\Capability as CapabilitiesEnum;
use Neo\Models\Actor;
use Neo\Models\Capability;
use Neo\Models\Role;
use Tests\TestCase;

class StoreRoleCapabilityTest extends TestCase {
    use DatabaseTransactions;

    public function setUp (): void {
        parent::setUp();

        Mail::Fake();
    }

    /**
     * Asserts route is forbidden to guests
     *
     * @return void
     */
    public function testGuestsAreProhibited (): void {
        $response = $this->json('POST', '/v1/roles/1/capabilities');
        $response->assertUnauthorized();
    }

    /**
     * Asserts route is secured with proper capability
     *
     * @return void
     */
    public function testRouteIsSecured (): void {
        $actor = Actor::factory()->create();
        $this->actingAs($actor);

        $role = Role::factory()->create();

        $response = $this->json('POST', '/v1/roles/' . $role->id . '/capabilities');
        $response->assertForbidden();
    }

    /**
     * Asserts user with proper capability can add a capability to a role
     *
     * @return void
     */
    public function testActorCanAddCapabilityToRole (): void {
        $actor = Actor::factory()->create()->addCapability(CapabilitiesEnum::roles_edit());
        $this->actingAs($actor);

        $role = Role::factory()->create();
        $capability = Capability::where("slug", "=", CapabilitiesEnum::tests)->first();

        $response = $this->json('POST',
            '/v1/roles/' . $role->id . '/capabilities',
            [
                "capability" => $capability->id,
            ]);
        $response->assertOk();
    }

    /**
     * Asserts Adding a capability to a role returns a correct response
     *
     * @return void
     */
    public function testAddingACapabilityToARoleReturnsCorrectResponse (): void {
        $actor = Actor::factory()->create()->addCapability(CapabilitiesEnum::roles_edit());
        $this->actingAs($actor);

        $role = Role::factory()->create();
        $capability = Capability::where("slug", "=", CapabilitiesEnum::tests)->first();

        $response = $this->json('POST',
            '/v1/roles/' . $role->id . '/capabilities',
            [
                "capability" => $capability->id,
            ]);

        $response->assertOk()
                 ->assertJsonCount(1);
    }

    /**
     * Asserts error when trying to add the same capability multiple times
     *
     * @return void
     */
    public function testCorrectResponseOnAddingSameCapabilityMultipleTimes (): void {
        $actor = Actor::factory()->create()->addCapability(CapabilitiesEnum::roles_edit());
        $this->actingAs($actor);

        $role = Role::factory()->create();
        $capability = Capability::where("slug", "=", CapabilitiesEnum::tests)->first();

        $response = $this->json('POST',
            '/v1/roles/' . $role->id . '/capabilities',
            [
                "capability" => $capability->id,
            ]);

        $response->assertOk();

        $role->refresh();

        $response = $this->json('POST',
            '/v1/roles/' . $role->id . '/capabilities',
            [
                "capability" => $capability->id,
            ]);

        $response->assertForbidden(); // No duplicates
    }


    /**
     * Asserts correct response on bad request
     *
     * @return void
     */
    public function testCorrectResponseOnBadRequest (): void {
        $actor = Actor::factory()->create()->addCapability(CapabilitiesEnum::roles_edit());
        $this->actingAs($actor);

        $role = Role::factory()->create();

        $response = $this->json('POST', '/v1/roles/' . $role->id . '/capabilities', []);
        $response->assertStatus(422);
    }
}
