<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ListSharesTest.php
 */

namespace Tests\Feature\Sharing;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Mail;
use Neo\Enums\Capability;
use Neo\Models\Actor;
use Tests\TestCase;

class ListSharesTest extends TestCase {
    use DatabaseTransactions;

    public function setUp (): void {
        parent::setUp();

        Mail::Fake();
    }

    /**
     * Test guests cannot use this route
     *
     * @return void
     */
    public function testGuestsAreProhibited (): void {
        $response = $this->json('GET', '/v1/actors/1/shares');
        $response->assertUnauthorized();
    }

    /**
     * Test route is secured
     *
     * @return void
     */
    public function testRouteIsSecured (): void {
        // This actor has no capability
        $actor = Actor::factory()->create();
        $this->actingAs($actor);

        $response = $this->json('GET', '/v1/actors/' . $actor->id . '/shares');
        $response->assertForbidden(); // Refused
    }

    /**
     * A user can access its own shares it it has the proper capability
     */
    public function testActorCanAccessItsOwnShares (): void {
        $actor = Actor::factory()->create()->addCapability(Capability::actors_edit());
        $this->actingAs($actor);

        $response = $this->json('GET', '/v1/actors/' . $actor->id . '/shares');
        $response->assertOk(); // OK
    }

    /**
     * A user can see another user shares if it has access to it
     */
    public function testActorCanAccessAccessibleActorShare (): void {
        $actor = Actor::factory()->create()->addCapability(Capability::actors_edit());
        $this->actingAs($actor);

        $response = $this->json('GET', '/v1/actors/' . $actor->id . '/shares');
        $response->assertOk(); // OK
    }

    /**
     * A user cannot see a user shares if it has not access to it
     */
    public function testActorCannotAccessUnrelatedActorShares (): void {
        $actor = Actor::factory()->create()->addCapability(Capability::actors_edit());
        $this->actingAs($actor);

        $otherActor = Actor::factory()->create();

        $response = $this->json('GET', '/v1/actors/' . $otherActor->id . '/shares');
        $response->assertForbidden(); // Forbidden
    }

    /**
     * Accessing a user shares returns all shares in the correct format
     */
    public function testCorrectResponseHasCorrectFormat (): void {
        $actor = Actor::factory()->create()->addCapability(Capability::actors_edit());
        $this->actingAs($actor);

        $response = $this->json('GET', '/v1/actors/' . $actor->id . '/shares');
        $response->assertOk()
                 ->assertJsonStructure([
                    "sharings",
                    "sharers"
                 ]);
    }
}
