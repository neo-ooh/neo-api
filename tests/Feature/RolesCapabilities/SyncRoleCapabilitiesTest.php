<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - SyncRoleCapabilitiesTest.php
 */

namespace Tests\Feature\RolesCapabilities;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Mail;
use Neo\Enums\Capability as CapabilitiesEnum;
use Neo\Models\Actor;
use Neo\Models\Capability;
use Neo\Models\Role;
use Tests\TestCase;

class SyncRoleCapabilitiesTest extends TestCase {
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
        $response = $this->json('PUT', '/v1/roles/1/capabilities');
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

        $response = $this->json('PUT', '/v1/roles/' . $role->id . '/capabilities');
        $response->assertForbidden();
    }

    /**
     * Asserts user with proper capability can update a capability of a role
     *
     * @return void
     */
    public function testActorCanUpdateRoleCapability (): void {
        $actor = Actor::factory()->create()->addCapability(CapabilitiesEnum::roles_edit());
        $this->actingAs($actor);

        $role = Role::factory()->create();
        $capability = Capability::query()->where("slug", "=", CapabilitiesEnum::tests)->first();

        $response = $this->json('PUT',
            '/v1/roles/' . $role->id . '/capabilities',
            [
                "capabilities" => [ $capability->id ],
            ]);
        $response->assertOk();
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

        $response = $this->json('PUT',
            '/v1/roles/' . $role->id . '/capabilities',
            [
                "capabilities" => [ 999999 ],
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

        $response = $this->json('PUT', '/v1/roles/' . $role->id . '/capabilities', []);
        $response->assertStatus(422);
    }
}
