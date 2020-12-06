<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <Valentin Dufois>
 *
 * @neo/api - TermsOfServiceTest.php
 */

namespace Tests\Feature;

use Neo\Models\Actor;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class TermsOfServiceTest extends TestCase {
    use DatabaseTransactions;

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testTermsOfServiceCannotBeRetrievedLoggedOut (): void {
        $response = $this->json("GET", "/v1/auth/terms-of-service");
        $response->assertUnauthorized();
    }

    public function testTermsOfServiceCanBeRetrievedLoggedIn (): void {
        /** @var Actor $actor */
        $actor = Actor::factory()->create();
        $this->actingAs($actor);

        $response = $this->json("GET", "/v1/auth/terms-of-service");
        $response->assertOk()
                 ->assertJsonStructure([
                     "url",
                 ]);
    }

    public function testCannotAcceptTermsOfServiceWhileLogout (): void {
        $response = $this->json("POST", "/v1/auth/terms-of-service", [
            "accept" => true,
        ]);

        $response->assertUnauthorized();
    }

    public function testCorrectErrorOnBadRequest (): void {
        /** @var Actor $actor */
        $actor = Actor::factory()->create();
        $this->actingAs($actor);

        $response = $this->json("POST", "/v1/auth/terms-of-service", []);
        $response->assertStatus(401);
    }

    public function testActorCanAcceptTermsOfService (): void {
        /** @var Actor $actor */
        $actor = Actor::factory()->create(["tos_accepted" => false]);
        $this->actingAs($actor);

        $response = $this->json("POST", "/v1/auth/terms-of-service", [
            "accept" => true,
        ]);
        $response->assertOk();
    }
}
