<?php
//------------------------------------------------------------------------------
// Copyright 2020 (c) Neo-OOH - All Rights Reserved
// Unauthorized copying of this file, via any medium is strictly prohibited
// Proprietary and confidential
// Written by Valentin Dufois <Valentin Dufois>
//
// neo-auth - SignupTokenCheckTest.php
//------------------------------------------------------------------------------

namespace Tests\Feature\Welcome;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Neo\Models\Actor;
use Neo\Models\SignupToken;
use Psalm\Report\GithubActionsReport;
use Tests\TestCase;

class SignupTokenCheckTest extends TestCase {
    use DatabaseTransactions;

    /**
     * Signup token verification fails if no token is provided
     *
     * @return void
     */
    public function testVerificationFailsIfTokenIsMissing(): void {
        $response = $this->json('GET', '/v1/auth/welcome');

        $response->assertStatus(422);
    }

    /**
     * Signup token verification fails if provided token is bad
     *
     * @return void
     */
    public function testVerificationFailsIfBadToken(): void {
        $response = $this->json('GET', '/v1/auth/welcome', [
            "token" => "foobarfoobarfoobarfoobarfoobarfo",
        ]);

        $response->assertStatus(400)
                 ->assertJson([
                     "code" => "welcome.bad-token",
                 ]);
    }

    /**
     * Actor name and success response is returned on valid token
     *
     * @return void
     */
    public function testCorrectResponseOnValidToken(): void {
        /** @var Actor $actor */
        $actor = Actor::factory()->create();

        SignupToken::create(["actor_id" => $actor->id]);

        $response = $this->json('GET', '/v1/auth/welcome', [
            "token" => $actor->signupToken->token,
        ]);

        $response->assertOk()
                 ->assertExactJson([
                     "name" => $actor->name,
                 ]);
    }
}
