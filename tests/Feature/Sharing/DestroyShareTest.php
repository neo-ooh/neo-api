<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - DestroyShareTest.php
 */

namespace Tests\Feature\Sharing;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Mail;
use Neo\Enums\Capability;
use Neo\Models\Actor;
use Tests\TestCase;

class DestroyShareTest extends TestCase {
    use DatabaseTransactions;

    public function setUp (): void {
        parent::setUp();

        Mail::Fake();
    }

    /**
     * Assert guests cannot use this route
     *
     * @return void
     */
    public function testGuestAreProhibited (): void {
        $response = $this->json('DELETE', '/v1/actors/{1}/shares');
        $response->assertUnauthorized();
    }

    /**
     * Assert only users with the proper capability can access this route
     *
     * @return void
     */
    public function testErrorOnActorWithoutProperCapability (): void {
        // This actor has no capability
        $actor = Actor::factory()->create();
        $this->actingAs($actor);

        $response = $this->json('DELETE', '/v1/actors/' . $actor->id . '/shares');
        $response->assertForbidden();
    }

    /**
     * Assert user can remove its own shares
     *
     * @return void
     */
    public function testActorCanDeleteEmittedShares (): void {
        // This actor has no capability
        $actor = Actor::factory()->create()->addCapability(Capability::actors_edit());
        $this->actingAs($actor);

        $otherActor = Actor::factory()->create();
        $actor->sharings()->attach($otherActor);
        $actor->refresh();

        $response = $this->json('DELETE',
            '/v1/actors/' . $actor->id . '/shares',
            [
                "actor" => $otherActor->id,
            ]);
        $response->assertOk();
    }

    /**
     * Assert user cannot remove received shares
     *
     * @return void
     */
    public function testActorCannotDeleteReceivedShares (): void {
        // This actor has no capability
        $actor = Actor::factory()->create()->addCapability(Capability::actors_edit());
        $this->actingAs($actor);

        $otherActor = Actor::factory()->create()->addCapability(Capability::actors_edit());

        $otherActor->sharings()->attach($actor);
        $actor->refresh();
        $otherActor->refresh();

        $response = $this->json('DELETE',
            '/v1/actors/' . $actor->id . '/shares',
            [
                "actor" => $otherActor->id,
            ]);
        $response->assertForbidden();
    }

    /**
     * Assert user can remove shares of accessible users
     *
     * @return void
     */
    public function testActorCanDeleteAccessibleActorShares (): void {
        $actor = Actor::factory()->create()->addCapability(Capability::actors_edit());
        $this->actingAs($actor);

        $otherActor = Actor::factory()->create()->addCapability(Capability::actors_edit());
        $otherActorChild = Actor::factory()->create()
            ->moveTo($otherActor)
            ->addCapability(Capability::actors_edit());

        $otherActor->sharings()->attach($actor);
        $otherActorChild->sharings()->attach($actor);
        $actor->refresh();

        $response = $this->json('DELETE',
            '/v1/actors/' . $otherActorChild->id . '/shares',
            [
                "actor" => $actor->id,
            ]);
        $response->assertOk();
    }

    /**
     * Assert correct response received on bad request
     *
     * @return void
     */
    public function testCorrectResponseReceivedOnBadRequest (): void {
        // This actor has no capability
        $actor = Actor::factory()->create()->addCapability(Capability::actors_edit());
        $this->actingAs($actor);

        $otherActor = Actor::factory()->create()->addCapability(Capability::actors_edit());
        $otherActor->sharings()->attach($actor);

        $actor->refresh();

        // Empty request body --> bad
        $response = $this->json('DELETE', '/v1/actors/' . $actor->id . '/shares', []);
        $response->assertStatus(422);
    }
}
