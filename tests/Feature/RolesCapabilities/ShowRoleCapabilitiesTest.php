<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <Valentin Dufois>
 *
 * @neo/api - ShowRoleCapabilitiesTest.php
 */

namespace Tests\Feature\RolesCapabilities;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Mail;
use Neo\Enums\Capability;
use Neo\Models\Actor;
use Neo\Models\Role;
use Tests\TestCase;

class ShowRoleCapabilitiesTest extends TestCase
{
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
    public function testGuestsAreProhibited(): void {
        $response = $this->json('GET', '/v1/roles/1/capabilities');
        $response->assertUnauthorized();
    }

    /**
     * Asserts route is secured with proper capability
     *
     * @return void
     */
    public function testRouteIsSecured(): void {
        $actor = Actor::factory()->create();
        $this->actingAs($actor);

        $role = Role::factory()->create();

        $response = $this->json('GET', '/v1/roles/'. $role->id .'/capabilities');
        $response->assertForbidden();
    }

    /**
     * Asserts user with proper capability can get a role's capabilities
     *
     * @return void
     */
    public function testActorCanListRoleCapability(): void {
        $actor = Actor::factory()->create()->addCapability(Capability::roles_edit());
        $this->actingAs($actor);

        $role = Role::factory()->create();

        $response = $this->json('GET', '/v1/roles/'. $role->id .'/capabilities');
        $response->assertOk();
    }

    /**
     * Asserts correct response on bad role id
     *
     * @return void
     */
    public function testCorrectResponseOnBadRoleID(): void {
        $actor = Actor::factory()->create()->addCapability(Capability::roles_edit());
        $this->actingAs($actor);

        $response = $this->json('GET', '/v1/roles/9999999/capabilities');
        $response->assertStatus(404);
    }
}
