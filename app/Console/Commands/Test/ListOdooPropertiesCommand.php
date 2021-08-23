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
use Neo\Services\Broadcast\Odoo\Models\Property;

class ListOdooPropertiesCommand extends Command {
    protected $signature = 'test:list-odoo-properties';

    protected $description = '[TEST] List Odoo properties';

    public function handle() {
        $basepath     = "https://odoo.neo-ooh.com/xmlrpc/2";
        $userEmail    = "vdufois@neo-ooh.com";
        $userPassword = "WhPa24FZeT";

        $client = new Client($basepath, "production", $userEmail, $userPassword);


        $properties = Property::all($client);

        foreach ($properties as $property) {
            $this->output->writeln($property->name);
        }
    }
}
