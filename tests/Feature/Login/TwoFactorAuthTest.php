<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <Valentin Dufois>
 *
 * @neo/api - TwoFactorAuthTest.php
 */

namespace Tests\Feature\Login;

use Firebase\JWT\JWT;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Testing\TestResponse;
use Neo\Mails\TwoFactorTokenEmail;
use Neo\Models\Actor;
use PHPUnit\Framework\AssertionFailedError;
use Tests\TestCase;

class TwoFactorAuthTest extends TestCase {
    use DatabaseTransactions;

    /**
     * @var Actor
     */
    private Actor $actor;

    private TestResponse $loginResponse;

    public function setUp(): void {
        parent::setUp();

        // Make sure we don"t send emails to peoples
        Mail::fake();

        // Generate a user for the tests
        $this->actor = Actor::factory()->create();

        // And directly log it in
        $this->loginResponse = $this->postJson("/v1/auth/login", [
            "email"    => $this->actor->email,
            "password" => "password",
        ]);
    }

    /**
     * Make sure the Two Factor Token is properly emitted and refreshed on login
     *
     * @return void
     */
    public function testTwoFactorTokenIsGeneratedOnLogin(): void {
        $token = $this->actor->twoFactorToken;
        self::assertNotNull($token, "The 2FA token was not generated on login");

        self::assertGreaterThan($this->actor->last_login_at, $token->created_at,
            "The 2FA token was not regenerated on login. The available one is too old");
    }

    /**
     * Make sure the Two Factor Token is properly emitted and refreshed on login
     *
     * @return void
     */
    public function testTwoFactorTokenIsSentByMailOnCreation(): void {
        // Create and login our user
        /** @var Actor $actor */
        $actor = Actor::factory()->create();
        Auth::setUser(null);

        $this->loginResponse = $this->json("POST", "/v1/auth/login", [
            "email"    => $actor->email,
            "password" => "password",
        ]);
        $this->loginResponse->assertStatus(200);

        // Assert the 2fa email was sent
        Mail::assertSent(TwoFactorTokenEmail::class);
    }

    /**
     * Make sure the correct error status is returned on a missing header
     *
     * @return void
     */
    public function testCorrectErrorOnMissingTwoFactorToken(): void {
        $response = $this->postJson("/v1/auth/two-fa-validation");

        $response->assertStatus(401);
    }

    /**
     * Make sure the correct error message is returned when a bad code is sent
     *
     * @return void
     */
    public function testCorrectErrorOnBadTwoFactorToken(): void {
        $response = $this->withHeader("Authorization", "Bearer {$this->loginResponse["token"]}")
                         ->postJson("/v1/auth/two-fa-validation", [
                             "token" => "000000", // Bad token
                         ]);

        $response->assertForbidden()
                 ->assertJson([
                     "code" => "auth.bad-2fa-token",
                 ]);
    }

    /**
     * Make sure the correct status is returned when the correct two factor token is given
     *
     * @return void
     */
    public function testCorrectResponseOnGoodTwoFactorToken(): void {
        $response = $this->withHeader("Authorization", "Bearer {$this->loginResponse["token"]}")
                         ->postJson("/v1/auth/two-fa-validation", [
                             "token" => $this->actor->twoFactorToken->token, // Correct token
                         ]);

        $response->assertOk();

        $token = $response["token"];

        try {
            $data = (array)JWT::decode($token, config("auth.jwt_public_key"), ["RS256"]);
            self::assertEquals($this->actor->id, $data["uid"], "Returned JWT holds a different user ID than expected");
        } catch (AssertionFailedError $exception) {
            self::assertTrue(false, "Returned JWT could not be decoded, and is thus invalid");
        }
    }

    /**
     * Make sure the correct error is returned when we try to validate an already-validated token
     *
     * @return void
     */
    public function testCorrectErrorOnAlreadyValidatedToken(): void {
        $this->testCorrectResponseOnGoodTwoFactorToken();

        $response = $this->withHeader("Authorization", "Bearer {$this->loginResponse["token"]}")
                         ->postJson("/v1/auth/two-fa-validation", [
                             "token" => $this->actor->twoFactorToken->token, // Correct token
                         ]);

        $response->assertForbidden();
    }
}
