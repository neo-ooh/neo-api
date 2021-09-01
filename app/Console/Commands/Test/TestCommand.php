<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - TestCommand.php
 */

namespace Neo\Console\Commands\Test;

use Illuminate\Console\Command;
use Neo\Models\Location;
use Neo\Models\Property;

class TestCommand extends Command {
    protected $signature = 'test:test';

    protected $description = 'Internal tests';

    public function handle() {
        $properties = Property::all();

        foreach ($properties as $property) {
            $property->network_id = Location::query()->whereHas("actor", function ($query) use ($property) {
                $query->where("id", "=", $property->actor_id);
            })
                                            ->get("network_id")
                                            ->pluck("network_id")
                                            ->first();

            $property->save();
        }
    }
}
