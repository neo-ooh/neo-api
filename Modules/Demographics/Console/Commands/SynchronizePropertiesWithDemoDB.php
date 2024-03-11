<?php
/*
 * Copyright 2024 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - SynchronizePropertiesWithDemoDB.php
 */

namespace Neo\Modules\Demographics\Console\Commands;

use Illuminate\Console\Command;
use Neo\Modules\Demographics\Models\DemographicProperty;
use Neo\Modules\Properties\Models\Property;

class SynchronizePropertiesWithDemoDB extends Command {
    protected $signature = 'properties:synchronize-with-demodb';

    protected $description = 'Synchronize properties list with the demographics DB';

    public function handle(): void {
        // List all properties including the ones that have been deleted
        Property::query()->chunk(250, function ($properties) {
            $entries = [];

            foreach ($properties as $property) {
                $entries[] = [
                    "id" => $property->getKey(),
                    "is_archived" => !$property->is_sellable,
                    "name" => $property->actor->name,
                ];
            }

            DemographicProperty::query()->upsert(
                $entries,
                ["id"]
            );
        });
    }
}
