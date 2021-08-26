<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ListOdooPropertiesCommand.php
 */

namespace Neo\Console\Commands\Test;

use Illuminate\Console\Command;
use Neo\Services\API\Odoo\Client;
use Neo\Services\Odoo\Models\Property;
use Neo\Services\Odoo\OdooConfig;

class ListOdooPropertiesCommand extends Command {
    protected $signature = 'test:list-odoo-properties';

    protected $description = '[TEST] List Odoo properties';

    public function handle() {
        $client = OdooConfig::fromConfig()->getClient();

        $properties = Property::all($client);

        /** @var Property $property */
        foreach ($properties as $property) {
            $this->output->writeln($property->name . ", " . implode(",", $property->rental_product_ids));
        }
    }
}
