<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - UpdateLibraryTest.php
 */

namespace Tests\Feature\Libraries;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Neo\Enums\Capability;
use Neo\Models\Actor;
use Neo\Models\Library;
use Tests\TestCase;

class UpdateLibraryTest extends TestCase {
    use DatabaseTransactions;

    /**
     * Asserts guests cannot call this route
     *
     * @return void
     */
    public function testGuestsCannotCallThisRoute (): void
    {
        $library = Library::factory()->create(["owner_id" => Actor::query()->first()->id]);
        $response = $this->json('PUT', '/v1/libraries/' . $library->id);
        $response->assertUnauthorized();
    }

    /**
     * Asserts user without proper capability cannot call this route
     */
    public function testActorWithoutProperCapabilityCannotCallThisRoute (): void
    {
        $actor = Actor::factory()->create();
        $this->actingAs($actor);

        $library = Library::factory()->create(["owner_id" => $actor->id]);

        $response = $this->json('PUT', '/v1/libraries/' . $library->id);
        $response->assertForbidden();
    }

    /**
     * Asserts user with proper capability can call this route
     */
    public function testActorWithProperCapabilityCanCallThisRoute (): void
    {
        $actor = Actor::factory()->create()->addCapability(Capability::libraries_edit());
        $this->actingAs($actor);

        $library = Library::factory()->create(["owner_id" => $actor->id]);

        $response = $this->json('PUT',
            '/v1/libraries/' . $library->id,
            [
                "name"          => $library->name . '---updated',
                "owner_id"      => $library->owner_id,
                "content_limit" => 10,
            ]);
        $response->assertOk()
                 ->assertJson([
                     "name" => $library->name . '---updated',
                 ]);
    }

    /**
     * Asserts correct error is returned on bad request
     */
    public function testCorrectErrorIsReturnedOnBadRequest (): void
    {
        $actor = Actor::factory()->create()->addCapability(Capability::libraries_edit());
        $this->actingAs($actor);

        $library = Library::factory()->create(["owner_id" => $actor->id]);

        $response = $this->json('PUT', '/v1/libraries/' . $library->id, []);
        $response->assertStatus(422)
                 ->assertJsonValidationErrors(["name", "owner_id", "content_limit"]);
    }

    /**
     * Asserts correct response is returned on good request
     */
    public function testCorrectResponseIsReturnedOnGoodRequest (): void
    {
        $actor = Actor::factory()->create()->addCapability(Capability::libraries_edit());
        $this->actingAs($actor);

        $library = Library::factory()->create(["owner_id" => $actor->id]);

        $response = $this->json('PUT',
            '/v1/libraries/' . $library->id,
            [
                "name"          => $library->name,
                "owner_id"      => $actor->id,
                "content_limit" => $library->content_limit + 10,
            ]);
        $response->assertOk()
                 ->assertJsonStructure([
                     "id",
                     "owner_id",
                     "owner" => [
                         "id",
                         "name",
                     ],
                     "name",
                     "content_limit",
                 ]);
    }

    /**
     * Asserts user cannot create a library with an inaccessible parent
     */
    public function testActorCannotUpdateLibraryWithInaccessibleParent (): void
    {
        $actor = Actor::factory()->create()->addCapability(Capability::libraries_edit());
        $this->actingAs($actor);

        $otherActor = Actor::factory()->create();
        $library = Library::factory()->create(["owner_id" => $otherActor->id]);

        $response = $this->json('PUT',
            '/v1/libraries/' . $library->id,
            [
                "name"          => $library->name,
                "owner_id"      => $otherActor->id,
                "content_limit" => $library->content_limit,
            ]);
        $response->assertForbidden();
    }
}
