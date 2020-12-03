<?php

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
        $response->assertStatus(422);
    }

    public function testActorCanAcceptTermsOfService (): void {
        /** @var Actor $actor */
        $actor = Actor::factory()->create();
        $this->actingAs($actor);

        $response = $this->json("POST", "/v1/auth/terms-of-service", [
            "accept" => true,
        ]);
        $response->assertOk();
    }
}
