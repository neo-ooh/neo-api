<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - TestPropertyFillFromOdooCommand.php
 */

namespace Neo\Console\Commands;

use Illuminate\Console\Command;
use Neo\Models\Property;
use Neo\Services\Odoo\OdooConfig;

class TestPropertyFillFromOdooCommand extends Command {
    protected $signature = 'test:fill-property';

    protected $description = 'Command description';

    public function handle() {
        \Neo\Jobs\Odoo\SyncPropertyDataJob::dispatchSync(\Neo\Models\Odoo\Property::find(106), OdooConfig::fromConfig()->getClient());
    }
}
