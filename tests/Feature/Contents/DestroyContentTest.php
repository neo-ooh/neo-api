<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - DestroyContentTest.php
 */

namespace Tests\Feature\Contents;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Neo\Enums\Capability;
use Neo\Models\Actor;
use Neo\Modules\Broadcast\Models\Content;
use Neo\Modules\Broadcast\Models\Library;
use Tests\TestCase;

class DestroyContentTest extends TestCase {
    use DatabaseTransactions;

    /**
     * Assert guest cannot access this route
     */
    public function testGuestsAreForbidden(): void {
        $actor   = Actor::factory()->create();
        $library = Library::factory()->create(["owner_id" => $actor->id]);
        $content = Content::factory()->create([
            "owner_id"   => $actor->id,
            "library_id" => $library->id,
        ]);

        $response = $this->json("DELETE", "/v1/contents/" . $content->id);
        $response->assertUnauthorized();
    }

    /**
     * Assert a user without proper capability cannot edit a content
     */
    public function testActorWithoutProperCapabilityCannotCallThisRoute(): void {
        $actor = Actor::factory()->create();
        $this->actingAs($actor);
        $library = Library::factory()->create(["owner_id" => $actor->id]);
        $content = Content::factory()->create([
            "owner_id"   => $actor->id,
            "library_id" => $library->id,
        ]);

        $response = $this->json("DELETE", "/v1/contents/" . $content->id);
        $response->assertForbidden();
    }

    /**
     * Assert a user cannot edit a content in an inaccessible library
     */
    public function testActorCannotDestroyContentInInaccessibleLibrary(): void {
        $actor = Actor::factory()->create()->addCapability(Capability::contents_edit());
        $this->actingAs($actor);

        $otherActor = Actor::factory()->create();
        $library    = Library::factory()->create(["owner_id" => $otherActor->id]);
        $content    = Content::factory()->create([
            "owner_id"   => $otherActor->id,
            "library_id" => $library->id,
        ]);

        $response = $this->json("DELETE", "/v1/contents/" . $content->id);
        $response->assertForbidden();
    }

    /**
     * Assert Actor with proper capability can edit content
     */
    public function testActorWithProperCapabilityCanCallThisRoute(): void {
        $actor = Actor::factory()->create()->addCapability(Capability::contents_edit());
        $this->actingAs($actor);
        $library = Library::factory()->create(["owner_id" => $actor->id]);
        $content = Content::factory()->create([
            "owner_id"   => $actor->id,
            "library_id" => $library->id,
        ]);

        $response = $this->json("DELETE", "/v1/contents/" . $content->id);
        $response->assertOk();
    }

    /**
     * Assert Actor with proper can edit another user content if accessible
     */
    public function testActorWithProperCapabilityCanEditAnotherAccessibleActorContent(): void {
        $actor = Actor::factory()->create()->addCapability(Capability::contents_edit());
        $this->actingAs($actor);

        $otherActor = Actor::factory()->create();
        $library    = Library::factory()->create(["owner_id" => $otherActor->id]);
        $content    = Content::factory()->create([
            "owner_id"   => $actor->id,
            "library_id" => $library->id,
        ]);

        $actor->shared_libraries()->attach($library->id);

        $response = $this->json("DELETE", "/v1/contents/" . $content->id);
        $response->assertOk();
    }

}
