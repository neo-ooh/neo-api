<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <Valentin Dufois>
 *
 * @neo/api - StoreActorTest.php
 */

namespace Tests\Feature\Actors;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Neo\Enums\Capability;
use Neo\Jobs\CreateSignupToken;
use Neo\Models\Actor;
use Tests\TestCase;

class StoreActorTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp (): void {
        parent::setUp();

        Mail::fake();
    }

    /**
     * It is not possible to query this route if we are not logged in
     *
     * @return void
     */
    public function testGuestAreProhibited(): void {
        $response = $this->json('POST', '/v1/actors');
        $response->assertUnauthorized();
    }

    /**
     * It is only possible to query the route with the proper capability
     *
     * @return void
     */
    public function testRouteIsSecured(): void {
        // This user has no capabilities
        $actor = Actor::factory()->create();
        $this->actingAs($actor);

        // This user will serve as a source for the new user
        $newActor = Actor::factory()->make();
        $requestBody = [
            "is_group" => false,
            "name" => $newActor->name,
            "email" => $newActor->email,
            "locale" => "fr",
            "enabled" => true,
            "parent_id" => $actor->id,
            "branding_id" => null,
            "roles" => [],
            "capabilities" => [],
            "make_library" => false
        ];

        // Fails because user is not authorized to create other users
        $response = $this->json('POST', '/v1/actors', $requestBody);
        $response->assertForbidden();

        $actor->addCapability(Capability::actors_create());

        // Succeeds
        $response = $this->json('POST', '/v1/actors', $requestBody);
        $response->assertStatus(201);
    }

    /**
     * It is not possible to create two users with the same email
     *
     * @return void
     */
    public function testCannotCreateActorWithExistingEmail(): void {
        $actor = Actor::factory()->create()->addCapability(Capability::actors_create());
        $this->actingAs($actor);

        // This user will serve as a source for the new user
        $newActor = Actor::factory()->make();
        $requestBody = [
            "is_group" => false,
            "name" => $newActor->name,
            "email" => $actor->email, // <- Already used email
            "enabled" => true,
            "parent_id" => $actor->id,
            "branding_id" => null,
            "roles" => [],
            "capabilities" => [],
            "make_library" => false
        ];

        // Succeeds
        $response = $this->json('POST', '/v1/actors', $requestBody);
        $response->assertStatus(422); // <- Laravel bad request code
    }

    /**
     * It is not possible to create a user with an invalid parent
     *
     * @return void
     */
    public function testCannotCreateActorWithBadParentID(): void {
        $actor = Actor::factory()->create()->addCapability(Capability::actors_create());
        $this->actingAs($actor);

        // This user will serve as a source for the new user
        $newActor = Actor::factory()->make();
        $requestBody = [
            "is_group" => false,
            "name" => $newActor->name,
            "email" => $newActor->email,
            "enabled" => true,
            "parent_id" => -1, // <- Bad ID
            "branding_id" => null,
            "roles" => [],
            "capabilities" => [],
            "make_library" => false
        ];

        // Succeeds
        $response = $this->json('POST', '/v1/actors', $requestBody);
        $response->assertStatus(422); // <- Laravel bad request code
    }

    /**
     * It is not possible to create a user with a bad role
     *
     * @return void
     */
    public function testCannotCreateActorWithBadRole(): void {
        $actor = Actor::factory()->create()->addCapability(Capability::actors_create());
        $this->actingAs($actor);

        // This user will serve as a source for the new user
        $newActor = Actor::factory()->make();
        $requestBody = [
            "is_group" => false,
            "name" => $newActor->name,
            "email" => $newActor->email,
            "enabled" => true,
            "parent_id" => $actor->id,
            "branding_id" => null,
            "roles" => [-1],
            "capabilities" => [],
            "make_library" => false
        ];

        // Succeeds
        $response = $this->json('POST', '/v1/actors', $requestBody);
        $response->assertStatus(422); // <- Laravel bad request code
    }

    /**
     * It is not possible to create a user with non-existant capabilities
     *
     * @return void
     */
    public function testCannotCreateActorWithBadCapabilities(): void {
        $actor = Actor::factory()->create()->addCapability(Capability::actors_create());
        $this->actingAs($actor);

        // This user will serve as a source for the new user
        $newActor = Actor::factory()->make();
        $requestBody = [
            "is_group" => false,
            "name" => $newActor->name,
            "email" => $newActor->email,
            "enabled" => true,
            "parent_id" => $actor->id,
            "branding_id" => null,
            "roles" => [],
            "capabilities" => [-1],
            "make_library" => false
        ];

        // Succeeds
        $response = $this->json('POST', '/v1/actors', $requestBody);
        $response->assertStatus(422); // <- Laravel bad request code
    }

    /**
     * Response on successful creation is valid
     *
     * @return void
     */
    public function testCorrectResponseOnSuccessfulCreation(): void {
        $actor = Actor::factory()->create()->addCapability(Capability::actors_create());
        $this->actingAs($actor);

        // This user will serve as a source for the new user
        $newActor = Actor::factory()->make();
        $requestBody = [
            "is_group" => false,
            "name" => $newActor->name,
            "email" => $newActor->email,
            "locale" => "fr",
            "enabled" => true,
            "parent_id" => $actor->id,
            "branding_id" => null,
            "roles" => [],
            "capabilities" => [],
            "make_library" => false
        ];

        // Succeeds
        $response = $this->json('POST', '/v1/actors', $requestBody);
        $response->assertStatus(201)
                 ->assertJsonStructure([
                     "id",
                     "parent",
                     "email",
                     "name",
                     "locale",
                     "parent",
                     "branding_id",
                 ]);
    }

    /**
     * Newly created users must receive an email with a signupToken
     *
     * @return void
     */
    public function testEmailSentOnActorCreation(): void {
        Queue::fake();

        $actor = Actor::factory()->create()->addCapability(Capability::actors_create());
        $this->actingAs($actor);

        // This user will serve as a source for the new user
        $newActor = Actor::factory()->make();
        $requestBody = [
            "is_group" => false,
            "name" => $newActor->name,
            "email" => $newActor->email,
            "locale" => "fr",
            "enabled" => true,
            "parent_id" => $actor->id, // <- Bad ID
            "branding_id" => null,
            "roles" => [],
            "capabilities" => [],
            "make_library" => false
        ];

        // Succeeds
        $response = $this->json('POST', '/v1/actors', $requestBody);
        $response->assertStatus(201);

        // Has the email been sent ?
        Queue::assertPushed(CreateSignupToken::class);
    }


}
