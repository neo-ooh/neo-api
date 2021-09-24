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

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Bus;
use Neo\Services\Odoo\Models\Campaign;
use Neo\Services\Odoo\Models\Contract;
use Neo\Services\Odoo\Models\Message;
use Neo\Services\Odoo\Models\OrderLine;
use Neo\Services\Odoo\OdooConfig;

class SendContractJob implements ShouldQueue {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(protected Contract $contract, protected array $flights, protected bool $clearOnSend) {
    }

    public function handle() {
        clock()->event('Send contract')->color('purple')->begin();

        $client = OdooConfig::fromConfig()->getClient();

        // Clean up contract before insert if requested
        if($this->clearOnSend) {
            $this->cleanupContract($client);
        }

        $flightsDescriptions = [];

        // We parse each flight of the contract, if it should be sent, we create a campaign in odoo for it, and add all the required orderlines
        foreach ($this->flights as $flightIndex => $flight) {
            if (!$flight['send']) {
                continue;
            }

            SendContractFlightJob::dispatch($this->contract, $flight, $flightIndex);

            $flightsDescriptions[] = $this->getFlightDescription($flight, $flightIndex);
        }

        // Log import in odoo
        Message::create($client, [
            ["subject" => false],
            ["body" => implode("\n", [
                $this->clearOnSend ? "Clear and Import" : "Import",
                $flightsDescriptions
            ])],
            ["model" => Contract::$slug],
            ["res_id" => $this->contract->id],
            ["subtype_id" => 2],
        ]);

        clock()->event('Send contract')->end();
    }

    protected function cleanupContract($client) {

        // Remove all order lines from the contract
        OrderLine::delete($client, [
            ["order_id", "=", $this->contract->id],
        ]);

        // Remove all lines from the contract
        Campaign::delete($client, [
            ["order_id", "=", $this->contract->id]
        ]);
    }

    protected function getFlightDescription(array $flight, int $flightIndex) {
        $flightStart = Carbon::parse($flight['start'])->toDateString();
        $flightEnd = Carbon::parse($flight['end'])->toDateString();
        $flightType = ucFirst($flight["type"]);

         return "Flight #$flightIndex ($flightType) [$flightStart -> $flightEnd])";
    }
}
