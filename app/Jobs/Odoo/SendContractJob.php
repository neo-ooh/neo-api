<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - SendContractJob.php
 */

namespace Neo\Jobs\Odoo;

use Edujugon\Laradoo\Exceptions\OdooException;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use JsonException;
use Neo\Modules\Properties\Models\InventoryProvider;
use Neo\Modules\Properties\Services\Exceptions\InvalidInventoryAdapterException;
use Neo\Modules\Properties\Services\InventoryAdapterFactory;
use Neo\Modules\Properties\Services\Odoo\API\OdooClient;
use Neo\Modules\Properties\Services\Odoo\Models\Campaign;
use Neo\Modules\Properties\Services\Odoo\Models\Contract;
use Neo\Modules\Properties\Services\Odoo\Models\Message;
use Neo\Modules\Properties\Services\Odoo\Models\OrderLine;
use Neo\Modules\Properties\Services\Odoo\OdooAdapter;
use Neo\Resources\Contracts\CPCompiledFlight;
use Neo\Resources\Contracts\CPCompiledPlan;

class SendContractJob implements ShouldQueue {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(protected Contract $contract, protected CPCompiledPlan $plan, protected bool $clearOnSend) {
    }

    /**
     * @throws InvalidInventoryAdapterException
     * @throws OdooException
     * @throws JsonException
     */
    public function handle(): array {
        $messages  = [];
        $inventory = InventoryProvider::query()->find(1);
        /** @var OdooAdapter $odoo */
        $odoo = InventoryAdapterFactory::make($inventory);

        $client = $odoo->getConfig()->getClient();

        // Clean up contract before insert if requested
        if ($this->clearOnSend) {
            $this->cleanupContract($client);
        }

        $flightsDescriptions = [];

        // We parse each flight of the contract, if it should be sent, we create a campaign in odoo for it, and add all the required order lines
        /** @var CPCompiledFlight $flight */
        foreach ($this->plan->flights as $flightIndex => $flight) {
            $flightMessages = (new SendContractFlightJob($this->contract, $flight, $flightIndex))->handle();

            $messages[$flight->getFlightName($flightIndex)] = $flightMessages;

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
        ], pullRecord:  false);

        return $messages;
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
     * @param OdooClient $client
     * @throws JsonException
     */
    protected function attachPlan(OdooClient $client): void {
        $planFileName = $this->plan->contract . ".ccp";

        $this->contract->removeAttachment($planFileName);
        $this->contract->storeAttachment($planFileName, base64_encode(gzencode(json_encode($this->plan, JSON_THROW_ON_ERROR))));
    }

    protected function getFlightDescription(CPCompiledFlight $flight, int $flightIndex): string {
        $type = $flight->type->name;
        return "Flight #" . ($flightIndex + 1) . " ($type) [$flight->start_date -> $flight->end_date]";
    }
}
