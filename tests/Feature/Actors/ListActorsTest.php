<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <Valentin Dufois>
 *
 * @neo/api - ListActorsTest.php
 */

namespace Tests\Feature\Actors;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Mail;
use Neo\Enums\Capability;
use Neo\Models\Actor;
use Tests\TestCase;

class ListActorsTest extends TestCase
{
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
    public function testGuestAreProhibited(): void {
        $response = $this->json('GET', '/v1/actors');
        $response->assertUnauthorized();
    }

    /**
     * Assert no capability is required to list accessible users
     *
     * @return void
     */
    public function testNoCapabilityIsRequiredToListRelatedActors(): void {
        // This actor has no capabilities
        $actor = Actor::factory()->create();
        $this->actingAs($actor);

        $response = $this->json('GET', '/v1/actors');
        $response->assertOk();
    }

    /**
     * All descendants, no matter their level, is returned
     *
     * @return void
     */
    public function testAllDescendantsAreReturned(): void {
        // Create our actor
        $actor = Actor::factory()->create()->addCapability(Capability::actors_edit());
        $this->actingAs($actor);

        // Add 10 direct descendants each with 2 descendants
        $actors = Actor::factory(10)->create()->each->moveTo($actor);
        foreach($actors as $newActor) {
            Actor::factory(2)->create()->each->moveTo($newActor);
        }

        $actor->refresh();

        // Expects exactly 30 (10 + 2 * 10) users in response
        $response = $this->json('GET', '/v1/actors');
        $response->assertOk()
                 ->assertJsonCount(30);
    }

    /**
     * all returned descendants do have the current user a parent or distant parent
     *
     * @return void
     */
    public function testActorIsParentOfAllDescendants(): void {
        // Create our actor
        $actor = Actor::factory()->create()->addCapability(Capability::actors_edit());
        $this->actingAs($actor);

        // Add 10 direct descendants each with 2 descendants
        $actors = Actor::factory(10)->create()->each->moveTo($actor);
        foreach($actors as $newActor) {
            Actor::factory(2)->create()->each->moveTo($newActor);
        }

        $actor->refresh();

        // Expects exactly 30 (10 + 2 * 10) users in response
        $response = $this->json('GET', '/v1/actors');
        $response->assertOk();
        $children = $response->getOriginalContent();

        // Make sure each returned children has the current actor in its parents tree
        foreach($children as $child) {
            self::assertTrue($child->parents->pluck('id')->contains($actor->id));
        }
    }

    /**
     * Asserts this route only returns users and not groups
     */
    public function testGroupsAreNotReturned(): void {
        // Create our actor
        $actor = Actor::factory()->create()->addCapability(Capability::actors_edit());
        $this->actingAs($actor);

        // Add 5 direct descendants each with 2 descendants
        $actors = Actor::factory(5)->create()->each->moveTo($actor);
        foreach($actors as $newActor) {
            Actor::factory(2)->create()->each->moveTo($newActor);
        }

        // Add 2 direct groups descendants each with 2 actor descendants
        $groups = Actor::factory(10)->create(["is_group" => true])->each->moveTo($actor);
        foreach($groups as $newGroup) {
            Actor::factory(2)->create()->each->moveTo($newGroup);
        }

        // Expects exactly 15 (5 + 2 * 5 + 2 * 10) users in response
        $response = $this->json('GET', '/v1/actors', [
            "groups" => 0
        ]);
        $response->assertOk()
                 ->assertJsonCount(35);
    }

    /**
     * Asserts this route only returns users and not groups
     */
    public function testAActorCanBeExcluded(): void {
        // Create our actor
        $actor = Actor::factory()->create()->addCapability(Capability::actors_edit());
        $this->actingAs($actor);

        // Add 5 direct descendants each with 2 descendants
        $actors = Actor::factory(5)->create()->each->moveTo($actor);
        foreach($actors as $newActor) {
            Actor::factory(2)->create()->each->moveTo($newActor);
        }

        // Add 2 direct groups descendants each with 2 actor descendants
        $groups = Actor::factory(10)->create([ 'is_group' => true ])->each->moveTo($actor);
        foreach($groups as $newGroup) {
            Actor::factory(2)->create()->each->moveTo($newGroup);
        }

        // Expects exactly 15 (5 + 2 * 5 + 2 * 10) actors minus 3 in response
        $response = $this->json('GET', '/v1/actors', [
            "exclude" => [$actors[0]->id, $actors[1]->id, $actors[2]->id],
        ]);
        $response->assertOk()
                 ->assertJsonCount(42);
    }

    /**
     * Asserts giving specific parameter includes the current user in the returned list
     */
    public function testCurrentActorCanBeIncluded(): void {
        // Create our actor
        $actor = Actor::factory()->create()->addCapability(Capability::actors_edit());
        $this->actingAs($actor);

        // Do not add any other actor

        $response = $this->json('GET', '/v1/actors', [
            "withself" => true,
        ]);
        $response->assertOk()
                 ->assertJsonCount(1);
    }

    /**
     * Asserts it is possible to request only minimal info on users
     */
    public function testResponseCanBeMinimal(): void {
        // Create our user
        $actor = Actor::factory()->create()->addCapability(Capability::actors_edit());
        $this->actingAs($actor);

        // Add 5 direct descendants each with 2 descendants
        $actors = Actor::factory(5)->create()->each->moveTo($actor);
        foreach($actors as $newActor) {
            Actor::factory(2)->create()->each->moveTo($newActor);
        }

        // Add 2 direct groups descendants each with 2 user descendants
        $groups = Actor::factory(10)->create([ 'is_group' => true ])->each->moveTo($actor);
        foreach($groups as $newGroup) {
            Actor::factory(2)->create()->each->moveTo($newGroup);
        }

        $response = $this->json('GET', '/v1/actors', [
            "shallow" => true,
        ]);
        $response->assertOk();
    }
}
