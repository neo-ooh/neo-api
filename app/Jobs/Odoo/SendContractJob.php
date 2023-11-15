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
use JsonException;
use Neo\Modules\Properties\Models\InventoryProvider;
use Neo\Modules\Properties\Services\Exceptions\InvalidInventoryAdapterException;
use Neo\Modules\Properties\Services\InventoryAdapterFactory;
use Neo\Modules\Properties\Services\Resources\ContractResource;
use Neo\Resources\CampaignPlannerPlan\CompiledPlan\CPCompiledFlight;
use Neo\Resources\CampaignPlannerPlan\CompiledPlan\CPCompiledPlan;

class SendContractJob implements ShouldQueue {
	use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

	public function __construct(protected ContractResource $contract, protected CPCompiledPlan $plan, protected bool $clear) {
	}

	/**
	 * @throws InvalidInventoryAdapterException
	 * @throws OdooException
	 * @throws JsonException
	 */
	public function handle(): array {
		$messages  = [];
		$provider  = InventoryProvider::query()->find(1);
		$inventory = InventoryAdapterFactory::make($provider);

		if ($this->clear) {
			$inventory->clearContract($this->contract->contract_id);
		}

//		$flightsDescriptions = [];

		// We parse each flight of the contract, if it should be sent, we create a campaign in odoo for it, and add all the required order lines
		/** @var CPCompiledFlight $flight */
		foreach ($this->plan->flights as $flightIndex => $flight) {
			$flightMessages = (new SendContractFlightJob($this->contract, $flight, $flightIndex))->handle();

			if (count($flightMessages) > 0) {
				$messages[$flight->name] = $flightMessages;
			}

//			$flightsDescriptions[] = $this->getFlightDescription($flight, $flightIndex);
		}

		$inventory->setContractAttachedPlan($this->contract->contract_id, $this->plan->toJson());

		// Log import in odoo
//		Message::create($client, [
//			"subject"      => false,
//			"body"         => implode("<br />", [
//				$this->clear ? "Clear and Import" : "Import",
//				...$flightsDescriptions,
//			]),
//			"model"        => Contract::$slug,
//			"res_id"       => $this->contract->id,
//			"message_type" => "notification",
//			"subtype_id"   => 2,
//		], pullRecord:  false);

		return $messages;
	}

	protected function getFlightDescription(CPCompiledFlight $flight, int $flightIndex): string {
		$type = $flight->type->name;
		return "Flight #" . ($flightIndex + 1) . " ($type) [$flight->start_date -> $flight->end_date]";
	}
}
