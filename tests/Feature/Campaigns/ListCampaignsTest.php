<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ListCampaignsTest.php
 */

namespace Tests\Feature\Campaigns;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Neo\Models\Actor;
use Neo\Modules\Broadcast\Models\Campaign;
use Tests\TestCase;


class ListCampaignsTest extends TestCase {
    use DatabaseTransactions;
    /**
     * Asserts guests cannot call this route
     */
    public function testGuestsAreProhibited (): void
    {
        $response = $this->json("GET", "/v1/campaigns");
        $response->assertUnauthorized();
    }

    /**
     * Asserts any logged-in users can call this route
     */
    public function testAnyLoggedInActorCanCallThisRoute (): void
    {
        $actor = Actor::factory()->create();
        $this->actingAs($actor);

        $response = $this->json("GET", "/v1/campaigns");
        $response->assertOk();
    }

    /**
     * Asserts own campaigns are correctly returned
     */
    public function testActorCampaignsAreCorrectlyReturned (): void
    {
        /** @var Actor $actor */
        $actor = Actor::factory()->create();
        $this->actingAs($actor);

        $campaignsCount = 3;
        Campaign::factory($campaignsCount)->create(["owner_id" => $actor->id]);

        $response = $this->json("GET", "/v1/campaigns");
        $response->assertOk()
                 ->assertJsonCount($campaignsCount)
                 ->assertJsonStructure([
                     "*" => [
                         "id",
                         "owner_id",
                         "format",
                         "name",
                         "display_duration",
                         "start_date",
                         "end_date",
                     ],
                 ]);
    }

    /**
     * Asserts children campaigns are correctly returned
     */
    public function testChildrenCampaignsAreCorrectlyReturned (): void
    {
        /** @var Actor $actor */
        $actor = Actor::factory()->create();
        $this->actingAs($actor);

        /** @var Actor $child */
        $child = Actor::factory()->create()->moveTo($actor);

        $campaignsCount = 3;
        Campaign::factory($campaignsCount)->create(["owner_id" => $child->id]);

        $response = $this->json("GET", "/v1/campaigns");
        $response->assertOk()
                 ->assertJsonCount($campaignsCount)
                 ->assertJsonStructure([
                       [
                             "id",
                             "owner_id",
                             "format",
                             "name",
                             "display_duration",
                             "start_date",
                             "end_date",
                        ]
                 ]);
    }

    /**
     * Asserts shared campaigns are correctly returned
     */
    public function testSharedCampaignsAreCorrectlyReturned (): void
    {
        /** @var Actor $actor */
        $actor = Actor::factory()->create();
        $this->actingAs($actor);

        /** @var Actor $child */
        $child = Actor::factory()->create();

        $campaignsCount = 3;
        $campaigns = Campaign::factory($campaignsCount)->create(["owner_id" => $child->id]);

        foreach ($campaigns as $campaign) {
            $actor->shared_campaigns()->attach($campaign->id);
        }

        $response = $this->json("GET", "/v1/campaigns");
        $response->assertOk()
                 ->assertJsonCount($campaignsCount)
                 ->assertJsonStructure([
                     "*" => [
                         "id",
                         "owner_id",
                         "format",
                         "name",
                         "display_duration",
                         "start_date",
                         "end_date",
                     ],
                 ]);
    }

    /**
     * Asserts campaigns accessible by the parent are correctly returned if the parent is a group
     */
    public function testParentGroupCampaignsAreCorrectlyReturned (): void
    {
        /** @var Actor $parent */
        $parent = Actor::factory()->create(["is_group" => true]);

        /** @var Actor $actor */
        $actor = Actor::factory()->create()->moveTo($parent);
        $this->actingAs($actor);

        $campaignsCount = 3;
        Campaign::factory($campaignsCount)->create(["owner_id" => $parent->id]);

        $response = $this->json("GET", "/v1/campaigns");
        $response->assertOk()
                 ->assertJsonCount($campaignsCount)
                 ->assertJsonStructure([
                     "*" => [
                         "id",
                         "owner_id",
                         "format",
                         "name",
                         "display_duration",
                         "start_date",
                         "end_date",
                     ],
                 ]);
    }

    /**
     * Asserts campaigns accessible by the parent are not returned if the parent is a user
     */
    public function testParentActorCampaignsAreNotReturned (): void
    {
        /** @var Actor $parent
         */
        $parent = Actor::factory()->create();

        /** @var Actor $actor */
        $actor = Actor::factory()->create()->moveTo($parent);
        $this->actingAs($actor);

        $campaignsCount = 3;
        Campaign::factory($campaignsCount)->create(["owner_id" => $parent->id]);

        $response = $this->json("GET", "/v1/campaigns");
        $response->assertOk()
                 ->assertJsonCount(0);
    }
}
