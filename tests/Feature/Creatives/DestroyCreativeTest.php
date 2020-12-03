<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <Valentin Dufois>
 *
 * @neo/api - $file.filePath
 */

namespace Tests\Feature\Creatives;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Storage;
use Neo\Enums\Capability;
use Neo\Models\Actor;
use Neo\Models\Content;
use Neo\Models\Creative;
use Neo\Models\Library;
use Tests\TestCase;

class DestroyCreativeTest extends TestCase {
    use DatabaseTransactions;

    /**
     * Assert guests cannot call this route
     */
    public function testGuestsAreProhibited (): void
    {
        /** @var Actor $actor */
        $actor    = Actor::factory()->create();

        /** @var Library $library */
        $library  = Library::factory()->create(["owner_id" => $actor->id]);

        /** @var Content $content */
        $content  = Content::factory()->create([
            "owner_id"   => $actor->id,
            "library_id" => $library->id,
        ]);

        /** @var Creative $creative */
        $creative = Creative::factory()->create([
            "owner_id"   => $actor->id,
            "content_id" => $content->id,
            "frame_id"   => $content->format->frames[0]->id,
        ]);

        $response = $this->json("DELETE", "/v1/creatives/{$creative->id}");
        $response->assertUnauthorized();
    }

    /**
     * Assert user cannot call this route without proper capability
     */
    public function testActorCannotCallRouteWithoutProperCapability (): void
    {
        Storage::fake();
        /** @var Actor $actor */
        $actor = Actor::factory()->create();
        $this->actingAs($actor);

        /** @var Library $library */
        $library  = Library::factory()->create(["owner_id" => $actor->id]);

        /** @var Content $content */
        $content  = Content::factory()->create([
            "owner_id"   => $actor->id,
            "library_id" => $library->id,
        ]);

        /** @var Creative $creative */
        $creative = Creative::factory()->create([
            "owner_id"   => $actor->id,
            "content_id" => $content->id,
            "frame_id"   => $content->format->frames[0]->id,
        ]);

        $response = $this->json("DELETE", "/v1/creatives/{$creative->id}");
        $response->assertForbidden();
    }

    /**
     * Assert Route returns success on correct request
     */
    public function testActorCanCallRouteWithProperCapability (): void
    {
        Storage::fake('public');

        $actor = Actor::factory()->create()->addCapability(Capability::contents_edit());
        $this->actingAs($actor);

        $library = Library::factory()->create(["owner_id" => $actor->id]);
        $content = Content::factory()->create([
            "owner_id"   => $actor->id,
            "library_id" => $library->id,
        ]);
        $creative = Creative::factory()->create([
            "owner_id"   => $actor->id,
            "content_id" => $content->id,
            "frame_id"   => $content->format->frames[0]->id,
        ]);

        $response = $this->json("DELETE", "/v1/creatives/" . $creative->id);
        $response->assertOk();
    }

    /**
     * Asserts route returns correct error on bad request
     */
    public function testCorrectResponseOnBadRequest (): void
    {
        Storage::fake();

        $actor = Actor::factory()->create()->addCapability(Capability::contents_edit());
        $this->actingAs($actor);

        $response = $this->json("DELETE", "/v1/creatives/999999999");
        $response->assertNotFound();
    }
}
