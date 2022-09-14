<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - StoreCreativeTest.php
 */

namespace Tests\Feature\Creatives;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Neo\Enums\Capability;
use Neo\Models\Actor;
use Neo\Models\Content;
use Neo\Models\Library;
use Neo\Modules\Broadcast\Models\Creative;
use Tests\TestCase;

class StoreCreativeTest extends TestCase {
    use DatabaseTransactions;

    /**
     * Assert guests cannot call this route
     */
    public function testGuestsAreProhibited(): void {
        $actor    = Actor::factory()->create();
        $library  = Library::factory()->create(["owner_id" => $actor->id]);
        $content  = Content::factory()->create([
            "owner_id"   => $actor->id,
            "library_id" => $library->id,
        ]);
        $response = $this->json("POST", "/v1/contents/$content->id");
        $response->assertUnauthorized();
    }

    /**
     * Assert user cannot call this route without proper capability
     */
    public function testActorCannotCallRouteWithoutProperCapability(): void {
        $actor = Actor::factory()->create();
        $this->actingAs($actor);

        $library = Library::factory()->create(["owner_id" => $actor->id]);
        $content = Content::factory()->create([
            "owner_id"   => $actor->id,
            "library_id" => $library->id,
        ]);

        $response = $this->json("POST", "/v1/contents/$content->id");
        $response->assertForbidden();
    }

    /**
     * Assert Route returns success on correct request
     */
//    public function testActorCanCallRouteWithProperCapability () {
//        Storage::fake('public');
//
//        $actor = Actor::factory()->create()->addCapability(Capability::contents_edit());
//        $this->actingAs($actor);
//
//        $library = Library::factory()->create(["owner_id" => $actor->id]);
//        $content = Content::factory()->create([
//            "owner_id"   => $actor->id,
//            "library_id" => $library->id,
//        ]);
//
//        $response = $this->json("POST",
//            "/v1/contents/" . $content->id,
//            [
//                "frame_id" => $content->format->frames[0]->id,
//                "file"     => UploadedFile::fake()->image("ad-01",
//                    $content->format->frames[0]->width,
//                    $content->format->frames[0]->height),
//            ]);
//        $response->assertCreated()
//                 ->assertJsonStructure([
//                     "id",
//                     "owner_id",
//                     "content_id",
//                     "frame_id",
//                     "extension",
//                     "status",
//                     "checksum",
//                 ]);
//    }

    /**
     * Asserts route returns correct error on bad request
     */
    public function testCorrectResponseOnBadRequest(): void {
        $actor = Actor::factory()->create()->addCapability(Capability::contents_edit());
        $this->actingAs($actor);

        $library = Library::factory()->create(["owner_id" => $actor->id]);
        $content = Content::factory()->create([
            "owner_id"   => $actor->id,
            "library_id" => $library->id,
        ]);

        $response = $this->json("POST", "/v1/contents/$content->id");
        $response->assertStatus(422)
                 ->assertJsonValidationErrors([
                     "frame_id",
                     "file",
                 ]);
    }

    /**
     * Asserts correct error if a creative is already present
     */
    public function testCorrectResponseOnAlreadyExistingCreative(): void {
        Storage::fake('public');

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

        Creative::factory()->create([
            "owner_id"   => $actor->id,
            "content_id" => $content->id,
            "frame_id"   => $content->layout->frames[0]->id,
        ]);

        $response = $this->json("POST",
            "/v1/contents/$content->id",
            [
                "frame_id" => $content->layout->frames[0]->id,
                "file"     => UploadedFile::fake()->image("ad-01",
                    $content->layout->frames[0]->width,
                    $content->layout->frames[0]->height),
            ]);
        $response->assertStatus(422);
    }

    /**
     * Asserts correct error if creative doesn't match the frame and content criterion
     */
    public function testCorrectResponseOnBadCreative(): void {
        Storage::fake('public');

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

        $response = $this->json("POST",
            "/v1/contents/" . $content->id,
            [
                "frame_id" => $content->layout->frames[0]->id,
                "file"     => UploadedFile::fake()->image("ad-01.jpeg",
                    $content->layout->frames[0]->width + 100,
                    $content->layout->frames[0]->height),
            ]);
        $response->assertStatus(422);
    }
}
