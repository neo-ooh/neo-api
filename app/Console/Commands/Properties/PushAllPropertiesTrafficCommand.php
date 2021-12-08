<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - PushAllPropertiesTrafficCommand.php
 */

namespace Neo\Console\Commands\Properties;

use Illuminate\Console\Command;
use Neo\Jobs\Traffic\PushPropertyTrafficJob;
use Neo\Models\Property;

class PushAllPropertiesTrafficCommand extends Command {
    protected $signature = 'properties:push-traffic';

    protected $description = 'Push the weekly traffic of all properties to Odoo';

    public function handle() {
        $properties = Property::query()->whereHas("odoo")->lazy(100);

        foreach ($properties as $property) {
            $this->getOutput()->write("Pushing traffic for property #{$property->getKey()} ...");

            PushPropertyTrafficJob::dispatch($property->getKey());

            $this->getOutput()->writeln(" Done!");
        }
    }
}
