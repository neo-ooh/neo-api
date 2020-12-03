<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <Valentin Dufois>
 *
 * @neo/api - $file.filePath
 */

namespace Tests\Unit\ActorsCapabilities;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Mail;
use Neo\Enums\Capability;
use Neo\Models\Actor;
use Tests\TestCase;

class StoreDestroyActorCapabilitiesTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp (): void {
        parent::setUp();

        Mail::fake();
    }

    /**
     * A user is always created without any capabilities
     *
     * @return void
     */
    public function testActorIsCreatedWithNoCapabilities(): void {
        $actor = Actor::factory()->create();

        self::assertCount(0, $actor->standalone_capabilities);
    }

    /**
     * A user is always created without any capabilities
     *
     * @return void
     */
    public function testCapabilitiesCanBeAddedToActor(): void {
        $capability = Capability::actors_edit();
        $actor = Actor::factory()->create();
        $actor->addCapability($capability);

        self::assertCount(1, $actor->standalone_capabilities);

        self::assertEquals($capability->value, $actor->standalone_capabilities[0]->slug);
    }

    /**
     * A user is always created without any capabilities
     *
     * @return void
     */
    public function testCapabilitiesCanRevokedFromActor(): void {
        $capability = Capability::actors_edit();
        /** @var Actor $actor */
        $actor = Actor::factory()->create();
        $actor->addCapability($capability);

        self::assertCount(1, $actor->standalone_capabilities);

        $actor->revokeCapability($capability);

        self::assertCount(0, $actor->standalone_capabilities);
    }
}
