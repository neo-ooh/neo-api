<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <Valentin Dufois>
 *
 * @neo/api - $file.filePath
 */

namespace Tests\Feature\ActorsCapabilities;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Mail;
use Neo\Enums\Capability;
use Neo\Models\Actor;
use Tests\TestCase;

class ListActorCapabilitiesTest extends TestCase {
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
        $response = $this->json('GET', '/v1/actors/1/capabilities');
        $response->assertUnauthorized();
    }

    /**
     * Asserts users without the proper capability `actors.edit` cannot access this route
     *
     * @return void
     */
    public function testErrorOnActorWithoutProperCapability (): void {
        // This actor has no capability
        $actor = Actor::factory()->create();
        $this->actingAs($actor);

        $otherActor = Actor::factory()->create()->moveTo($actor);

        $response = $this->json('GET', '/v1/actors/' . $otherActor->id . '/capabilities');
        $response->assertForbidden();
    }

    /**
     * Asserts a user can list the capabilities of an accessible user
     *
     * @return void
     */
    public function testActorCanListCapabilitiesOfAccessibleActor (): void {
        $actor = Actor::factory()->create()->addCapability(Capability::actors_edit());
        $this->actingAs($actor);

        $otherActor = Actor::factory()->create()->moveTo($actor);

        $response = $this->json('GET', '/v1/actors/' . $otherActor->id . '/capabilities');
        $response->assertOk();
    }

    /**
     * Asserts a user can list its own capabilities
     *
     * @return void
     */
    public function testActorCanListItsOwnCapabilities (): void {
        $actor = Actor::factory()->create();
        $this->actingAs($actor);

        $response = $this->json('GET', '/v1/actors/' . $actor->id . '/capabilities');
        $response->assertOk();
    }

    /**
     * Asserts user cannot list capabilities of an unrelated user
     *
     * @return void
     */
    public function testActorCannotListCapabilitiesOfUnrelatedActor (): void {
        $actor = Actor::factory()->create()->addCapability(Capability::actors_edit());
        $this->actingAs($actor);

        $otherActor = Actor::factory()->create();

        $response = $this->json('GET', '/v1/actors/' . $otherActor->id . '/capabilities');
        $response->assertForbidden();
    }
}
