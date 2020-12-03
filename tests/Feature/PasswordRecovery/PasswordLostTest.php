<?php
//------------------------------------------------------------------------------
// Copyright 2020 (c) Neo-OOH - All Rights Reserved
// Unauthorized copying of this file, via any medium is strictly prohibited
// Proprietary and confidential
// Written by Valentin Dufois <Valentin Dufois>
//
// neo-auth - PasswordLostTest.php
//------------------------------------------------------------------------------

namespace Tests\Feature\PasswordRecovery;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Mail;
use Neo\Mails\RecoverPasswordEmail;
use Neo\Models\Actor;
use Tests\TestCase;

class PasswordLostTest extends TestCase {
    use DatabaseTransactions;

    public function setUp(): void {
        parent::setUp();

        Mail::fake();
    }

    /**
     * Password recovery fails if no email is passed in the request body
     *
     * @return void
     */
    public function testRecoveryFailsOnMissingEmail(): void {
        $response = $this->postJson("/v1/auth/recovery/recover-password");

        $response->assertStatus(422);
    }

    /**
     * Password recovery fails if passed email is unrecognized
     *
     * @return void
     */
    public function testRecoveryFailsOnUnrecognizedEmail(): void {
        $response = $this->postJson("/v1/auth/recovery/recover-password", [
            "email" => "foo@bar.com",
        ]);

        $response->assertStatus(400)
                 ->assertJson([
                     "code" => "recovery.unknown-email",
                 ]);
    }

    /**
     * A locked user is not allowed to recevoer its password
     *
     * @return void
     */
    public function testLockedActorCannotRecoverPassword(): void {
        /** @var Actor $actor */
        $actor = Actor::factory()->create(["is_locked" => true]);

        $response = $this->postJson("/v1/auth/recovery/recover-password", [
            "email" => $actor->email,
        ]);

        $response->assertUnauthorized()
                 ->assertJson([
                     "code" => "recovery.unauthorized",
                 ]);
    }

    /**
     * A group user is not allowed to recover its password
     *
     * @return void
     */
    public function testGroupCannotRecoverPassword(): void {
        /** @var Actor $actor */
        $actor = Actor::factory()->create(["is_group" => true]);

        $response = $this->postJson("/v1/auth/recovery/recover-password", [
            "email" => $actor->email,
        ]);

        $response->assertUnauthorized()
                 ->assertJson([
                     "code" => "recovery.unauthorized",
                 ]);
    }

    /**
     * Password recovery returns success on correct email
     *
     * @return void
     */
    public function testReturnsSuccessOnCorrectEmail(): void {
        /** @var Actor $actor */
        $actor = Actor::factory()->create();

        $response = $this->postJson("/v1/auth/recovery/recover-password", [
            "email" => $actor->email,
        ]);

        $response->assertOk();
    }

    /**
     * Password recovery returns success on correct email
     *
     * @return void
     */
    public function testRecoverLinkSentOnSuccess(): void {
        /** @var Actor $actor */
        $actor = Actor::factory()->create();

        $this->postJson("/v1/auth/recovery/recover-password", [
            "email" => $actor->email,
        ]);

        Mail::assertSent(RecoverPasswordEmail::class);
    }
}
