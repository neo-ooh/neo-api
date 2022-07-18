<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ShowLibrariesTest.php
 */

namespace Tests\Feature\Libraries;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Neo\Models\Actor;
use Neo\Modules\Broadcast\Models\Library;
use Tests\TestCase;

class ShowLibrariesTest extends TestCase {
    use DatabaseTransactions;

    /**
     * Assert guests cannot call this route
     *
     * @return void
     */
    public function testGuestsCannotCallThisRoute (): void
    {
        $actor = Actor::factory()->create();
        $library = Library::factory()->create(["owner_id" => $actor->id]);

        $response = $this->json('GET', '/v1/libraries/' . $library->id);
        $response->assertUnauthorized();
    }

    /**
     * Assert user can see its own libraries
     */
    public function testActorCanAccessItsOwnLibrary (): void
    {
        $actor = Actor::factory()->create();
        $this->actingAs($actor);

        $library = Library::factory()->create(["owner_id" => $actor->id]);

        $response = $this->json("GET", "/v1/libraries/" . $library->id);
        $response->assertOk()
                 ->assertJsonStructure([
                     "id",
                     "owner_id",
                     "owner",
                     "name",
                     "content_limit",
                     "contents_count",
                     "contents",
                 ])->assertJson([
                "id" => $library->id,
            ]);
    }

    /**
     * Assert user can see its children libraries
     */
    public function testActorCanAccessItsChildrenLibraries (): void
    {
        $actor = Actor::factory()->create();
        $this->actingAs($actor);

        $children = Actor::factory()->create()->moveTo($actor);
        $library = Library::factory()->create(["owner_id" => $children->id]);

        $response = $this->json("GET", "/v1/libraries/" . $library->id);
        $response->assertOk()
                 ->assertJsonStructure([
                     "id",
                     "owner_id",
                     "owner",
                     "name",
                     "content_limit",
                     "contents_count",
                     "contents",
                 ])->assertJson([
                "id" => $library->id,
            ]);
    }

    /**
     * Assert user can see its shared libraries
     */
    public function testActorCanAccessSharedLibraries (): void
    {
        /** @var Actor $actor */
        $actor = Actor::factory()->create();
        $this->actingAs($actor);

        $otherActor = Actor::factory()->create();
        $library = Library::factory()->create(["owner_id" => $otherActor->id]);

        $actor->shared_libraries()->attach($library);

        $response = $this->json("GET", "/v1/libraries/" . $library->id);
        $response->assertOk()
                 ->assertJsonStructure([
                     "id",
                     "owner_id",
                     "owner",
                     "name",
                     "content_limit",
                     "contents_count",
                     "contents",
                 ])->assertJson([
                "id" => $library->id,
            ]);
    }

    /**
     * Assert user can see its parent libraries if it is a group
     */
    public function testActorCanAccessItsParentLibraryIfItIsAGroup (): void
    {
        /** @var Actor $parent */
        $parent  = Actor::factory()->create(["is_group" => true]);

        /** @var Library $library */
        $library = Library::factory()->create(["owner_id" => $parent->id]);

        /** @var Actor $actor */
        $actor = Actor::factory()->create()->moveTo($parent);
        $this->actingAs($actor);

        $response = $this->json("GET", "/v1/libraries/$library->id");
        $response->assertOk()
                 ->assertJsonStructure([
                     "id",
                     "owner_id",
                     "owner",
                     "name",
                     "content_limit",
                     "contents_count",
                     "contents",
                 ])->assertJson([
                "id" => $library->id,
            ]);
    }

    /**
     * Assert user cannot see its parent libraries if it is not a group
     */
    public function testActorCannotAccessItsParentLibraryIfItIsNotAGroup (): void
    {
        $parent = Actor::factory()->create();
        $library = Library::factory()->create(["owner_id" => $parent->id]);

        $actor = Actor::factory()->create()->moveTo($parent);
        $this->actingAs($actor);

        $response = $this->json("GET", "/v1/libraries/" . $library->id);
        $response->assertForbidden();
    }

    /**
     * Assert user cannot see an unrelated library
     */
    public function testActorCannotAccessUnrelatedLibrary (): void
    {
        $actor = Actor::factory()->create();
        $this->actingAs($actor);

        $otherActor = Actor::factory()->create();
        $library = Library::factory()->create(["owner_id" => $otherActor->id]);

        $response = $this->json("GET", "/v1/libraries/" . $library->id);
        $response->assertForbidden();
    }
}
