<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - PushPropertyTrafficCommand.php
 */

namespace Neo\Console\Commands\Properties;

use Illuminate\Console\Command;
use Neo\Jobs\Traffic\PushPropertyTrafficJob;

class PushPropertyTrafficCommand extends Command {
    protected $signature = 'property:push-traffic {property}';

    protected $description = 'Push weekly traffic of the specified property towards Odoo';

    public function handle() {
        $propertyId = $this->argument("property");

        PushPropertyTrafficJob::dispatchSync($propertyId);
    }
}
