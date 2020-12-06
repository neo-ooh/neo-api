<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <Valentin Dufois>
 *
 * @neo/api - PasswordResetTest.php
 */

namespace Tests\Feature\PasswordRecovery;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Neo\Models\Actor;
use Tests\TestCase;

class PasswordResetTest extends TestCase {
    use DatabaseTransactions;
    public function setUp(): void {
        parent::setUp();

        Mail::fake();
    }

    /**
     * An error is returned when the recovery token is missing from the url
     *
     * @return void
     */
    public function testCorrectErrorOnMissingRecoveryToken(): void {
        $response = $this->postJson("/v1/auth/recovery/reset-password", [
            "password" => "foobar",
            "password_confirmation" => "foobar",
        ]);

        $response->assertStatus(422);
    }

    /**
     * An error is returned when the new password is missing from the request
     *
     * @return void
     */
    public function testCorrectErrorOnMissingNewPassword(): void {
        $response = $this->postJson("/v1/auth/recovery/reset-password?token=foobarfoobarfoobarfoobarfoobarfo");

        $response->assertStatus(422);
    }

    /**
     * An error is returned when the new password and its confirmation do not match
     *
     * @return void
     */
    public function testCorrectErrorOnNonMatchingPassword(): void {
        $response = $this->postJson("/v1/auth/recovery/reset-password?token=foobarfoobarfoobarfoobarfoobarfo", [
            "password" => "foobar",
            "password_confirmation" => "barfoo",
        ]);

        $response->assertStatus(422);
    }

    /**
     * An error is returned when the given token is erroneous
     *
     * @return void
     */
    public function testCorrectErrorOnBadToken(): void {
        $response = $this->postJson("/v1/auth/recovery/reset-password?token=foobarfoobarfoobarfoobarfoobarfo", [
            "password" => "foobar",
            "password_confirmation" => "foobar",
        ]);

        $response->assertStatus(400)
                 ->assertJson([
                     "code" => "recovery.bad-token",
                 ]);
    }

    /**
     * The password gets updated properly when the token is valid and the passwords match
     *
     * @return void
     */
    public function testPasswordUpdatesCorrectly(): void {
        // Get a user
        /** @var Actor $actor */
        $actor = Actor::factory()->create();

        // Asks for a recovery token
        $response = $this->postJson("/v1/auth/recovery/recover-password", [
            "email" => $actor->email,
        ]);

        $response->assertOk();

        // Use the token fom the database (Actor would find it in its emails)
        $response = $this->postJson("/v1/auth/recovery/reset-password", [
            "token" => $actor->recoveryToken->token,
            "password" => "foobar",
            "password_confirmation" => "foobar",
        ]);

        // Confirm the recovery returns a success status
        $response->assertOk();
        $actor->refresh();

        // Confirm the update took place in the database
        self::assertTrue(Hash::check("foobar", $actor->password), "User\'s updated password hash should match `foobar`");

        $actor->delete();
    }
}
