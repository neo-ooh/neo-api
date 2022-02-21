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
//        /** @var Contract $contract */
//        $contract = Contract::query()
//                            ->find(697)
//                            ->append(["expected_impressions", "received_impressions"]);
//
//        dump($contract->getReceivedImpressionsAttribute());

        /*Contract::query()
                ->where("salesperson_id", "=", 20)
                ->get()
                ->each(function (Contract $contract) {
                    ImportContractJob::dispatchSync($contract->id);
                    RefreshContractReservations::dispatchSync($contract->id);
                });*/

        Contract::query()
                ->where("salesperson_id", "=", 20)
                ->has("flights", "=", 0)
                ->delete();
    }
}
