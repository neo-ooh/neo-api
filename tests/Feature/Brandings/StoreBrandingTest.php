<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <Valentin Dufois>
 *
 * @neo/api - StoreBrandingTest.php
 */

namespace Tests\Feature\Brandings;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Neo\Enums\Capability;
use Neo\Models\Actor;
use Neo\Models\Branding;
use Tests\TestCase;

class StoreBrandingTest extends TestCase {
    use DatabaseTransactions;

    /**
     * Asserts guests cannot use this route
     *
     * @return void
     */
    public function testGuestsCannotUseThisRoute (): void {
        $response = $this->json('POST', '/v1/brandings');
        $response->assertUnauthorized();
    }

    /**
     * Asserts user without proper capability cannot use this route
     */
    public function testActorWithoutProperCapabilityCannotUseThisRoute (): void {
        $actor = Actor::factory()->create();
        $this->actingAs($actor);

        $response = $this->json('POST', '/v1/brandings');
        $response->assertForbidden();
    }

    /**
     * Asserts user with proper capability can use this route
     */
    public function testActorWithProperCapabilityCanUseThisRoute (): void {
        $actor = Actor::factory()->create()->addCapability(Capability::brandings_edit());
        $this->actingAs($actor);

        $branding = Branding::factory()->make();

        $response = $this->json('POST',
            '/v1/brandings',
            [
                "name" => $branding->name,
            ]);

        $response->assertCreated();
    }

    /**
     * Asserts correct error on bad request
     */
    public function testCorrectResponseOnBadRequest (): void {
        $actor = Actor::factory()->create()->addCapability(Capability::brandings_edit());
        $this->actingAs($actor);

        Branding::factory()->make();

        $response = $this->json('POST', '/v1/brandings');

        $response->assertStatus(422)
                 ->assertJsonValidationErrors([ "name" ]);
    }

    /**
     * Asserts correct response on success
     */
    public function testCorrectResponseOnSuccess (): void {
        $actor = Actor::factory()->create()->addCapability(Capability::brandings_edit());
        $this->actingAs($actor);

        $branding = Branding::factory()->make();

        $response = $this->json('POST',
            '/v1/brandings',
            [
                "name" => $branding->name,
            ]);

        $response->assertCreated()
                 ->assertJsonStructure([
                     "id",
                     "name",
                 ]);
    }
}
