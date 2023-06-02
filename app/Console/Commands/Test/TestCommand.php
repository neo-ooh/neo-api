<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - TestCommand.php
 */

namespace Neo\Console\Commands\Test;

use Illuminate\Console\Command;
use Neo\Enums\ActorType;
use Neo\Models\Actor;
use Neo\Models\Utils\ActorsGetter;

class TestCommand extends Command {
    protected $signature = 'test:test';

    protected $description = 'Internal tests';

    /**
     * @return void
     */
    public function handle() {
        $parentId = 1345;
        $typeId   = 10316;

        $actors = ActorsGetter::from($parentId)
                              ->selectChildren(true)
                              ->getActors()
                              ->filter(fn(Actor $actor) => $actor->type === ActorType::Property)
                              ->load("property");
        $actors->each(function (Actor $actor) use ($typeId) {
            $actor->property->type()->associate($typeId);
            $actor->property->save();
        });
    }
}
