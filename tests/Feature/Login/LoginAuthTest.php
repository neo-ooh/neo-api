<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <Valentin Dufois>
 *
 * @neo/api - LoginAuthTest.php
 */

namespace Tests\Feature\Login;

use Exception;
use Firebase\JWT\JWT;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Mail;
use Neo\Models\Actor;
use Neo\Models\SignupToken;
use Tests\TestCase;

class LoginAuthTest extends TestCase {
    use DatabaseTransactions;

    public function setUp(): void {
        parent::setUp();

        // Make sure we don"t send emails to peoples
        Mail::fake();
    }

    /**
     * The login route is expected to return a 422:Unprocessable if one of the email or password
     * value is missing  from the request
     *
     * @return void
     */
    public function testCorrectErrorOnRequestWithBadArguments(): void {
        $response = $this->postJson("/v1/auth/login", [
            "foo" => "bar",
        ]);

        $response->assertStatus(422);
    }

    /**
     * The login route is expected to return a 403:??? if the provided email do not match any user
     *
     * @return void
     */
    public function testCorrectErrorOnBadEmail(): void {
        $response = $this->json("POST", "/v1/auth/login", [
            "email" => "foo@bar.com",
            "password" => "password",
        ]);

        $response->assertStatus(422)
                 ->assertJson([
                     "code" => "auth.bad-email",
                 ]);
    }

    /**
     * The login route is expected to return a 403:??? if the provided password is incorrect
     *
     * @return void
     */
    public function testCorrectErrorOnBadPassword(): void {
        /** @var Actor $actor */
        $actor = Actor::factory()->create();

        $response = $this->json("POST", "/v1/auth/login", [
            "email" => $actor->email,
            "password" => "123456",
        ]);

        $response->assertStatus(422)
                 ->assertJson([
                     "code" => "auth.bad-password",
                 ]);
    }

    /**
     * The login route is expected to return a JWT for the user on login
     *
     * @return void
     */
    public function testCorrectResponseOnLogin(): void {
        /** @var Actor $actor */
        $actor = Actor::factory()->create();

        $response = $this->json("POST", "/v1/auth/login", [
            "email" => $actor->email,
            "password" => "password",
        ]);

        $response->assertOk()
                 ->assertJsonStructure([
                     "token",
                     "tos_accepted",
                 ]);
    }

    /**
     * The returned JWT must hold the correct user ID and name
     *
     * @return void
     */
    public function testLoginTokenIsValid(): void {
        /** @var Actor $actor */
        $actor = Actor::factory()->create();

        $response = $this->json("POST", "/v1/auth/login", [
            "email" => $actor->email,
            "password" => "password",
        ]);

        $response->assertOk();
        $token = $response["token"];

        try {
            $data = (array)JWT::decode($token, config("auth.jwt_public_key"), ["RS256"]);
            self::assertEquals($actor->id, $data["uid"], "Returned JWT holds a different user ID than expected");
        } catch (Exception $exception) {
            self::assertTrue(false, "Returned JWT could not be decoded, and is thus invalid");
        }
    }

    public function testNewActorCannotLogIn(): void {
        /** @var Actor $actor */
        $actor = Actor::factory()->create();
        SignupToken::create(["actor_id" => $actor->id]);

        $response = $this->json("POST", "/v1/auth/login", [
            "email" => $actor->email,
            "password" => "password",
        ]);

        $response->assertStatus(422)
                 ->assertJson([
                     "code" => "auth.not-allowed",
                 ]);
    }

    public function testLockedActorCannotLogIn(): void {
        /** @var Actor $actor */
        $actor = Actor::factory()->create(["is_locked" => true]);

        $response = $this->json("POST", "/v1/auth/login", [
            "email" => $actor->email,
            "password" => "password",
        ]);

        $response->assertStatus(422)
                 ->assertJson([
                     "code" => "auth.not-allowed",
                 ]);
    }

    public function testGroupCannotLogIn(): void {
        /** @var Actor $group */
        $group = Actor::factory()->create(["is_group" => true]);

        $response = $this->json("POST", "/v1/auth/login", [
            "email" => $group->email,
            "password" => "password",
        ]);

        $response->assertStatus(422)
                 ->assertJson([
                     "code" => "auth.not-allowed",
                 ]);
    }
}
