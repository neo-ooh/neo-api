<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <Valentin Dufois>
 *
 * @neo/api - ListBrandingsTest.php
 */

namespace Tests\Feature\Brandings;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Neo\Enums\Capability;
use Neo\Models\Actor;
use Neo\Models\Branding;
use Neo\Models\BrandingFile;
use Tests\TestCase;

class ListBrandingsTest extends TestCase {
    use DatabaseTransactions;

    public function setUp (): void {
        parent::setUp();
        Mail::fake();

        Storage::fake('public');
    }

    /**
     * Asserts Brandings can not be retrieved by guests
     *
     * @return void
     */
    public function testBrandingsCanNotBeRetrievedByGuests (): void {
        $response = $this->json('GET', '/v1/brandings');
        $response->assertUnauthorized();
    }

    /**
     * Asserts actors without the proper capability cannot retrieve the brandings
     */
    public function testBrandingsCannotBeRetrievedWithoutProperCapability (): void {
        $actor = Actor::factory()->create();
        $this->actingAs($actor);

        $response = $this->json('GET', '/v1/brandings');
        $response->assertForbidden();
    }

    /**
     * Asserts user with the proper capability can list all brandings
     */
    public function testBrandingsCanBeRetrievedWithProperCapability (): void {
        $actor = Actor::factory()->create()->addCapability(Capability::brandings_edit());
        $this->actingAs($actor);

        $response = $this->json('GET', '/v1/brandings');
        $response->assertOk();
    }

    /**
     * Asserts response is in correct format and contains files
     */
    public function testRetrievedBrandingsAreInCorrectFormat (): void {
        $actor = Actor::factory()->create()->addCapability(Capability::brandings_edit());
        $this->actingAs($actor);

        $startingCount = Branding::count();

        $brandingsCount = 5;
        $filesCount = 5;

        Branding::factory($brandingsCount)
                ->create()
                ->each(fn (Branding $branding) => $branding->files()
                                                           ->createMany(BrandingFile::factory($filesCount)
                                                                                    ->make()
                                                                                    ->toArray())
                                                           ->make()
                                                           ->toArray());

        $response = $this->json('GET', '/v1/brandings');
        $response->assertOk()
                 ->assertJsonCount($startingCount + $brandingsCount)
                 ->assertJsonStructure([
                     "*" => [
                         "id",
                         "name",
                         "files" => [
                             "*" => [
                                 "id",
                                 "type",
                                 "filename",
                                 "original_name",
                             ],
                         ],
                     ],
                 ]);
    }
}
