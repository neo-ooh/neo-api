<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <Valentin Dufois>
 *
 * @neo/api - UpdateBrandingTest.php
 */

namespace Tests\Feature\Brandings;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Neo\Enums\Capability;
use Neo\Models\Actor;
use Neo\Models\Branding;
use Tests\TestCase;

class UpdateBrandingTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * Asserts guests cannot use this route
     *
     * @return void
     */
    public function testGuestsCannotUseThisRoute(): void
    {
        $branding = Branding::factory()->create();

        $response = $this->json('PUT', '/v1/brandings/' . $branding->id);
        $response->assertUnauthorized();
    }

    /**
     * Asserts user without proper capability cannot use this route
     */
    public function testActorWithoutProperCapabilityCannotUseThisRoute(): void
    {
        $actor = Actor::factory()->create();
        $this->actingAs($actor);

        $branding = Branding::factory()->create();

        $response = $this->json('PUT', '/v1/brandings/' . $branding->id);
        $response->assertForbidden();
    }

    /**
     * Asserts user with proper capability can use this route
     */
    public function testActorWithProperCapabilityCanUseThisRoute(): void
    {
        $actor = Actor::factory()->create()->addCapability(Capability::brandings_edit());
        $this->actingAs($actor);

        $branding = Branding::factory()->create();
        $response = $this->json('PUT',
            '/v1/brandings/' . $branding->id,
            [
                "name" => $branding->name,
            ]);

        $response->assertOk();
    }

    /**
     * Asserts correct error on bad request
     */
    public function testCorrectResponseOnBadRequest(): void
    {
        $actor = Actor::factory()->create()->addCapability(Capability::brandings_edit());
        $this->actingAs($actor);

        $branding = Branding::factory()->create();

        $response = $this->json('PUT', '/v1/brandings/' . $branding->id);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(["name"]);
    }

    /**
     * Asserts correct response on success
     */
    public function testCorrectResponseOnSuccess(): void
    {
        $actor = Actor::factory()->create()->addCapability(Capability::brandings_edit());
        $this->actingAs($actor);

        $branding = Branding::factory()->create();

        $response = $this->json('PUT',
            '/v1/brandings/' . $branding->id,
            [
                "name" => $branding->name,
            ]);

        $response->assertOk()
                 ->assertJsonStructure([
                     "id",
                     "name",
                     "files" => [
                         "*" => [],
                     ],
                 ]);
    }
}
