<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - StoreActorRoleTest.php
 */

namespace Tests\Feature\ActorsRoles;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Mail;
use Neo\Enums\Capability;
use Neo\Models\Actor;
use Neo\Models\Role;
use Tests\TestCase;

class StoreActorRoleTest extends TestCase {
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
        $response = $this->json('POST', '/v1/roles/1/actors');
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

        $response = $this->json('POST', '/v1/roles/' . $role->id . '/actors');
        $response->assertForbidden();
    }

    /**
     * Asserts user with proper capability can add a user to a role
     *
     * @return void
     */
    public function testActorCanAddAccessibleActorToRole (): void {
        $actor = Actor::factory()->create()->addCapability(Capability::actors_edit());
        $this->actingAs($actor);

        $otherActor = Actor::factory()->create()->moveTo($actor);

        $role = Role::factory()->create();

        $response = $this->json('POST',
            '/v1/roles/' . $role->id . '/actors',
            [
                "actor_id" => $otherActor->id,
            ]);
        $response->assertOk()
                 ->assertJsonCount(1);
    }

    /**
     * Asserts error when trying to add the same user multiple times
     *
     * @return void
     */
    public function testCorrectResponseOnAddingSameActorMultipleTimes (): void {
        $actor = Actor::factory()->create()->addCapability(Capability::actors_edit());
        $this->actingAs($actor);

        $otherActor = Actor::factory()->create()->moveTo($actor);

        $role = Role::factory()->create();

        $response = $this->json('POST',
            '/v1/roles/' . $role->id . '/actors',
            [
                "actor_id" => $otherActor->id,
            ]);
        $response->assertOk();

        $response = $this->json('POST',
            '/v1/roles/' . $role->id . '/actors',
            [
                "actor_id" => $otherActor->id,
            ]);
        $response->assertForbidden(); // No duplicates
    }

    /**
     * Asserts correct response on bad request
     *
     * @return void
     */
    public function testCorrectResponseOnBadRequest (): void {
        $actor = Actor::factory()->create()->addCapability(Capability::actors_edit());
        $this->actingAs($actor);

        $role = Role::factory()->create();

        $response = $this->json('POST', '/v1/roles/' . $role->id . '/actors', []);
        $response->assertStatus(422);
    }

    /**
     * Asserts correct response on trying to add a role to an unrelated user
     *
     * @return void
     */
    public function testCorrectResponseOnAddingUnrelatedActor (): void {
        $actor = Actor::factory()->create()->addCapability(Capability::actors_edit());
        $this->actingAs($actor);

        $otherActor = Actor::factory()->create();

        $role = Role::factory()->create();

        $response = $this->json('POST',
            '/v1/roles/' . $role->id . '/actors',
            [
                "actor_id" => $otherActor->id,
            ]);
        $response->assertStatus(422);
    }
}
