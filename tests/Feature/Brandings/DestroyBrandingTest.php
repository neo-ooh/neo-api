<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - DestroyBrandingTest.php
 */

namespace Tests\Feature\Brandings;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Storage;
use Neo\Enums\Capability;
use Neo\Models\Actor;
use Neo\Models\Branding;
use Neo\Models\BrandingFile;
use Tests\TestCase;

class DestroyBrandingTest extends TestCase {
    use DatabaseTransactions;

    /**
     * Asserts guests cannot use this route
     *
     * @return void
     */
    public function testGuestsCannotUseThisRoute (): void {
        $branding = Branding::factory()->create();

        $response = $this->json('DELETE', '/v1/brandings/' . $branding->id);
        $response->assertUnauthorized();
    }

    /**
     * Asserts user without proper capability cannot use this route
     */
    public function testActorWithoutProperCapabilityCannotUseThisRoute (): void {
        $actor = Actor::factory()->create();
        $this->actingAs($actor);

        $branding = Branding::factory()->create();

        $response = $this->json('DELETE', '/v1/brandings/' . $branding->id);
        $response->assertForbidden();
    }

    /**
     * Asserts user with proper capability can use this route
     */
    public function testActorWithProperCapabilityCanUseThisRoute (): void {
        Storage::fake('public');

        $actor = Actor::factory()->create()->addCapability(Capability::brandings_edit());
        $this->actingAs($actor);

        $branding = Branding::factory()->create();
        BrandingFile::factory()->create([ "branding_id" => $branding->id ]);

        $response = $this->json('DELETE', '/v1/brandings/' . $branding->id);
        $response->assertOk();
    }

    /**
     * Asserts correct error on bad request
     */
    public function testCorrectResponseOnBadRequest (): void {
        $actor = Actor::factory()->create()->addCapability(Capability::brandings_edit());
        $this->actingAs($actor);

        Branding::factory()->create();

        $response = $this->json('PUT', '/v1/brandings/0');

        $response->assertStatus(404);
    }
}
