<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <Valentin Dufois>
 *
 * @neo/api - ListLibraryContentsTest.php
 */

namespace Tests\Feature\Contents;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Neo\Models\Actor;
use Neo\Models\Content;
use Neo\Models\Library;
use Tests\TestCase;

class ListLibraryContentsTest extends TestCase {
    use DatabaseTransactions;

    /**
     * Assert guest cannot access this route
     */
    public function testGuestsAreForbidden (): void
    {
        $actor = Actor::factory()->create();
        $library = Library::factory()->create(["owner_id" => $actor->id]);

        $response = $this->json("GET", "/v1/libraries/" . $library->id . "/contents");
        $response->assertUnauthorized();
    }

    /**
     * Assert all the library content are returned
     */
    public function testCorrectResponse (): void
    {
        $actor = Actor::factory()->create();
        $this->actingAs($actor);

        $library = Library::factory()->create(["owner_id" => $actor->id]);

        $contentCount = 3;
        Content::factory($contentCount)->create([
            "owner_id"   => $actor->id,
            "library_id" => $library->id,
        ]);

        $response = $this->json("GET", "/v1/libraries/" . $library->id . "/contents");
        $response->assertOk()
                 ->assertJsonCount($contentCount)
                 ->assertJsonStructure([
                     "*" => [
                         "id",
                         "owner_id",
                         "library_id",
                         "layout_id",
                         "name",
                         "scheduling_duration",
                         "scheduling_times",
                     ],
                 ]);
    }

    /**
     * Assert user cannot access content from inaccessible library
     */

    public function testCannotListContentFromInaccessibleLibrary (): void
    {
        $actor = Actor::factory()->create();
        $this->actingAs($actor);

        $otherActor = Actor::factory()->create();
        $library = Library::factory()->create(["owner_id" => $otherActor->id]);

        $contentCount = 3;
        Content::factory($contentCount)->create([
            "owner_id"   => $actor->id,
            "library_id" => $library->id,
        ]);

        $response = $this->json("GET", "/v1/libraries/" . $library->id . "/contents");
        $response->assertForbidden();
    }
}
