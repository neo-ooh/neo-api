<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <Valentin Dufois>
 *
 * @neo/api - $file.filePath
 */

namespace Tests\Feature\Roles;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Mail;
use Neo\Enums\Capability;
use Neo\Models\Actor;
use Neo\Models\Role;
use Tests\TestCase;

class ShowRoleTest extends TestCase
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
        $response = $this->json('GET', '/v1/roles/1');
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

        $response = $this->json('GET', '/v1/roles/'.$role->id);
        $response->assertForbidden();
    }

    /**
     * Asserts user with proper capability can access a role
     *
     * @return void
     */
    public function testActorCanAccessRole(): void {
        $actor = Actor::factory()->create()->addCapability(Capability::roles_edit());
        $this->actingAs($actor);

        $role = Role::factory()->create();

        $response = $this->json('GET', '/v1/roles/'. $role->id);
        $response->assertOk();
    }

    /**
     * Asserts role access returns correct response
     *
     * @return void
     */
    public function testAccessRoleReturnsCorrectResponse(): void {
        $actor = Actor::factory()->create()->addCapability(Capability::roles_edit());
        $this->actingAs($actor);

        $role = Role::factory()->create();

        $response = $this->json('GET', '/v1/roles/'. $role->id);
        $response->assertOk()
                 ->assertJsonStructure([
                     "id",
                     "name",
                     "desc",
                     "created_at",
                     "updated_at",
                     "capabilities",
                     "actors",
                 ]);
    }

    /**
     * Asserts correct response on bad Role
     *
     * @return void
     */
    public function testCorrectResponseOnBadRole(): void {
        $actor = Actor::factory()->create()->addCapability(Capability::roles_edit());
        $this->actingAs($actor);

        $response = $this->json('GET', '/v1/roles/'. 99999);
        $response->assertStatus(404);
    }
}
