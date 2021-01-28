<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <Valentin Dufois>
 *
 * @neo/api - StoreCampaignTest.php
 */

namespace Tests\Feature\Campaigns;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Date;
use Neo\Enums\Capability;
use Neo\Models\Actor;
use Neo\Models\Format;
use Tests\TestCase;

class StoreCampaignTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * Assert Guests cannot call this route
     */
    public function testGuestsAreProhibited(): void
    {
        $response = $this->json("POST", "/v1/campaigns");
        $response->assertUnauthorized();
    }

    /**
     * Assert user without proper capability cannot call this route
     */
    public function testActorCannotCallRouteWithoutProperCapability(): void
    {
        $actor = Actor::factory()->create();
        $this->actingAs($actor);

        $response = $this->json("POST", "/v1/campaigns", []);
        $response->assertForbidden();
    }

    /**
     * Assert user with correct capability can call this route
     */
    public function testActorWithProperCapabilityCanCallThisRoute(): void
    {
        $actor = Actor::factory()->create()->addCapability(Capability::campaigns_edit());
        $this->actingAs($actor);

        $response = $this->json("POST", "/v1/campaigns", []);
        $response->assertStatus(422);
    }

    /**
     * Assert correct response on correct request
     */
    public function testCorrectResponseOnValidRequest(): void
    {
        $actor = Actor::factory()->create()->addCapability(Capability::campaigns_edit());
        $this->actingAs($actor);

        /** @var Format $format */
        $format = Format::query()->first();

        $response = $this->json("POST",
            "/v1/campaigns",
            [
                "owner_id" => $actor->id,
                "format_id" => $format->id,
                "name" => "campaign-name",
                "display_duration" => 15,
                "content_limit" => 10,
                "start_date" => Date::now()->toIso8601String(),
                "end_date" => Date::now()->addDays(14)->toIso8601String(),
                "loop_saturation" => 1
            ]);
        $response->assertCreated()
                 ->assertJsonStructure([
                     "id",
                     "owner_id",
                     "format_id",
                     "name",
                     "display_duration",
                     "content_limit",
                     "start_date",
                     "end_date",
                     "loop_saturation"
                 ]);
    }

    /**
     * Assert correct errors on bad request
     */
    public function testCorrectErrorOnBadRequest(): void
    {
        $actor = Actor::factory()->create()->addCapability(Capability::campaigns_edit());
        $this->actingAs($actor);

        $response = $this->json("POST", "/v1/campaigns", []);
        $response->assertStatus(422)
                 ->assertJsonValidationErrors([
                     "owner_id",
                     "format_id",
                     "display_duration",
                     "content_limit",
                     "start_date",
                     "end_date",
                 ]);
    }

    /**
     * Assert Actor can create a campaign with accessible owner
     */
    public function testActorCanCreateCampaignWithAccessibleOwner(): void
    {
        $actor = Actor::factory()->create()->addCapability(Capability::campaigns_edit());
        $this->actingAs($actor);

        /** @var Format $format */
        $format = Format::query()->first();

        $otherActor = Actor::factory()->create()->moveTo($actor);

        $response = $this->json("POST",
            "/v1/campaigns",
            [
                "owner_id" => $otherActor->id,
                "format_id" => $format->id,
                "name" => "campaign-name",
                "display_duration" => 15,
                "content_limit" => 10,
                "start_date" => Date::now()->toIso8601String(),
                "end_date" => Date::now()->addDays(14)->toIso8601String(),
                "loop_saturation" => 1
            ]);
        $response->assertCreated()
                 ->assertJsonStructure([
                     "id",
                     "owner_id",
                     "format_id",
                     "name",
                     "display_duration",
                     "content_limit",
                     "start_date",
                     "end_date",
                     "loop_saturation",
                 ]);
    }

    /**
     * Assert Actor cannot create a campaign with inaccessible owner
     */

    public function testCannotCreateCampaignWithInaccessibleOwner(): void
    {
        /** @var Actor $actor */
        $actor = Actor::factory()->create()->addCapability(Capability::campaigns_edit());
        $this->actingAs($actor);

        /** @var Format $format */
        $format = Format::query()->first();

        /** @var Actor $otherActor */
        $otherActor = Actor::factory()->create();

        $response = $this->json("POST",
            "/v1/campaigns",
            [
                "owner_id" => $otherActor->id,
                "format_id" => $format->id,
                "name" => "campaign-name",
                "display_duration" => 15,
                "content_limit" => 10,
                "start_date" => Date::now()->toIso8601String(),
                "end_date" => Date::now()->addDays(14)->toIso8601String(),
            ]);
        $response->assertStatus(422)
                 ->assertJsonValidationErrors([
                     "owner_id",
                 ]);
    }
}
