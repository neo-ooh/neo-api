<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <Valentin Dufois>
 *
 * @neo/api - $file.filePath
 */

namespace Tests\Feature\Roles;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Mail;
use Neo\Enums\Capability;
use Neo\Models\Actor;
use Tests\TestCase;

class ListRolesTest extends TestCase {
    use DatabaseTransactions;

    public function setUp (): void {
        parent::setUp();

        Mail::Fake();
    }

    /**
     * Asserts route is forbidden to guests
     *
     * @return void
     */
    public function testGuestsAreProhibited (): void {
        $response = $this->json('GET', '/v1/roles');
        $response->assertUnauthorized();
    }

    /**
     * Asserts route is secured with proper capability
     *
     * @return void
     */
    public function testRouteIsSecured (): void {
        $actor = Actor::factory()->create();
        $this->actingAs($actor);

        $response = $this->json('GET', '/v1/roles');
        $response->assertForbidden();
    }

    /**
     * Asserts user with proper capability can list roles
     *
     * @return void
     */
    public function testActorCanListRole (): void {
        $actor = Actor::factory()->create()->addCapability(Capability::roles_edit());
        $this->actingAs($actor);

        $response = $this->json('GET', '/v1/roles');
        $response->assertOk();
    }

    /**
     * Asserts role creation returns correct response
     *
     * @return void
     */
    public function testListRolesReturnsCorrectResponse (): void {
        $actor = Actor::factory()->create()->addCapability(Capability::roles_edit());
        $this->actingAs($actor);

        $response = $this->json('GET', '/v1/roles');
        $response->assertOk()
                 ->assertJsonStructure([
                     '*' => [
                         "id",
                         "name",
                         "desc",
                         "created_at",
                         "updated_at",
                     ],
                 ]);
    }
}
