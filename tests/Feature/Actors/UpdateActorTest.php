<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - UpdateActorTest.php
 */

namespace Tests\Feature\Actors;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Mail;
use Neo\Enums\Capability;
use Neo\Models\Actor;
use Neo\Models\Branding;
use Tests\TestCase;

class UpdateActorTest extends TestCase {
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
    public function testGuestAreProhibited (): void {
        /** @var Actor $actor */
        $actor = Actor::factory()->create();

        $response = $this->json("PUT", "/v1/actors/{$actor->id}");
        $response->assertUnauthorized();
    }

    /**
     * It is not possible to update another user without proper capability
     *
     * @return void
     */
    public function testActorCannotEditWithoutProperCapability (): void {
        // This user has no capabilities
        /** @var Actor $actor */
        $actor = Actor::factory()->create();
        $this->actingAs($actor);

        // This user is a descendant of our current one
        $newActor = Actor::factory()->create()->moveTo($actor);

        // Fails because user is not authorized to create other users
        $response = $this->json("PUT", "/v1/actors/{$newActor->id}", [
            'name'        => $newActor->name,
            'email'       => $newActor->email,
            'parent_id'   => $actor->id,
            'branding_id' => null,
        ]);
        $response->assertForbidden();
    }

    public function testActorCanOnlyUpdateRelatedActors (): void {
        /** @var Actor $actor */
        $actor = Actor::factory()->create();
        $actor->addCapability(Capability::actors_edit());
        $this->actingAs($actor);

        /** @var Actor $newActor */
        $newActor = Actor::factory()->create();
        $requestBody = [
            "name"        => $newActor->name,
            "email"       => $newActor->email,
            "parent_id"   => $actor->id,
            "branding_id" => null,
            "is_locked"   => false,
        ];

        // Fails -> newActor is not a child
        $response = $this->json("PUT", "/v1/actors/{$newActor->id}", $requestBody);
        $response->assertForbidden();

        $newActor->moveTo($actor);

        $actor->unsetRelations();
        $actor->refresh();

        // Succeeds -> newActor is a child
        $response = $this->json("PUT", "/v1/actors/{$newActor->id}", $requestBody);
        $response->assertOk();
    }

    public function testActorCanUpdateItself (): void {
        /** @var Actor $actor */
        $actor = Actor::factory()->create();
        $this->actingAs($actor);

        /** @var Actor $newActor */
        $newActor = Actor::factory()->make();
        $response = $this->json("PUT", "/v1/actors/{$actor->id}", [
            "name"        => $newActor->name,
            "email"       => $newActor->email,
            "branding_id" => null,
            "is_locked"   => false,
        ]);

        $response->assertOk();
    }

    public function testCorrectErrorOnBadRequest (): void {
        /** @var Actor $actor */
        $actor = Actor::factory()->create();
        $actor->addCapability(Capability::actors_edit());
        $this->actingAs($actor);

        /** @var Actor $newActor */
        $newActor = Actor::factory()->create()->moveTo($actor);

        // Fails -> bad body
        $response = $this->json("PUT", "/v1/actors/{$newActor->id}", []);
        $response->assertStatus(422);
    }

    public function testCorrectResponseOnSuccess (): void {
        /** @var Actor $actor */
        $actor = Actor::factory()->create();
        $actor->addCapability(Capability::actors_edit());
        $this->actingAs($actor);

        /** @var Actor $newActor */
        $newActor = Actor::factory()->create()->moveTo($actor);

        // Fails -> bad body
        $response = $this->json("PUT", "/v1/actors/{$newActor->id}", [
            "name"        => $newActor->name,
            "email"       => $newActor->email,
            "password"    => "foobar",
            "parent_id"   => $actor->id,
            "branding_id" => null,
            "is_locked"   => false,
        ]);

        $response->assertOk()
                 ->assertJsonStructure([
                     "id",
                     "name",
                     "email",
                     "parent",
                     "branding_id",
                     "is_locked",
                 ]);
    }

    public function testCorrectErrorOnHierarchicalLoop (): void {
        /** @var Actor $actor */
        $actor = Actor::factory()->create()->addCapability(Capability::actors_edit());
        $this->actingAs($actor);

        /** @var Actor $aActor */
        $aActor = Actor::factory()->create()->moveTo($actor);
        /** @var Actor $bActor */
        $bActor = Actor::factory()->create()->moveTo($aActor);

        // Fails -> bad body
        $response = $this->json("PUT", "/v1/actors/{$aActor->id}", [
            "name"        => $aActor->name,
            "email"       => $aActor->email,
            "is_locked"   => false,
            "parent_id"   => $bActor->id, // <- Incest here
            "branding_id" => null,
        ]);

        $response->assertForbidden()
                 ->assertJsonStructure([ "code", "message" ]);
    }

    public function testActorCannotBeItsOwnParent (): void {
        /** @var Actor $actor */
        $actor = Actor::factory()->create()->addCapability(Capability::actors_edit());
        $this->actingAs($actor);

        /** @var Actor $aActor */
        $aActor = Actor::factory()->create()->moveTo($actor);

        // Fails -> bad body
        $response = $this->json("PUT", "/v1/actors/{$aActor->id}", [
            "name"        => $aActor->name,
            "email"       => $aActor->email,
            "is_locked"   => false,
            "parent_id"   => $aActor->id, // <- Incest here
            "branding_id" => null,
        ]);

        $response->assertForbidden()
                 ->assertJsonStructure([ "code", "message" ]);
    }

    public function testActorParentCanBeChanged (): void {
        /** @var Actor $actor */
        $actor = Actor::factory()->create()->addCapability(Capability::actors_edit());
        $this->actingAs($actor);

        /** @var Actor $aActor */
        $aActor = Actor::factory()->create()->moveTo($actor);
        /** @var Actor $bActor */
        $bActor = Actor::factory()->create()->moveTo($actor);

        // Fails -> bad body
        $response = $this->json("PUT", "/v1/actors/{$aActor->id}", [
            "name"        => $aActor->name,
            "email"       => $aActor->email,
            "is_locked"   => false,
            "parent_id"   => $bActor->id,
            "branding_id" => null,
        ]);

        $response->assertOk()
                 ->assertJson([ "parent" => [
                     "id" => $bActor->id,
                 ] ]);
    }

    public function testActorCanBeLocked (): void {
        /** @var Actor $actor */
        $actor = Actor::factory()->create()->addCapability(Capability::actors_edit());
        $this->actingAs($actor);

        /** @var Actor $aActor */
        $aActor = Actor::factory()->create()->moveTo($actor);

        // Fails -> bad body
        $response = $this->json("PUT", "/v1/actors/{$aActor->id}", [
            "name"        => $aActor->name,
            "email"       => $aActor->email,
            "is_locked"   => true,
            "parent_id"   => $aActor->details->parent_id,
            "branding_id" => $aActor->branding_id,
        ]);

        $response->assertOk()
                 ->assertJson([
                     "is_locked" => true,
                     "locked_by" => [
                         "id" => $actor->id,
                     ],
                 ]);
    }

    public function testActorBrandingCanBeChanged (): void {
        /** @var Actor $actor */
        $actor = Actor::factory()->create()->addCapability(Capability::actors_edit());
        $this->actingAs($actor);

        /** @var Actor $aActor */
        $aActor = Actor::factory()->create()->moveTo($actor);
        /** @var Branding $branding */
        $branding = Branding::factory()->create();

        // Fails -> bad body
        $response = $this->json("PUT", "/v1/actors/{$aActor->id}", [
            "name"        => $aActor->name,
            "email"       => $aActor->email,
            "is_locked"   => false,
            "parent_id"   => $aActor->details->parent_id,
            "branding_id" => $branding->id,
        ]);

        $response->assertOk()
                 ->assertJson([
                     "branding_id" => $branding->id,
                 ]);
    }
}
