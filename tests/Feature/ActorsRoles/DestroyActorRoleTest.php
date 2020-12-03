<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <Valentin Dufois>
 *
 * @neo/api - $file.filePath
 */

namespace Tests\Feature\ActorsRoles;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Mail;
use Neo\Enums\Capability;
use Neo\Models\Actor;
use Neo\Models\Role;
use Tests\TestCase;

class DestroyActorRoleTest extends TestCase
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
        $response = $this->json('DELETE', '/v1/roles/1/actors');
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

        $response = $this->json('DELETE', '/v1/roles/'. $role->id .'/actors');
        $response->assertForbidden();
    }

    /**
     * Asserts user with proper capability can remove a user from a role
     *
     * @return void
     */
    public function testActorCanRemoveActorFromRole(): void {
        $actor = Actor::factory()->create()->addCapability(Capability::actors_edit());
        $this->actingAs($actor);

        $otherActor = Actor::factory()->create()->moveTo($actor);

        $role = Role::factory()->create();
        $role->actors()->attach($otherActor);

        $response = $this->json('DELETE', '/v1/roles/'. $role->id .'/actors', [
            "actor_id" => $otherActor->id,
        ]);
        $response->assertOk();
    }

    /**
     * Asserts correct error when trying to remove a user not attached to the role
     *
     * @return void
     */
    public function testCorrectResponseOnNotAssociatedActor(): void {
        $actor = Actor::factory()->create()->addCapability(Capability::actors_edit());
        $this->actingAs($actor);

        $otherActor = Actor::factory()->create()->moveTo($actor);

        $role = Role::factory()->create();

        $response = $this->json('DELETE', '/v1/roles/'. $role->id .'/actors', [
            "actor_id" => $otherActor->id,
        ]);
        $response->assertForbidden();
    }

    /**
     * Asserts correct error when trying to remove an unrelated user
     *
     * @return void
     */
    public function testCorrectResponseOnUnrelatedActor(): void {
        $actor = Actor::factory()->create()->addCapability(Capability::actors_edit());
        $this->actingAs($actor);

        $otherActor = Actor::factory()->create();

        $role = Role::factory()->create();

        $response = $this->json('DELETE', '/v1/roles/'. $role->id .'/actors', [
            "actor_id" => $otherActor->id,
        ]);
        $response->assertStatus(422);
    }

    /**
     * Asserts correct response on bad user id
     *
     * @return void
     */
    public function testCorrectResponseOnBadActorID(): void {
        $actor = Actor::factory()->create()->addCapability(Capability::actors_edit());
        $this->actingAs($actor);

        $role = Role::factory()->create();

        $response = $this->json('DELETE', '/v1/roles/'. $role->id .'/actors', [
            "actor_id" => 999999,
        ]);
        $response->assertStatus(422);
    }

    /**
     * Asserts correct response on bad request
     *
     * @return void
     */
    public function testCorrectResponseOnBadRequest(): void {
        $actor = Actor::factory()->create()->addCapability(Capability::actors_edit());
        $this->actingAs($actor);

        $role = Role::factory()->create();

        $response = $this->json('DELETE', '/v1/roles/'. $role->id .'/actors', []);
        $response->assertStatus(422);
    }
}
