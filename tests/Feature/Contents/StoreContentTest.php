<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - StoreContentTest.php
 */

namespace Tests\Feature\Contents;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Neo\Enums\Capability;
use Neo\Models\Actor;
use Neo\Models\Format;
use Neo\Models\Library;
use Tests\TestCase;

class StoreContentTest extends TestCase {
    use DatabaseTransactions;

    /**
     * Assert guest cannot access this route
     */
    public function testGuestsAreForbidden(): void {
        $response = $this->json("POST", "/v1/contents");
        $response->assertUnauthorized();
    }

    /**
     * Assert user without proper capabilities cannot call this route
     */
    public function testCannotCallRouteWithoutProperCapability(): void {
        $actor = Actor::factory()->create();
        $this->actingAs($actor);

        $response = $this->json("POST", "/v1/contents");
        $response->assertForbidden();
    }

    /**
     * Assert user with proper capability can call this route
     */
    public function testRouteCanBeCalledWithProperCapability(): void {
        $actor = Actor::factory()->create()->addCapability(Capability::contents_edit());
        $this->actingAs($actor);

        $response = $this->json("POST", "/v1/contents");
        $response->assertStatus(422)
                 ->assertJsonValidationErrors([
                     "owner_id",
                     "library_id",
                     "layout_id",
                 ]);
    }

    /**
     * Assert correct request yields correct response
     */
    public function testCorrectResponseOnCorrectRequest(): void {
        $actor = Actor::factory()->create()->addCapability(Capability::contents_edit());
        $this->actingAs($actor);

        $library = Library::factory()->create(["owner_id" => $actor->id]);
        $format  = Format::query()->has("layouts")->inRandomOrder()->first();

        $response = $this->json("POST",
            "/v1/contents",
            [
                "owner_id"   => $actor->id,
                "library_id" => $library->id,
                "layout_id"  => $format->layouts[0]->id,
            ]);
        $response->assertCreated()
                 ->assertJsonStructure([
                     "id",
                     "owner_id",
                     "library_id",
                     "layout_id",
                     "name",
                     "scheduling_duration",
                     "scheduling_times",
                 ]);
    }

    /**
     * Assert user cannot create content in inaccessible library
     */
    public function testCannotCreateContentInInaccessibleLibrary(): void {
        $actor = Actor::factory()->create()->addCapability(Capability::contents_edit());
        $this->actingAs($actor);

        $otherActor = Actor::factory()->create();
        $library    = Library::factory()->create(["owner_id" => $otherActor->id]);

        $format = Format::query()->has("layouts")->inRandomOrder()->first();

        $response = $this->json("POST",
            "/v1/contents",
            [
                "owner_id"   => $actor->id,
                "library_id" => $library->id,
                "layout"     => $format->layouts[0]->id,
                "name"       => "test-content",
            ]);
        $response->assertStatus(422)
                 ->assertJsonValidationErrors(["library_id"]);
    }
}
