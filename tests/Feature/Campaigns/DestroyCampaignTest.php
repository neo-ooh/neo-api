<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <Valentin Dufois>
 *
 * @neo/api - $file.filePath
 */

namespace Tests\Feature\Campaigns;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Neo\Enums\Capability;
use Neo\Models\Actor;
use Neo\Models\Campaign;
use Tests\TestCase;

class DestroyCampaignTest extends TestCase {
    use DatabaseTransactions;

    /**
     * Assert guests cannot call this route
     */
    public function testGuestsAreForbidden (): void
    {
        /** @var Actor $actor */
        $actor    = Actor::factory()->create();
        /** @var Campaign $campaign */
        $campaign = Campaign::factory()->create(["owner_id" => $actor->id]);

        $response = $this->json("DELETE", "/v1/campaigns/" . $campaign->id);
        $response->assertUnauthorized();
    }

    /**
     * Assert only users with the proper capability can call this route
     */
    public function testOnlyActorWithProperCapabilityCanCallThisRoute (): void
    {
        /** @var Actor $actor */
        $actor = Actor::factory()->create();
        $this->actingAs($actor);

        /** @var Campaign $campaign */
        $campaign = Campaign::factory()->create(["owner_id" => $actor->id]);

        $response = $this->json("DELETE", "/v1/campaigns/" . $campaign->id);
        $response->assertForbidden();

        $actor->addCapability(Capability::campaigns_edit());

        $response = $this->json("DELETE", "/v1/campaigns/" . $campaign->id);
        $response->assertOk();
    }

    /**
     * Assert correct error on invalid campaign
     */
    public function testCorrectErrorOnInvalidCampaign (): void
    {
        /** @var Actor $actor */
        $actor = Actor::factory()->create()->addCapability(Capability::campaigns_edit());
        $this->actingAs($actor);

        $response = $this->json("DELETE", "/v1/campaigns/999999");
        $response->assertNotFound();
    }

    /**
     * Assert user cannot destroy inaccessible campaign
     */
    public function testCannotDestroyInaccessibleCampaign (): void
    {
        /** @var Actor $actor */
        $actor = Actor::factory()->create()->addCapability(Capability::campaigns_edit());
        $this->actingAs($actor);

        /** @var Actor $otherActor */
        $otherActor = Actor::factory()->create();
        /** @var Campaign $campaign */
        $campaign = Campaign::factory()->create(["owner_id" => $otherActor->id]);

        $response = $this->json("DELETE", "/v1/campaigns/" . $campaign->id);
        $response->assertForbidden();
    }
}
