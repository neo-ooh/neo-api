<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <Valentin Dufois>
 *
 * @neo/api - UpdateContentTest.php
 */

namespace Tests\Feature\Contents;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Neo\Enums\Capability;
use Neo\Models\Actor;
use Neo\Models\Content;
use Neo\Models\Library;
use Tests\TestCase;

class UpdateContentTest extends TestCase {
    use DatabaseTransactions;

    /**
     * Assert guest cannot access this route
     */
    public function testGuestsAreForbidden (): void
    {
        $actor = Actor::factory()->create();
        $library = Library::factory()->create(["owner_id" => $actor->id]);
        $content = Content::factory()->create([
            "owner_id"   => $actor->id,
            "library_id" => $library->id,
        ]);

        $response = $this->json("PUT", "/v1/contents/" . $content->id);
        $response->assertUnauthorized();
    }

    /**
     * Assert a user without proper capability cannot edit a content
     */
    public function testActorWithoutProperCapabilityCannotCallThisRoute (): void
    {
        $actor = Actor::factory()->create();
        $this->actingAs($actor);
        $library = Library::factory()->create(["owner_id" => $actor->id]);
        $content = Content::factory()->create([
            "owner_id"   => $actor->id,
            "library_id" => $library->id,
        ]);

        $response = $this->json("PUT", "/v1/contents/" . $content->id);
        $response->assertForbidden();
    }

    /**
     * Assert a user cannot edit a content in an inaccessible library
     */
    public function testActorCannotEditContentInInaccessibleLibrary (): void
    {
        /** @var Actor $actor */
        $actor = Actor::factory()->create()->addCapability(Capability::contents_edit());
        $this->actingAs($actor);

        /** @var Actor $otherActor */
        $otherActor = Actor::factory()->create();

        /** @var Library $library */
        $library = Library::factory()->create(["owner_id" => $otherActor->id]);

        /** @var Content $content */
        $content = Content::factory()->create([
            "owner_id"   => $actor->id,
            "library_id" => $library->id,
        ]);

        $response = $this->json("PUT", "/v1/contents/{$content->id}", [
            "owner_id"   => $actor->id,
            "library_id" => $library->id,
            "name"       => "library-test",
        ]);
        $response->assertJsonValidationErrors(["library_id"]);
    }

    /**
     * Assert Actor with proper capability can edit content
     */
    public function testActorWithProperCapabilityCanCallThisRoute (): void
    {
        /** @var Actor $actor */
        $actor = Actor::factory()->create()->addCapability(Capability::contents_edit());
        $this->actingAs($actor);

        /** @var Library $library */
        $library = Library::factory()->create(["owner_id" => $actor->id]);

        /** @var Content $content */
        $content = Content::factory()->create([
            "owner_id"   => $actor->id,
            "library_id" => $library->id,
        ]);

        $response = $this->json("PUT",
            "/v1/contents/" . $content->id,
            [
                "owner_id"   => $actor->id,
                "library_id" => $library->id,
                "name"       => "library-test",
            ]);
        $response->assertOk()
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
     * Assert Actor with proper can edit another user content if accessible
     */
    public function testActorWithProperCapabilityCanEditAnotherAccessibleActorContent (): void
    {
        /** @var Actor $actor */
        $actor = Actor::factory()->create()->addCapability(Capability::contents_edit());
        $this->actingAs($actor);

        /** @var Actor $otherActor */
        $otherActor = Actor::factory()->create();

        /** @var Library $library */
        $library = Library::factory()->create(["owner_id" => $otherActor->id]);

        /** @var Content $content */
        $content = Content::factory()->create([
            "owner_id"   => $otherActor->id,
            "library_id" => $library->id,
        ]);

        $actor->shared_libraries()->attach($library->id);

        $response = $this->json("PUT",
            "/v1/contents/" . $content->id,
            [
                "owner_id"   => $otherActor->id,
                "library_id" => $library->id,
                "name"       => "library-test",
            ]);
        $response->assertOk()
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

}
