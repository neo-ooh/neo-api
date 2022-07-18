<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - UpdateScheduleTest.php
 */

namespace Tests\Feature\Schedules;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Date;
use Neo\Enums\Capability;
use Neo\Models\Actor;
use Neo\Modules\Broadcast\Models\Campaign;
use Neo\Modules\Broadcast\Models\Content;
use Neo\Modules\Broadcast\Models\Library;
use Neo\Modules\Broadcast\Models\Schedule;
use Tests\TestCase;

class UpdateScheduleTest extends TestCase {
    use DatabaseTransactions;

    protected Actor $actor;
    protected Library $library;
    protected Content $content;
    protected Campaign $campaign;
    protected Schedule $schedule;

    public function setUp(): void {
        parent::setUp();

        $this->actor    = Actor::factory()->create();
        $this->library  = Library::factory()->create(["owner_id" => $this->actor->id]);
        $this->content  = Content::factory()->create(["owner_id"   => $this->actor->id,
                                                      "library_id" => $this->library->id]);
        $this->campaign = Campaign::factory()->create(["owner_id" => $this->actor->id]);
        $this->schedule = Schedule::query()->create([
            "owner_id"    => $this->actor->id,
            "campaign_id" => $this->campaign->id,
            "content_id"  => $this->content->id,
            "start_date"  => Date::now(),
            "end_date"    => $this->campaign->end_date,
        ]);
        $this->schedule->refresh();
    }

    /**
     * Assert guests cannot call this route
     */
    public function testGuestsAreForbidden(): void {
        $response = $this->json("PUT", "/v1/schedules/" . $this->schedule->id);
        $response->assertUnauthorized();
    }

    /**
     * Assert only users with the proper capability can call this route
     */
    public function testOnlyActorsWithProperCapabilityCanCallThisRoute(): void {
        $this->actingAs($this->actor);
        $response = $this->json("PUT", "/v1/schedules/" . $this->schedule->id);
        $response->assertForbidden();

        $this->actor->addCapability(Capability::contents_schedule());
        $response = $this->json("PUT", "/v1/schedules/" . $this->schedule->id);
        $response->assertStatus(422);
    }

    /**
     * Assert correct response on correct request
     */
    public function testCorrectResponseOnCorrectRequest(): void {
        $this->actor->addCapability(Capability::contents_schedule());
        $this->actingAs($this->actor);
        $response = $this->json("PUT",
            "/v1/schedules/" . $this->schedule->id,
            [
                "start_date" => $this->campaign->start_date,
                "end_date"   => $this->campaign->end_date,
            ]);
        $response->assertOk();
    }

    /**
     * Assert it is not possible to set a start date earlier than the campaign start date
     */
    //    public function testCannotStartScheduleBeforeCampaign()
    //    {
    //        $this->user->addCapability(Capability::contents_schedule());
    //        $this->actingAs($this->user);
    //        $response = $this->json("PUT", "/v1/schedules/".$this->schedule->id, [
    //            "start_date" => \Carbon\Traits\Date::$this->campaign->start_date,
    //            "end_date" => $this->campaign->end_date,
    //        ]);
    //        $response->assertOk()
    //                 ->assertJson([
    //                     $this->content->attributesToArray()
    //                 ]);
    //    }
    /**
     * Assert it is not possible to set an end date later than the campaign end date
     */
}
