<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - SendContractJob.php
 */

namespace Neo\Jobs\Odoo;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Bus;
use Neo\Services\Odoo\Models\Campaign;
use Neo\Services\Odoo\Models\Contract;
use Neo\Services\Odoo\Models\OrderLine;
use Neo\Services\Odoo\OdooConfig;

class SendContractJob implements ShouldQueue {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(protected Contract $contract, protected array $flights, protected bool $clearOnSend) {
    }

    public function handle() {
        clock()->event('Send contract')->color('purple')->begin();

        // Clean up contract before insert if requested
        if($this->clearOnSend) {
            $this->cleanupContract();
        }

//        $flightsJobs = [];

        // We parse each flight of the contract, if it should be sent, we create a campaign in odoo for it, and add all the required orderlines
        foreach ($this->flights as $flightIndex => $flight) {
            if (!$flight['send']) {
                continue;
            }

            SendContractFlightJob::dispatch($this->contract, $flight, $flightIndex);
//            $flightsJobs[] = new SendContractFlightJob($this->contract, $flight);
        }

//        if(count($flightsJobs) > 0) {
//            Bus::chain($flightsJobs)->dispatch();
//        }

        clock()->event('Send contract')->end();
    }

    public function cleanupContract() {
        $client = OdooConfig::fromConfig()->getClient();

        // Remove all order lines from the contract
        OrderLine::delete($client, [
            ["order_id", "=", $this->contract->id],
        ]);

        // Remove all lines from the contract
        Campaign::delete($client, [
            ["order_id", "=", $this->contract->id]
        ]);
    }
}
