<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ImportContractDataJob.php
 */

namespace Neo\Jobs\Contracts;

use Edujugon\Laradoo\Exceptions\OdooException;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use JsonException;
use Neo\Models\Advertiser;
use Neo\Models\Client;
use Neo\Models\Contract;
use Neo\Models\ContractFlight;
use Neo\Models\ContractLine;
use Neo\Modules\Properties\Models\Product;
use Neo\Resources\Contracts\CPCompiledCategory;
use Neo\Resources\Contracts\CPCompiledFlight;
use Neo\Resources\Contracts\CPCompiledProduct;
use Neo\Resources\Contracts\CPCompiledProperty;
use Neo\Resources\Contracts\FlightType;
use Neo\Services\Odoo\Models\Contract as OdooContract;
use Neo\Services\Odoo\Models\OrderLine;
use Neo\Services\Odoo\OdooClient;
use Neo\Services\Odoo\OdooConfig;
use Symfony\Component\Console\Output\ConsoleOutput;
use function Ramsey\Uuid\v4;

class ImportContractDataJob implements ShouldQueue {
	use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

	public int $timeout = 300;

	public function __construct(
		protected int               $contractId,
		protected OdooContract|null $odooContract = null
	) {
	}

	/**
	 * @throws OdooException
	 * @throws JsonException
	 */
	public function handle(): void {
		$output = new ConsoleOutput();

		/** @var Contract|null $contract */
		$contract = Contract::query()->find($this->contractId);

		if (!$contract) {
			// contract not found!
			return;
		}

		$odooClient = OdooConfig::fromConfig()->getClient();

		// Check if the contract is present in ODOO
		if ($this->odooContract === null) {
			$this->odooContract = OdooContract::findByName($odooClient, $contract->contract_id);
		}

		// Get contract client
		/** @var Client $client */
		$client = Client::query()->firstOrCreate([
			                                         "odoo_id" => $this->odooContract->partner_id[0],
		                                         ], [
			                                         "name" => $this->odooContract->partner_id[1],
		                                         ]);

		$output->writeln($contract->contract_id . ": Set client to $client->name (#$client->id))");

		// Get contract advertiser
		$advertiser = null;

		if ($this->odooContract->analytic_account_id) {
			/** @var Advertiser $advertiser */
			$advertiser = Advertiser::query()->firstOrCreate([
				                                                 "odoo_id" => $this->odooContract->analytic_account_id[0],
			                                                 ], [
				                                                 "name" => $this->odooContract->analytic_account_id[1],
			                                                 ]);

			$output->writeln($contract->contract_id . ": Set advertiser to $advertiser->name (#$advertiser->id))");
		}

		$contract->external_id   = $this->odooContract->id;
		$contract->advertiser_id = $advertiser?->getKey();
		$contract->client_id     = $client->getKey();
		$contract->save();

		// Check if the contract has a compiled plan attached to it
		$contractAttachment = $this->odooContract->getAttachment($contract->getAttachedPlanName());

		$hasPlan = $contractAttachment !== null;

		if ($hasPlan) {
			// A contract attachment is available.
			// We import it, and store it
			$contract->storePlan($contractAttachment->datas);
			$contract->has_plan = true;
			$contract->save();

			$output->writeln($contract->contract_id . ": Attached Campaign Planner Plan.");
		}

		// Load all the lines from the contract from Odoo, and remove linked ones
		$orderLines = $this->getLinesFromOdoo($odooClient, $contract, $output)
		                   ->filter(fn(OrderLine $orderLine) => !$orderLine->is_linked_line);

		// List all the products in connect matching the ones in the order lines
		$products = Product::query()
		                   ->whereHas("external_representations", function (Builder $query) use ($orderLines) {
			                   $query->whereIn(DB::raw("JSON_VALUE(context, '$.variant_id')"), $orderLines->pluck("product_id.0")
			                                                                                              ->unique());
		                   })
		                   ->with(["category", "external_representations"])
		                   ->get();

		// Load flights from the plan, if available
		/** @var Collection<FlightDefinition> $flights */
		$flights = $hasPlan ? $this->getFlightsFromPlan($contract, $output) : new Collection();

		// Takes the flight already existing in the contract that didn't exist in the contract
		$planFlights = $flights->pluck("uid");
		foreach ($contract->flights as $i => $contractFlight) {
			if ($planFlights->doesntContain($contractFlight->uid)) {
				$flights->push(new FlightDefinition(
					               name     : $contractFlight->name ?? "Flight #$i",
					               uid      : $contractFlight->uid,
					               type     : $contractFlight->type,
					               startDate: $contractFlight->start_date->toDateString(),
					               endDate  : $contractFlight->end_date->toDateString()
				               ));
			}
		}

		// Now we parse all the order lines in the contract, and match them to their respective flights
		/** @var OrderLine $orderLine */
		foreach ($orderLines as $orderLine) {
			// Find the counterpart product in connect for this line
			/** @var Product|null $product */
			$product = $products->firstWhere(fn(Product $product) => $product->external_representations->where("context.variant_id", "=", $orderLine->product_id[0])
			                                                                                           ->isNotEmpty()
			);

			if ($product === null) {
				// Unknown product, ignore
				continue;
			}

			// Parse known flights and find one with the same sale type, dates, and who has a reference to this product
			// Infer the sale type of the line
			$saleType = FlightType::Guaranteed;
			if ($product->is_bonus) {
				$saleType = FlightType::BUA;
			} else if ($orderLine->discount > 99.9) {
				$saleType = FlightType::Bonus;
			}

			// Start by matching flights by specs
			$matchingFlights = $flights->filter(fn(FlightDefinition $flight) => $flight->type === $saleType
				&& $flight->startDate === $orderLine->rental_start
				&& $flight->endDate === $orderLine->rental_end
			);

			// Now perform a second filter, to see if one of the matching flights reference the line
			/** @var FlightDefinition|null $matchingFlight */
			$matchingFlight = null;
			/** @var CPCompiledProduct|null $planLine */
			$planLine = null;

			foreach ($matchingFlights as $flight) {
				$planLine = $flight->planLines->firstWhere("id", "=", $product->getKey());
				if ($planLine !== null) {
					$matchingFlight = $flight;
					break;
				}
			}

			if (!$matchingFlight) {
				// No flight reference this specific line. Did some flights matched by specs at least ?
				if ($matchingFlights->count() > 0) {
					// Yes, use the first one as a fallback
					/** @var FlightDefinition $matchingFlight */
					$matchingFlight                       = $matchingFlights->first();
					$matchingFlight->additionalLinesAdded = true;
				} else {
					// No, create a new definition matching this one, and add it to the list
					$matchingFlight = new FlightDefinition(
						name     : "Flight #" . count($flights) + 1,
						uid      : v4(),
						type     : $saleType,
						startDate: $orderLine->rental_start,
						endDate  : $orderLine->rental_end
					);
					$flights->push($matchingFlight);
					$contract->additional_flights_imported = true;
				}
			}

			// Add the order line
			$matchingFlight->lines->push(new LineDefinition(
				                             productId    : $product->getKey(),
				                             lineId       : $orderLine->getKey(),
				                             spots        : $orderLine->product_uom_qty,
				                             media_value  : $orderLine->price_unit * $orderLine->nb_weeks * $orderLine->nb_screen * $orderLine->product_uom_qty,
				                             discount     : $orderLine->discount,
				                             discount_type: "relative",
				                             price        : $orderLine->price_subtotal,
				                             traffic      : $planLine?->traffic ?? 0,
				                             impressions  : $planLine?->impressions ?? $orderLine->connect_impression ?: $orderLine->impression
			                             ));
		}

		// Lines have now been mapped to flights, we can insert everything.
		// We try to be conservative here, want to update as much as possible.
		// If the contract got updated, we want to presere as many associations as possible.

		$storedFlights = collect();

		/** @var FlightDefinition $flight */
		foreach ($flights as $flightDefinition) {
			// Did all the products defined in the flight have a counterpart line ?
			foreach ($flightDefinition->productIds as $productId) {
				if (!$flightDefinition->lines->contains("productId", "=", $productId)) {
					$flightDefinition->missingReferencedLine = true;
				}
			}

			$flight = ContractFlight::updateOrCreate([
				                                         "contract_id" => $contract->getKey(),
				                                         "uid"         => $flightDefinition->uid,
			                                         ], [
				                                         "name"                      => $flightDefinition->name,
				                                         "start_date"                => $flightDefinition->startDate,
				                                         "end_date"                  => $flightDefinition->endDate,
				                                         "type"                      => $flightDefinition->type,
				                                         "additional_lines_imported" => $flightDefinition->additionalLinesAdded,
				                                         "missing_lines_on_import"   => $flightDefinition->missingReferencedLine,
			                                         ]);

			$storedFlights->push($flight);

			// Remove the flight lines, we will re-import them.
			$flight->lines()->delete();

			/** @var LineDefinition $line */
			foreach ($flightDefinition->lines as $line) {
				ContractLine::query()->upsert([
					                              "flight_id" => $flight->getKey(),
					                                                                                                                                                                                                                                                                                                                                                               "product_id" => $line->productId,
					                                                                                                                                                                                                                                                                                                                                                                                                                                                                           "external_id" => $line->lineId,
					                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                           "spots" => $line->spots,
					                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                           "media_value" => $line->media_value,
					                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                   "discount" => $line->discount,
					                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                   "discount_type" => $line->discount_type,
					                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                       "price" => $line->price,
					                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                       "traffic" => $line->traffic,
					                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                       "impressions" => $line->impressions,
				                              ], [
					                              "flight_id", "product_id",
				                              ]);
			}
		}

		// Everything has been inserted, do some cleanup
		// Remove any flights attached with the contract that are not part of the ones just created
		$contract->flights()->whereNotIn("id", $storedFlights->pluck("id"))->delete();
		// And remove any flight that have no lines in them
		$contract->flights()->whereDoesntHave("lines")->delete();

		$output->writeln($contract->contract_id . ": " . $storedFlights->count() . " Flights attached.");

		// Update contract start date, end date and expected impressions
		$startDate = $storedFlights
			->sortBy("start_date", SORT_REGULAR, descending: false)
			->first()?->start_date;

		$endDate = $storedFlights
			->sortBy("end_date", SORT_REGULAR, descending: true)
			->first()?->end_date;

		$contract->start_date           = $startDate;
		$contract->end_date             = $endDate;
		$contract->expected_impressions = $flights->sum(
			fn(FlightDefinition $flight) => $flight->lines->sum(
				fn(LineDefinition $line) => $line->impressions)
		);
		$contract->save();
	}

	/**
	 * @throws JsonException
	 * @returns Collection<FlightDefinition>
	 */
	public function getFlightsFromPlan(Contract $contract, ConsoleOutput $output): Collection {
		// Get the compiled plan from the contract
		$plan = $contract->getStoredPlanAttribute();

		if (!$plan) {
			return new Collection();
		}

		$output->writeln($contract->contract_id . ": Loading plan flights from attached plan");

		// Take all the flights from the plan, and make `FlightDefinitions` for each one of them
		/** @var Collection<FlightDefinition> $flights */
		$flights = collect();

		/** @var CPCompiledFlight $flight */
		foreach ($plan->flights as $i => $flight) {
			// List all the compiled products in the flight
			/** @var Collection<CPCompiledProduct> $products */
			$products = $flight->properties->toCollection()->flatMap(
				fn(CPCompiledProperty $property) => $property->categories->toCollection()->flatMap(
					fn(CPCompiledCategory $category) => $category->products->toCollection()
				)
			);

			$flights->push(new FlightDefinition(
				               name      : $flight->name ?? "Flight #" . ($i + 1),
				               uid       : $flight->id,
				               type      : $flight->type,
				               startDate : $flight->start_date,
				               endDate   : $flight->end_date,
				               planLines : $products,
				               productIds: $products->pluck("id")
			               ));
		}

		return $flights;
	}

	/**
	 * @throws OdooException
	 * @throws JsonException
	 */
	public function getLinesFromOdoo(OdooClient $client, Contract $contract, ConsoleOutput $output): Collection {
		$lines     = collect();
		$chunkSize = 50;

		$output->writeln($contract->contract_id . ": Loading contract lines from Odoo...");

		do {
			$hasMore = false;

			$receivedLines = OrderLine::all($client, [
				["order_id", '=', $this->odooContract->id],
				["is_linked_line", '!=', 1],
			],                              $chunkSize, $lines->count());

			if ($receivedLines->count() === $chunkSize) {
				$hasMore = true;
			}

			$lines = $lines->merge($receivedLines);
			$output->writeln($contract->contract_id . ": Collected {$lines->count()} lines...");
		} while ($hasMore);

		return $lines;
	}
}
