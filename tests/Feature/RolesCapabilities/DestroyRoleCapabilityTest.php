<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <Valentin Dufois>
 *
 * @neo/api - DestroyRoleCapabilityTest.php
 */

namespace Tests\Feature\RolesCapabilities;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Mail;
use Neo\Enums\Capability as CapabilitiesEnum;
use Neo\Models\Actor;
use Neo\Models\Capability;
use Neo\Models\Role;
use Tests\TestCase;

class DestroyRoleCapabilityTest extends TestCase {
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
        $response = $this->json('DELETE', '/v1/roles/1/capabilities');
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

        $response = $this->json('DELETE', '/v1/roles/' . $role->id . '/capabilities');
        $response->assertForbidden();
    }

    /**
     * Asserts user with proper capability can remove a capability from a role
     *
     * @return void
     */
    public function testActorCanRemoveCapabilityFromRole (): void {
        $actor = Actor::factory()->create()->addCapability(CapabilitiesEnum::roles_edit());
        $this->actingAs($actor);

        $role = Role::factory()->create();
        $capability = (new Capability)->where("slug", "=", CapabilitiesEnum::tests)->first();

        $role->capabilities()->attach($capability);

        $response = $this->json('DELETE',
            '/v1/roles/' . $role->id . '/capabilities',
            [
                "capability" => $capability->id,
            ]);
        $response->assertOk();
    }

    /**
     * Asserts correct error when trying to remove a capability not attached to role
     *
     * @return void
     */
    public function testCorrectResponseOnNotAssociatedCapability (): void {
        $actor = Actor::factory()->create()->addCapability(CapabilitiesEnum::roles_edit());
        $this->actingAs($actor);

        $role = Role::factory()->create();
        $capability = Capability::where("slug", "=", CapabilitiesEnum::tests)->first();

        $response = $this->json('DELETE',
            '/v1/roles/' . $role->id . '/capabilities',
            [
                "capability" => $capability->id,
            ]);
        $response->assertForbidden();
    }

    /**
     * Asserts correct response on bad capability id
     *
     * @return void
     */
    public function testCorrectResponseOnBadCapabilityID (): void {
        $actor = Actor::factory()->create()->addCapability(CapabilitiesEnum::roles_edit());
        $this->actingAs($actor);

        $role = Role::factory()->create();

        $response = $this->json('DELETE',
            '/v1/roles/' . $role->id . '/capabilities',
            [
                "capability" => 999999,
            ]);
        $response->assertStatus(422);
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

        $response = $this->json('DELETE', '/v1/roles/' . $role->id . '/capabilities', []);
        $response->assertStatus(422);
    }
}
