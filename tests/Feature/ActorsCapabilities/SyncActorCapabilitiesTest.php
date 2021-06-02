<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - SyncActorCapabilitiesTest.php
 */

namespace Tests\Feature\ActorsCapabilities;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Mail;
use Neo\Enums\Capability as CapabilityEnum;
use Neo\Models\Actor;
use Neo\Models\Capability;
use Tests\TestCase;

class SyncActorCapabilitiesTest extends TestCase {
    use DatabaseTransactions;

    public function setUp (): void {
        parent::setUp();
        Mail::fake();
    }

    /**
     * Asserts guests cannot use this route
     *
     * @return void
     */
    public function testGuestsAreProhibited (): void {
        $response = $this->json('PUT', '/v1/actors/1/capabilities');
        $response->assertUnauthorized();
    }

    /**
     * Asserts users without the proper capability `actors.edit` cannot access this route
     *
     * @return void
     */
    public function testErrorOnActorWithoutProperCapability (): void {
        // This user has no capability
        $actor = Actor::factory()->create();
        $this->actingAs($actor);

        $response = $this->json('PUT', '/v1/actors/' . $actor->id . '/capabilities');
        $response->assertForbidden();
    }

    /**
     * Asserts a user can add capabilities to an accessible user
     *
     * @return void
     */
    public function testActorCanAddCapabilityToAccessibleActor (): void {
        $actor = Actor::factory()->create()->addCapability(CapabilityEnum::actors_edit());
        $this->actingAs($actor);

        $testCapability = Capability::query()->where("slug", "=", "test.capability")->first();
        $testCapability->standalone = true;
        $testCapability->save();

        $otherActor = Actor::factory()->create()->moveTo($actor);

        $response = $this->json('PUT',
            '/v1/actors/' . $otherActor->id . '/capabilities',
            [
                "capabilities" => [ $testCapability->id ],
            ]);
        $response->assertOk();
    }

    /**
     * Asserts user cannot add a capability to an unrelated user
     *
     * @return void
     */
    public function testActorCannotAddCapabilityToUnrelatedActor (): void {
        $actor = Actor::factory()->create()->addCapability(CapabilityEnum::actors_edit());
        $this->actingAs($actor);

        $testCapability = Capability::query()->where("slug", "=", "test.capability")->first();

        $otherActor = Actor::factory()->create();

        $response = $this->json('PUT',
            '/v1/actors/' . $otherActor->id . '/capabilities',
            [
                "capabilities" => [ $testCapability->id ],
            ]);
        $response->assertForbidden();
    }

    /**
     * Asserts user cannot add a not-standalone capability
     *
     * @return void
     */
    public function testActorCannotAddNonStandaloneCapability (): void {
        $actor = Actor::factory()->create()->addCapability(CapabilityEnum::actors_edit());
        $this->actingAs($actor);

        $testCapability = Capability::query()->where("slug", "=", "test.capability")->first();
        $testCapability->standalone = false;
        $testCapability->save();

        $otherActor = Actor::factory()->create()->moveTo($actor);

        $response = $this->json('PUT',
            '/v1/actors/' . $otherActor->id . '/capabilities',
            [
                "capabilities" => [ $testCapability->id ],
            ]);
        $response->assertForbidden();
    }

    /**
     * Asserts error on invalid capability
     *
     * @return void
     */
    public function testActorCannotAddInvalidCapability (): void {
        $actor = Actor::factory()->create()->addCapability(CapabilityEnum::actors_edit());
        $this->actingAs($actor);

        $otherActor = Actor::factory()->create()->moveTo($actor);

        $response = $this->json('PUT',
            '/v1/actors/' . $otherActor->id . '/capabilities',
            [
                "capabilities" => [ 999999 ],
            ]);
        $response->assertStatus(422);
    }

    /**
     * Asserts correct error on bad request
     *
     * @return void
     */
    public function testCorrectErrorOnBadRequest (): void {
        $actor = Actor::factory()->create()->addCapability(CapabilityEnum::actors_edit());
        $this->actingAs($actor);

        $otherActor = Actor::factory()->create()->moveTo($actor);

        $response = $this->json('PUT', '/v1/actors/' . $otherActor->id . '/capabilities', []);
        $response->assertStatus(422);
    }
}
