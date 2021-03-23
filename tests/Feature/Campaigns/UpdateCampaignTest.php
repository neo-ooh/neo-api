<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - UpdateCampaignTest.php
 */

namespace Tests\Feature\Campaigns;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Neo\Enums\Capability;
use Neo\Models\Actor;
use Neo\Models\Campaign;
use Tests\TestCase;

class UpdateCampaignTest extends TestCase {
    use DatabaseTransactions;

    /**
     * Assert guests cannot call this route
     */
    public function testGuestsAreProhibited(): void {
        $actor    = Actor::factory()->create();
        $campaign = Campaign::factory()->create(["owner_id" => $actor->id]);

        $response = $this->json("PUT", "/v1/campaigns/" . $campaign->id);
        $response->assertUnauthorized();
    }

    /**
     * Assert only user with the proper capability can call this route
     */
    public function testOnlyActorWithTheProperCapabilityCanCallThisRoute(): void {
        $actor = Actor::factory()->create();
        $this->actingAs($actor);
        $campaign = Campaign::factory()->create(["owner_id" => $actor->id]);

        $response = $this->json("PUT", "/v1/campaigns/" . $campaign->id);
        $response->assertForbidden();

        $actor->addCapability(Capability::campaigns_edit());

        $response = $this->json("PUT", "/v1/campaigns/" . $campaign->id);
        $response->assertStatus(422);
    }

    /**
     * Assert Correct response on correct request
     */
    public function testCorrectResponseOnCorrectRequest(): void {
        $actor = Actor::factory()->create()->addCapability(Capability::campaigns_edit());
        $this->actingAs($actor);

        $campaign = Campaign::factory()->create(["owner_id" => $actor->id]);

        $response = $this->json("PUT",
            "/v1/campaigns/" . $campaign->id,
            [
                "name"             => "test-campaign-1111",
                "owner_id"         => $campaign->owner_id,
                "content_limit"    => $campaign->content_limit + 5,
                "display_duration" => $campaign->display_duration,
                "start_date"       => $campaign->start_date,
                "end_date"         => $campaign->end_date,
                "loop_saturation"  => 1
            ]);
        $response->assertOk()
                 ->assertJsonStructure([
                     "id",
                     "owner_id",
                     "owner",
                     "content_limit",
                     "display_duration",
                     "start_date",
                     "end_date",
                     "loop_saturation"
                 ]);
    }

    /**
     * Assert correct errors on bad request
     */
    public function testCorrectErrorsOnBadRequest(): void {
        $actor = Actor::factory()->create()->addCapability(Capability::campaigns_edit());
        $this->actingAs($actor);

        $campaign = Campaign::factory()->create(["owner_id" => $actor->id]);

        $response = $this->json("PUT", "/v1/campaigns/" . $campaign->id);
        $response->assertStatus(422)
                 ->assertJsonValidationErrors([
                     "name",
                     "owner_id",
                     "content_limit",
                     "display_duration",
                     "start_date",
                     "end_date",
                 ]);
    }

    /**
     * Assert cannot update inaccessible campaign
     */
    public function testActorCannotUpdateInaccessibleCampaign(): void {
        $actor = Actor::factory()->create()->addCapability(Capability::campaigns_edit());
        $this->actingAs($actor);

        $otherActor = Actor::factory()->create();
        $campaign   = Campaign::factory()->create(["owner_id" => $otherActor->id]);

        $response = $this->json("PUT", "/v1/campaigns/" . $campaign->id);
        $response->assertForbidden();
    }
}
