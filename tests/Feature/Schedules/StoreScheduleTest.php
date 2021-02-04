<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <Valentin Dufois>
 *
 * @neo/api - StoreScheduleTest.php
 */

namespace Tests\Feature\Schedules;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Neo\Enums\Capability;
use Neo\Models\Actor;
use Neo\Models\Campaign;
use Neo\Models\Content;
use Neo\Models\Creative;
use Neo\Models\Format;
use Neo\Models\Library;
use Storage;
use Tests\TestCase;

class StoreScheduleTest extends TestCase {
    use DatabaseTransactions;

    /**
     * Assert guests cannot call this route
     */
    public function testGuestsAreProhibited (): void
    {
        $actor = Actor::factory()->create();
        $campaign = Campaign::factory()->create([ "owner_id" => $actor->id ]);

        $response = $this->json("POST", "/v1/campaigns/" . $campaign->id . "/schedules");
        $response->assertUnauthorized();
    }

    /**
     * Assert proper capability is required to call this route
     */
    public function testProperCapabilityIsRequiredToAccessThisRoute (): void
    {
        $actor = Actor::factory()->create();
        $this->actingAs($actor);

        $campaign = Campaign::factory()->create([ "owner_id" => $actor->id ]);

        $response = $this->json("POST", "/v1/campaigns/" . $campaign->id . "/schedules");
        $response->assertForbidden();

        $actor->addCapability(Capability::contents_schedule());

        $response = $this->json("POST", "/v1/campaigns/" . $campaign->id . "/schedules");
        $response->assertStatus(422);
    }

    /**
     * Assert correct response on correct request
     */
    public function testCorrectResponseReturnedOnCorrectRequest (): void
    {
        Storage::fake("public");

        /** @var Actor $actor */
        $actor = Actor::factory()->create()->addCapability(Capability::contents_schedule());
        $this->actingAs($actor);

        /** @var Campaign $campaign */
        $campaign = Campaign::factory()->create([ "owner_id" => $actor->id ]);

        /** @var Library $library */
        $library = Library::factory()->create([ "owner_id" => $actor->id ]);

        /** @var Content $content */
        $content = Content::factory()->create([ "library_id" => $library->id, "owner_id" => $actor->id ]);
        $content->refresh();

        foreach ($content->layout->frames as $frame) {
            Creative::factory()->create([ "owner_id"   => $actor->id,
                                          "content_id" => $content->id,
                                          "frame_id"   => $frame->id ]);
        }

        $response = $this->json("POST",
            "/v1/campaigns/{$campaign->id}/insert",
            [
                "content_id" => $content->id,
                "order" => 1
            ]);
        $response->assertCreated()
                 ->assertJsonStructure([
                     "id",
                     "campaign_id",
                     "content_id",
                     "content",
                     "owner_id",
                     "start_date",
                     "end_date",
                     "print_count",
                 ]);
    }

    /**
     * Assert it is not possible to schedule an incomplete content
     */
    public function testCannotScheduleIncompleteContent (): void
    {
        $actor = Actor::factory()->create()->addCapability(Capability::contents_schedule());
        $this->actingAs($actor);

        $campaign = Campaign::factory()->create([ "owner_id" => $actor->id ]);
        $library = Library::factory()->create([ "owner_id" => $actor->id ]);
        $content = Content::factory()->create([ "library_id" => $library->id, "owner_id" => $actor->id ]);

        $response = $this->json("POST",
            "/v1/campaigns/" . $campaign->id . "/schedules",
            [
                "content_id" => $content->id,
            ]);
        $response->assertStatus(422);
    }

    /**
     * Assert it is not possible to schedule a content with a different format than the campaign
     */
    public function testCannotScheduleContentWithDifferentFormatThanCampaign (): void
    {
        $actor = Actor::factory()->create()->addCapability(Capability::contents_schedule());
        $this->actingAs($actor);

        $format = Format::query()->first();
        $campaign = Campaign::factory()->create([ "owner_id" => $actor->id, "format_id" => $format ]);
        $library = Library::factory()->create([ "owner_id" => $actor->id ]);
        $format = Format::query()->has('layouts')->first();
        $content = Content::factory()->create([ "library_id" => $library->id,
                                                "owner_id"   => $actor->id,
                                                "layout_id"  => $format->layouts[0]->id ]);

        $response = $this->json("POST",
            "/v1/campaigns/" . $campaign->id . "/schedules",
            [
                "content_id" => $content->id,
            ]);
        $response->assertStatus(422);
    }

    /**
     * Assert it is not possible to add a schedule if the campaign is full
     */

    /**
     * Assert it is not possible to schedule a content for longer than the allowed time
     */

    /**
     * Assert it is not possible to schedule a content if its scheduling times is reached
     */
}
