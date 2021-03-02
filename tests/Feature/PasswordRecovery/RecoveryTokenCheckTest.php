<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <Valentin Dufois>
 *
 * @neo/api - RecoveryTokenCheckTest.php
 */

namespace Tests\Feature\PasswordRecovery;

use Neo\Models\Actor;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Neo\Models\RecoveryToken;
use Tests\TestCase;

class RecoveryTokenCheckTest extends TestCase {
    use DatabaseTransactions;

    /**
     * Token verification fails if no token is provided
     *
     * @return void
     */
    public function testVerificationFailsIfTokenMissing (): void {
        $response = $this->json("POST", "/v1/auth/recovery/check-token");

        $response->assertStatus(422);
    }

    /**
     * Token verification returns correct error on bad token
     *
     * @return void
     */
    public function testCorrectErrorIsReturnedOnBadToken (): void {
        $response = $this->json("POST", "/v1/auth/recovery/check-token", [
                "token" => "foobarfoobarfoobarfoobarfoobarfo",
            ]);

        $response->assertStatus(400)
                 ->assertJson([
                     "code" => "recovery.bad-token",
                 ]);
    }

    /**
     * Token verification returns correct infos on good token
     *
     * @return void
     */
    public function testCorrectResponseIsReturnedOnGoodToken (): void {
        /** @var Actor $actor */
        $actor = Actor::factory()->create();
        RecoveryToken::create(["actor" => $actor, "email" => $actor->email]);

        $response = $this->json("POST", "/v1/auth/recovery/check-token",
            [
                "token" => $actor->recoveryToken->token,
            ]);

        $response->assertOk()
                 ->assertExactJson([
                     "name" => $actor->name,
                 ]);
    }
}
