<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ShowCampaignTest.php
 */

namespace Tests\Feature\Campaigns;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Neo\Models\Actor;
use Neo\Models\Campaign;
use Tests\TestCase;

class ShowCampaignTest extends TestCase {
    use DatabaseTransactions;

    /**
     * Asserts guests cannot call this route
     */
    public function testGuestsAreProhibited (): void
    {
        $actor = Actor::factory()->create();
        $campaign = Campaign::factory()->create(["owner_id" => $actor->id]);

        $response = $this->json("GET", "/v1/campaigns/" . $campaign->id);
        $response->assertUnauthorized();
    }

    /**
     * Asserts any user can call this route
     */
    public function testLoggedInActorCanCallThisRoute (): void
    {
        $actor = Actor::factory()->create();
        $this->actingAs($actor);

        $campaign = Campaign::factory()->create(["owner_id" => $actor->id]);

        $response = $this->json("GET", "/v1/campaigns/" . $campaign->id);
        $response->assertOk();
    }

    /**
     * Asserts user cannot access inaccessible campaign
     */
    public function testCorrectErrorOnInaccessibleCampaign (): void
    {
        $actor = Actor::factory()->create();
        $this->actingAs($actor);

        $otherActor = Actor::factory()->create();

        $campaign = Campaign::factory()->create(["owner_id" => $otherActor->id]);

        $response = $this->json("GET", "/v1/campaigns/" . $campaign->id);
        $response->assertForbidden();
    }

    /**
     * Asserts correct response on good request
     */
    public function testCorrectResponseContent (): void
    {
        $actor = Actor::factory()->create();
        $this->actingAs($actor);

        $campaign = Campaign::factory()->create(["owner_id" => $actor->id]);

        $response = $this->json("GET", "/v1/campaigns/" . $campaign->id);
        $response->assertOk()
                 ->assertJsonStructure([
                     "id",
                     "owner_id",
                     "owner",
                     "format_id",
                     "format",
                     "name",
                     "display_duration",
                     "start_date",
                     "end_date",
                 ]);
    }

    /**
     * Asserts correct error on bad campaign
     */
    public function testCorrectErrorOnBadCampaign (): void
    {
        $actor = Actor::factory()->create();
        $this->actingAs($actor);

        $response = $this->json("GET", "/v1/campaigns/9999999");
        $response->assertNotFound();
    }
}
