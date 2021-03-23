<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - StoreShareTest.php
 */

namespace Tests\Feature\Sharing;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Mail;
use Neo\Enums\Capability;
use Neo\Models\Actor;
use Tests\TestCase;

class StoreShareTest extends TestCase {
    use DatabaseTransactions;

    public function setUp (): void {
        parent::setUp();

        Mail::Fake();
    }

    /**
     * A guest cannot share anything
     *
     * @return void
     */
    public function testGuestAreProhibited (): void {
        $response = $this->json('POST', '/v1/actors/1/shares');
        $response->assertUnauthorized();
    }

    /**
     * A user without the proper capability (actors.edit) cannot share its pool of users
     */
    public function testActorCannotShareWithoutProperCapability (): void {
        // Actor without capability
        $actor = Actor::factory()->create();
        $this->actingAs($actor);

        $response = $this->json('POST', '/v1/actors/' . $actor->id . '/shares');
        $response->assertForbidden();
    }

    /**
     * A user can create a share with a descendant
     */
    public function testActorCanShareWithDescendant (): void {
        // Actor
        $actor = Actor::factory()->create()
            ->addCapability(Capability::actors_edit());
        $this->actingAs($actor);

        $child = Actor::factory()->create()->moveTo($actor);

        // Actor wants to share with child
        $response = $this->json('POST', '/v1/actors/' . $actor->id . '/shares', [
            "actor" => $child->id,
        ]);
        $response->assertStatus(201);
    }

    /**
     * A Actor cannot share with a parent
     */
    public function testActorCannotShareWithItsParent (): void {
        // Actor
        $actor = Actor::factory()->create()
            ->addCapability(Capability::actors_edit());

        // Its child
        $child = Actor::factory()->create()->moveTo($actor);
        $this->actingAs($child);

        // Child wants to share with its parent
        $response = $this->json('POST', '/v1/actors/' . $child->id . '/shares', [
            "actor" => $actor->id,
        ]);
        $response->assertForbidden();
    }

    /**
     * A Actor cannot share with the same user multiple times
     */
    public function testActorCannotShareWithTheSameActorMultipleTimes (): void {
        // Actor
        $actor = Actor::factory()->create()
            ->addCapability(Capability::actors_edit());
        $this->actingAs($actor);

        // Its child
        $child = Actor::factory()->create()->moveTo($actor);

        // Parent wants to share with its child
        $response = $this->json('POST', '/v1/actors/' . $actor->id . '/shares', [
            "actor" => $child->id,
        ]);
        $response->assertStatus(201); // OK

        // Parent wants to share with its child, again
        $response = $this->json('POST', '/v1/actors/' . $actor->id . '/shares', [
            "actor" => $child->id,
        ]);
        $response->assertForbidden(); // Impossible
    }

    /**
     * A Actor cannot share with itself
     */
    public function testActorCannotShareWithItself (): void {
        // Actor
        $actor = Actor::factory()->create()
            ->addCapability(Capability::actors_edit());
        $this->actingAs($actor);


        // Parent wants to share with its child
        $response = $this->json('POST', '/v1/actors/' . $actor->id . '/shares', [
            "actor" => $actor->id,
        ]);
        $response->assertForbidden(); // Impossible
    }


}
