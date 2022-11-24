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
use Edujugon\Laradoo\Exceptions\OdooException;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use JsonException;
use Neo\Services\Odoo\Models\Campaign;
use Neo\Services\Odoo\Models\Contract;
use Neo\Services\Odoo\Models\Message;
use Neo\Services\Odoo\Models\OrderLine;
use Neo\Services\Odoo\OdooClient;
use Neo\Services\Odoo\OdooConfig;

class SendContractJob implements ShouldQueue {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(protected Contract $contract, protected array $plan, protected bool $clearOnSend) {
    }

    /**
     * @throws OdooException
     * @throws JsonException
     */
    public function handle(): void {
        $client = OdooConfig::fromConfig()->getClient();

        // Clean up contract before insert if requested
        if ($this->clearOnSend) {
            $this->cleanupContract($client);
        }

        $flightsDescriptions = [];

        // We parse each flight of the contract, if it should be sent, we create a campaign in odoo for it, and add all the required order lines
        foreach ($this->plan["flights"] as $flightIndex => $flight) {
            SendContractFlightJob::dispatchSync($this->contract, $flight, $flightIndex);

            $flightsDescriptions[] = $this->getFlightDescription($flight, $flightIndex);
        }

        $this->attachPlan($client);

        // Log import in odoo
        Message::create($client, [
            "subject"      => false,
            "body"         => implode("<br />", [
                $this->clearOnSend ? "Clear and Import" : "Import",
                ...$flightsDescriptions,
            ]),
            "model"        => Contract::$slug,
            "res_id"       => $this->contract->id,
            "message_type" => "notification",
            "subtype_id"   => 2,
        ], pullRecord: false);
    }

    protected function cleanupContract($client): void {
        // Remove all order lines from the contract
        $response = OrderLine::delete($client, [
            ["order_id", "=", $this->contract->id],
        ]);

        if ($response !== true) {
            Log::debug("Error when deleting order lines on contract " . $this->contract->name, [$response]);
        }

        // Remove all flights from the contract
        $response = Campaign::delete($client, [
            ["order_id", "=", $this->contract->getKey()],
        ]);

        if ($response !== true) {
            Log::debug("Error when deleting flight lines on contract " . $this->contract->name, [$response]);
        }
    }

    /**
     * @throws JsonException
     */
    protected function attachPlan(OdooClient $client) {
        $planFileName = $this->plan["contract"] . ".ccp";

        $this->contract->removeAttachment($planFileName);
        $this->contract->storeAttachment($planFileName, base64_encode(gzencode(json_encode($this->plan, JSON_THROW_ON_ERROR))));
    }

    protected function getFlightDescription(array $flight, int $flightIndex): string {
        $flightStart = Carbon::parse($flight['start_date'])->toDateString();
        $flightEnd   = Carbon::parse($flight['end_date'])->toDateString();
        $flightType  = ucFirst($flight["type"]);

        return "Flight #" . ($flightIndex + 1) . " ($flightType) [$flightStart -> $flightEnd]";
    }
}
