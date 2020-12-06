<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <Valentin Dufois>
 *
 * @neo/api - StoreLibraryTest.php
 */

namespace Tests\Feature\Libraries;


use Illuminate\Foundation\Testing\DatabaseTransactions;
use Neo\Enums\Capability;
use Neo\Models\Actor;
use Tests\TestCase;

class StoreLibraryTest extends TestCase {
    use DatabaseTransactions;

    /**
     * Asserts guests cannot call this route
     *
     * @return void
     */
    public function testGuestsCannotCallThisRoute (): void
    {
        $response = $this->json('POST', '/v1/libraries');
        $response->assertUnauthorized();
    }

    /**
     * Asserts user without proper capability cannot call this route
     */
    public function testActorWithoutProperCapabilityCannotCallThisRoute (): void
    {
        $actor = Actor::factory()->create();
        $this->actingAs($actor);

        $response = $this->json('POST', '/v1/libraries');
        $response->assertForbidden();
    }

    /**
     * Asserts user with proper capability can call this route
     */
    public function testActorWithProperCapabilityCanCallThisRoute (): void
    {
        $actor = Actor::factory()->create()->addCapability(Capability::libraries_create());
        $this->actingAs($actor);

        $response = $this->json('POST',
            '/v1/libraries',
            [
                "name"          => "test-library",
                "owner_id"      => $actor->id,
                "capacity" => 10,
            ]);
        $response->assertCreated();
    }

    /**
     * Asserts correct error is returned on bad request
     */
    public function testCorrectErrorIsReturnedOnBadRequest (): void
    {
        $actor = Actor::factory()->create()->addCapability(Capability::libraries_create());
        $this->actingAs($actor);

        $response = $this->json('POST', '/v1/libraries', []);
        $response->assertStatus(422)
                 ->assertJsonValidationErrors(["name", "owner_id"]);
    }

    /**
     * Asserts correct response is returned on good request
     */
    public function testCorrectResponseIsReturnedOnGoodRequest (): void
    {
        $actor = Actor::factory()->create()->addCapability(Capability::libraries_create());
        $this->actingAs($actor);

        $response = $this->json('POST',
            '/v1/libraries',
            [
                "name"          => "test-library",
                "owner_id"      => $actor->id,
                "capacity" => 10,
            ]);
        $response->assertCreated()
                 ->assertJsonStructure([
                     "id",
                     "owner_id",
                     "owner" => [
                         "id",
                         "name",
                     ],
                     "name",
                     "content_limit",
                 ]);
    }

    /**
     * Asserts user cannot create a library with an inaccessible parent
     */
    public function testActorCannotCreateLibraryWithInaccessibleParent (): void
    {
        $actor = Actor::factory()->create()->addCapability(Capability::libraries_create());
        $this->actingAs($actor);

        $otherActor = Actor::factory()->create();

        $response = $this->json('POST',
            '/v1/libraries',
            [
                "name"     => "test-library",
                "owner_id" => $otherActor->id,
            ]);
        $response->assertStatus(422)
                 ->assertJsonValidationErrors(["owner_id"]);
    }
}
