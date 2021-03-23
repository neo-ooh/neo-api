<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ListLibrariesTest.php
 */

namespace Tests\Feature\Libraries;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Neo\Models\Actor;
use Neo\Models\Library;
use Tests\TestCase;

class ListLibrariesTest extends TestCase {
    use DatabaseTransactions;

    /**
     * Assert guests cannot call this route
     *
     * @return void
     */
    public function testGuestsCannotCallThisRoute (): void
    {
        $response = $this->json('GET', '/v1/libraries');
        $response->assertUnauthorized();
    }

    /**
     * Assert user with proper capability can call this route
     */
    public function testActorWithProperCapabilityCanCallThisRoute (): void
    {
        $actor = Actor::factory()->create();
        $this->actingAs($actor);

        $response = $this->json('GET', '/v1/libraries');
        $response->assertOk();
    }

    /**
     * Assert the user's own libraries are correctly returned
     */
    public function testRouteReturnsActorOwnLibraries (): void
    {
        /** @var Actor $actor */
        $actor = Actor::factory()->create();
        $this->actingAs($actor);

        $librariesCount = 3;
        Library::factory($librariesCount)->create(["owner_id" => $actor->id]);

        $response = $this->json("GET", "/v1/libraries");
        $response->assertOk()
                 ->assertJsonCount($librariesCount)
                 ->assertJsonStructure([
                     "*" => [
                         "id",
                         "owner_id",
                         "name",
                         "content_limit",
                         "contents_count",
                     ],
                 ]);
    }

    /**
     * Assert the user's children libraries are correctly returned
     */
    public function testRouteReturnsActorsChildrenLibraries (): void
    {
        $actor = Actor::factory()->create();
        $this->actingAs($actor);

        $childrenCount = 3;
        $librariesCount = 3;

        $children = Actor::factory($childrenCount)->create()->each->moveTo($actor);
        $children->each(function ($child) use ($librariesCount) {
            Library::factory($librariesCount)->create(["owner_id" => $child->id]);
        });

        $response = $this->json("GET", "/v1/libraries");
        $response->assertOk()
                 ->assertJsonCount($childrenCount * $librariesCount)
                 ->assertJsonStructure([
                     "*" => [
                         "id",
                         "owner_id",
                         "name",
                         "content_limit",
                         "contents_count",
                     ],
                 ]);
    }

    /**
     * Assert user can access its parent libraries if its parent is a group
     */
    public function testRouteReturnsParentLibraryIfItIsAGroup (): void
    {
        /** @var Actor $parent */
        $parent = Actor::factory()->create(["is_group" => true]);

        /** @var Actor $actor */
        $actor = Actor::factory()->create()->moveTo($parent);
        $this->actingAs($actor);

        $librariesCount = 3;

        Library::factory($librariesCount)->create(["owner_id" => $parent->id]);

        $request = $this->json("GET", "/v1/libraries");
        $request->assertOk()
                ->assertJsonCount($librariesCount)
                ->assertJsonStructure([
                    "*" => [
                        "id",
                        "owner_id",
                        "name",
                        "content_limit",
                        "contents_count",
                    ],
                ]);
    }

    /**
     * Assert user cannot access its parent libraries if its parent is not group
     */
    public function testRouteDoNotReturnsParentLibraryIfItIsNotAGroup (): void
    {
        /** @var Actor $parent */
        $parent = Actor::factory()->create();


        /** @var Actor $actor */
        $actor = Actor::factory()->create()->moveTo($parent);
        $this->actingAs($actor);

        $librariesCount = 3;

        Library::factory($librariesCount)->create(["owner_id" => $parent->id]);

        $request = $this->json("GET", "/v1/libraries");
        $request->assertOk()
                ->assertJsonCount(0);
    }

    /**
     * Assert user can access libraries shared with it
     */
    public function testRouteReturnsSharedLibraries (): void
    {
        /** @var Actor $actor */
        $actor = Actor::factory()->create();
        $this->actingAs($actor);

        $otherActor = Actor::factory()->create();
        $library = Library::factory()->create(["owner_id" => $otherActor]);
        $actor->shared_libraries()->attach($library);

        $request = $this->json("GET", "/v1/libraries");
        $request->assertOk()
                ->assertJsonCount(1)
                ->assertJsonStructure([
                    "*" => [
                        "id",
                        "owner_id",
                        "name",
                        "content_limit",
                        "contents_count",
                    ],
                ]);
    }
}
