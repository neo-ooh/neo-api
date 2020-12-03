<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <Valentin Dufois>
 *
 * @neo/api - $file.filePath
 */

namespace Tests\Feature\Actors;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Mail;
use Neo\Enums\Capability;
use Neo\Models\Actor;
use Tests\TestCase;

class ShowActorTest extends TestCase {
    use DatabaseTransactions;

    public function setUp (): void {
        parent::setUp();

        Mail::fake();
    }

    /**
     * It is not possible to query this route if we are not logged in
     *
     * @return void
     */
    public function testGuestAreProhibited (): void {
        $actor = Actor::factory()->create();

        $response = $this->json('GET', '/v1/actors/' . $actor->id);
        $response->assertUnauthorized();
    }

    /**
     * A user can access its own record
     *
     * @return void
     */
    public function testActorCanQueryItself (): void {
        $actor = Actor::factory()->create();
        $this->actingAs($actor);

        $response = $this->json('GET', '/v1/actors/' . $actor->id);
        $response->assertOk();
    }

    /**
     * A user can access it direct descendants if it has the appropriate capability
     *
     * @return void
     */
    public function testActorCanAccessItsDirectDescendants (): void {
        $actor = Actor::factory()->create()->addCapability(Capability::actors_edit());
        $this->actingAs($actor);

        $descendant = Actor::factory()->create()->moveTo($actor);

        $response = $this->json('GET', '/v1/actors/' . $descendant->id);
        $response->assertOk();
    }

    /**
     * A user can access any of its descendants
     *
     * @return void
     */
    public function testActorCanAccessItsDescendants (): void {
        $actor = Actor::factory()->create()->addCapability(Capability::actors_edit());
        $this->actingAs($actor);

        $firstDescendant = Actor::factory()->create()->moveTo($actor);
        $secondDescendant = Actor::factory()->create()->moveTo($firstDescendant);

        $response = $this->json('GET', '/v1/actors/' . $secondDescendant->id);
        $response->assertOk();
    }

    /**
     * A user can access any of its descendants
     *
     * @return void
     */
    public function testActorCannotAccessUnrelatedActors (): void {
        $actor = Actor::factory()->create()->addCapability(Capability::actors_edit());
        $this->actingAs($actor);

        $other = Actor::factory()->create();

        $otherChild = Actor::factory()->create()->moveTo($other);

        $response = $this->json('GET', '/v1/actors/' . $otherChild->id);
        $response->assertForbidden();
    }

    /**
     * A user can access any of its descendants
     *
     * @return void
     */
    public function testActorCannotAccessItsParent (): void {
        $parent = Actor::factory()->create();

        $child = Actor::factory()
            ->create()
            ->moveTo($parent)
            ->addCapability(Capability::actors_edit());
        $this->actingAs($child);

        $response = $this->json('GET', '/v1/actors/' . $parent->id);
        $response->assertForbidden();
    }

    /**
     * A user can access any of its descendants
     *
     * @return void
     */
    public function testCorrectResponseOnSuccessfulAccess (): void {
        $parent = Actor::factory()->create()->addCapability(Capability::actors_edit());
        $this->actingAs($parent);

        $child = Actor::factory()->create()->moveTo($parent);

        $response = $this->json('GET', '/v1/actors/' . $child->id);
        $response->assertOk()
                 ->assertJsonStructure([
                     "id",
                     "parent",
                     "email",
                     "name",
                     "parent",
                     "branding",
                     "is_locked",
                 ]);
    }

    /**
     * A user can access any of its descendants
     *
     * @return void
     */
    public function testCorrectErrorOnBadActorID (): void {
        $parent = Actor::factory()->create()->addCapability(Capability::actors_edit());
        $this->actingAs($parent);

        $response = $this->json('GET', '/v1/actors/999999999');
        $response->assertForbidden(); // 404 but with model binding protection, this gives a 403 as the specified user is not in the list of accessible users
    }
}
