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
use Neo\Services\Odoo\Models\Contract;
use Neo\Services\Odoo\OdooConfig;

class TestCommand extends Command {
    protected $signature = 'test:test';

    protected $description = 'Internal tests';

    /**
     */
    public function handle() {
//        $inventory = InventoryProvider::find(1);
//        $odoo      = InventoryAdapterFactory::make($inventory);

//        /** @var OdooClient $client */
        $client = OdooConfig::fromConfig()->getClient();

//        dump(Contract::get($client, 3042)->toArray());
        $time_start = microtime(true);
        dump($client->client->call(Contract::$slug, "action_del_lines", [[3042]])->toArray());

        $time_end       = microtime(true);
        $execution_time = ($time_end - $time_start) / 60;
        dump($execution_time);

//        $product = Product::search($odoo->getConfig()->getClient(), [
//            ["default_code", "=", "Production"],
//        ])->toArray();
//
//        dump($product);
    }
}
