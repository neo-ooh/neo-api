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
use Neo\Models\Contract;

class TestCommand extends Command {
    protected $signature = 'test:test';

    protected $description = 'Internal tests';

    public function handle() {
//        $productId  = 23119; // Alexis Nihon - DV
//        $productId = 22075; // Alexis Nihon - DV
//        $start     = "2022-03-01";
//        $end       = "2022-03-14";
//
//        $odooConfig = OdooConfig::fromConfig();
//        $client     = $odooConfig->getClient();
//
//        $lines = OrderLine::all($client, [
//            ["product_id", "=", $productId], // Specific product
//            ["rental_start", "<=", $end], // when range overlaps
//            ["rental_end", ">=", $start],
//            ["state", "=", ["sent", "done"]], // Filter by contracts state
//        ]);
//
//        dump($lines->toArray());

//        MigrateContractsJob::dispatchSync();

        Contract::query()->where("contract_id", "=", "OTG-222-21")->first()->load(["flights"]);

    }
}
