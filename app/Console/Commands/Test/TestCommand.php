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
use Neo\Jobs\RefreshContractReservations;

class TestCommand extends Command {
    protected $signature = 'test:test';

    protected $description = 'Internal tests';

    public function handle() {
//        /** @var Contract $contract */
//        $contract = Contract::query()
//                            ->find(728)
//                            ->append(["expected_impressions", "received_impressions"]);
//
//        dump($contract->toArray());

        RefreshContractReservations::dispatchSync(725);
    }
}
