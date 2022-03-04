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

//        Contract::query()
//                ->where("id", "=", 329)
////                ->where("salesperson_id", "=", 23)
//                ->get()
//                ->each(function (Contract $contract) {
//                    try {
//                        ImportContractDataJob::dispatchSync($contract->id);
//                        ImportContractReservations::dispatchSync($contract->id);
//                    } catch (Ripcord_TransportException $e) {
//                        $this->error($e->getMessage());
//                    }
//                });

//        Contract::query()
//                ->where("salesperson_id", "=", 20)
//                ->has("flights", "=", 0)
//                ->delete();

//        /** @var Contract $contract */
//        $contract = Contract::with("flights", "flights.lines", "flights.lines.product.property")->firstWhere("id", "=", 611);

//        $propertyId = 964;
//        SynchronizePropertyData::dispatchSync($propertyId, OdooConfig::fromConfig()->getClient());

//        $tag    = Tag::query()->find("17");
//        $actors = Actor::find(88)->direct_children;
//        $actors->each(fn(Actor $actor) => $actor->tags()->attach($tag));

    }
}
