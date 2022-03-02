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
use Neo\Jobs\Contracts\ImportContractDataJob;
use Neo\Jobs\Contracts\ImportContractReservations;
use Neo\Models\Contract;
use Ripcord_TransportException;

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

        Contract::query()
                ->where("id", "=", 329)
//                ->where("salesperson_id", "=", 23)
                ->get()
                ->each(function (Contract $contract) {
                    try {
                        ImportContractDataJob::dispatchSync($contract->id);
                        ImportContractReservations::dispatchSync($contract->id);
                    } catch (Ripcord_TransportException $e) {
                        $this->error($e->getMessage());
                    }
                });

//        Contract::query()
//                ->where("salesperson_id", "=", 20)
//                ->has("flights", "=", 0)
//                ->delete();

//        /** @var Contract $contract */
//        $contract = Contract::with("flights", "flights.lines", "flights.lines.product.property")->firstWhere("id", "=", 611);
    }
}
