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

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\LazyCollection;
use JsonException;
use Neo\Modules\Properties\Models\Contract;
use Neo\Modules\Properties\Models\ContractFlight;
use Neo\Modules\Properties\Models\ContractLine;
use Neo\Modules\Properties\Models\InventoryProvider;
use Neo\Modules\Properties\Models\Product;
use Neo\Modules\Properties\Services\Exceptions\InvalidInventoryAdapterException;
use Neo\Modules\Properties\Services\Exceptions\InventoryResourceNotFound;
use Neo\Modules\Properties\Services\InventoryAdapterFactory;
use Neo\Modules\Properties\Services\Resources\ContractLineResource;
use Neo\Modules\Properties\Services\Resources\Enums\ContractLineType;
use Neo\Modules\Properties\Services\Resources\Enums\InventoryResourceType;
use Neo\Modules\Properties\Services\Resources\InventoryResourceId;
use Neo\Resources\CampaignPlannerPlan\CompiledPlan\CPCompiledFlight;
use Neo\Resources\CampaignPlannerPlan\CompiledPlan\Mobile\CPCompiledMobileProperty;
use Neo\Resources\CampaignPlannerPlan\CompiledPlan\OOH\CPCompiledOOHCategory;
use Neo\Resources\CampaignPlannerPlan\CompiledPlan\OOH\CPCompiledOOHProduct;
use Neo\Resources\CampaignPlannerPlan\CompiledPlan\OOH\CPCompiledOOHProperty;
use Neo\Resources\FlightType;
use Symfony\Component\Console\Output\ConsoleOutput;
use function Ramsey\Uuid\v4;

class ImportContractDataJob implements ShouldQueue {
	use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

	public int $timeout = 300;

	public function __construct(
		protected int $contractId,
	) {
	}

	/**
	 * @throws JsonException
	 * @throws InvalidInventoryAdapterException
	 */
	public function handle(): void {
		$output = new ConsoleOutput();

		/** @var Contract|null $contract */
		$contract = Contract::query()->find($this->contractId);

		if (!$contract) {
			// contract not found!
			return;
		}

		$provider  = InventoryProvider::query()->find($contract->inventory_id);
		$inventory = InventoryAdapterFactory::make($provider);

		try {
			$externalContract = $inventory->getContract(new InventoryResourceId(
				                                            inventory_id: $inventory->getInventoryID(),
				                                            external_id : $contract->external_id,
				                                            type        : InventoryResourceType::Contract,
			                                            ));
		} catch (InventoryResourceNotFound) {
			// Contract not found, stop here.
			return;
		}

		// Check if the contract has a compiled plan attached to it
		$attachedPlanData = $inventory->getContractAttachedPlan($externalContract->contract_id);

		if ($hasPlan = $attachedPlanData !== null) {
			// A contract attachment is available.
			// We import it, and store it
			$contract->storePlan($attachedPlanData);
			$contract->has_plan = true;
			$contract->save();

			$output->writeln($contract->contract_id . ": Attached Campaign Planner Plan.");
		}

		// Load flights from the plan, if available
		/** @var Collection<FlightDefinition> $flights */
		$flights = $hasPlan ? $this->getFlightsFromPlan($contract, $output) : new Collection();

		// Takes the flight already existing in the contract that didn't exist in the contract
		$planFlights = $flights->pluck("uid");
		foreach ($contract->flights as $i => $contractFlight) {
			if ($planFlights->doesntContain($contractFlight->uid)) {
				$flights->push(new FlightDefinition(
					               name      : $contractFlight->name ?? "Flight #$i",
					               uid       : $contractFlight->uid,
					               type      : $contractFlight->type,
					               start_date: $contractFlight->start_date->toDateString(),
					               end_date  : $contractFlight->end_date->toDateString()
				               ));
			}
		}

		// To preserve memory but still retain some performances, we'll parse the lines by chunk of 250
		$linesChunks = LazyCollection::make($externalContract->lines)
		                             ->chunk(250);

		$lineIsOOH    = fn(ContractLineResource $line) => in_array($line->type, [ContractLineType::Guaranteed, ContractLineType::Bonus, ContractLineType::BUA]);
		$lineIsMobile = fn(ContractLineResource $line) => $line->type == ContractLineType::Mobile;

		/** @var LazyCollection<ContractLineResource> $lines */
		foreach ($linesChunks as $lines) {
			// Filter our linked lines and production costs
			$lines = $lines->filter(fn(ContractLineResource $line) => !$line->is_linked && $line->type !== ContractLineType::ProductionCost);

			$oohLines = $lines->filter($lineIsOOH);

			// List all the products in connect matching the ones in the order lines
			$products = Product::query()
			                   ->whereHas("external_representations", function (Builder $query) use ($oohLines) {
				                   $query->whereIn(DB::raw("JSON_VALUE(context, '$.variant_id')"), $oohLines->pluck("product_id.external_id")
				                                                                                            ->unique());
			                   })
			                   ->with(["category", "external_representations"])
			                   ->get();

			// Now we parse all the order lines in the contract, and match them to their respective flights
			/** @var ContractLineResource $orderLine */
			foreach ($lines as $orderLine) {
				// Parse known flights and find one with the same sale type, dates, and who has a reference to this product
				$lineFlightType = FlightType::from($orderLine->type->value);

				// Start by matching flights by specs
				$matchingFlights = $flights->filter(fn(FlightDefinition $flight) => $flight->type === $lineFlightType
					&& $flight->start_date === $orderLine->start_date
					&& $flight->end_date === $orderLine->end_date
				);

				// Now if it is an OOH product, perform a second filter
				// to see if one of the matching flights references the line
				/** @var FlightDefinition|null $matchingFlight */
				$matchingFlight = null;

				$product = null;

				if ($lineIsOOH($orderLine)) {
					// Find the counterpart product in connect for this line
					/** @var Product|null $product */
					$product = $products->firstWhere(fn(Product $product) => $product->external_representations->where("context.variant_id", "=", $orderLine->product_id->external_id)
					                                                                                           ->isNotEmpty()
					);

					if ($product === null) {
						// Unknown product, ignore
						continue;
					}

					/** @var CPCompiledOOHProduct|CPCompiledMobileProperty|null $planLine */
					$planLine = null;

					foreach ($matchingFlights as $flight) {
						$planLine = $flight->plan_lines->firstWhere("id", "=", $product->getKey());
						if ($planLine !== null) {
							$matchingFlight = $flight;
							break;
						}
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
							name      : "Flight #" . (count($flights) + 1),
							uid       : v4(),
							type      : $lineFlightType,
							start_date: $orderLine->start_date,
							end_date  : $orderLine->end_date
						);

						$flights->push($matchingFlight);
						$contract->additional_flights_imported = true;
					}
				}

				if ($lineIsOOH($orderLine)) {
					// Add the order line
					$matchingFlight->lines->push(new LineDefinition(
						                             productId    : $product->getKey(),
						                             lineId       : $orderLine->line_id->external_id,
						                             spots        : $orderLine->spots_count,
						                             media_value  : $orderLine->media_value,
						                             discount     : $orderLine->discount_amount_relative,
						                             discount_type: "relative",
						                             price        : $orderLine->price,
						                             traffic      : $planLine->traffic ?? 0,
						                             impressions  : $planLine->impressions ?? $orderLine->impressions,
					                             ));
				}
			}
		}

		// Lines have now been mapped to flights, we can insert everything.
		// We try to be conservative here, want to update as much as possible.
		// If the contract got updated, we want to presere as many associations as possible.

		$storedFlights = collect();

		/** @var FlightDefinition $flightDefinition */
		foreach ($flights as $flightDefinition) {
			// Did all the products defined in the flight have a counterpart line ?
			foreach ($flightDefinition->product_ids as $productId) {
				if (!$flightDefinition->lines->contains("productId", "=", $productId)) {
					$flightDefinition->missingReferencedLine = true;
				}
			}

			/** @var ContractFlight $flight */
			$flight = ContractFlight::query()->updateOrCreate([
				                                                  "contract_id" => $contract->getKey(),
				                                                  "uid"         => $flightDefinition->uid,
			                                                  ], [
				                                                  "name"                      => $flightDefinition->name,
				                                                  "start_date"                => $flightDefinition->start_date,
				                                                  "end_date"                  => $flightDefinition->end_date,
				                                                  "type"                      => $flightDefinition->type,
				                                                  "additional_lines_imported" => $flightDefinition->additionalLinesAdded,
				                                                  "missing_lines_on_import"   => $flightDefinition->missingReferencedLine,
				                                                  "parameters"                => $flightDefinition->parameters,
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
		$contract->flights()->where("type", "<>", FlightType::Mobile)
		         ->whereDoesntHave("lines")->delete();

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
			$flightDefinition = new FlightDefinition(
				name      : $flight->name ?? "Flight #" . ($i + 1),
				uid       : $flight->id,
				type      : $flight->type,
				start_date: $flight->start_date,
				end_date  : $flight->end_date,
			);

			switch ($flight->type) {
				case FlightType::Guaranteed:
				case FlightType::Bonus:
				case FlightType::BUA:
					// OOH Flight
					// List all the compiled products in the flight
					/** @var Collection<CPCompiledOOHProduct> $products */
					$products = $flight->getAsOOHFlight()->properties->toCollection()->flatMap(
						fn(CPCompiledOOHProperty $property) => $property->categories->toCollection()->flatMap(
							fn(CPCompiledOOHCategory $category) => $category->products->toCollection()
						)
					);

					$flightDefinition->plan_lines  = $products;
					$flightDefinition->product_ids = $products->pluck("id");
					break;
				case FlightType::Mobile:
					// Mobile Flight
					$mobileFlight = $flight->getAsMobileFlight();

					$flightDefinition->parameters->mobile_properties                   = $mobileFlight->properties->toCollection()
					                                                                                              ->map(fn(CPCompiledMobileProperty $p) => [
						                                                                                              "property_id" => $p->id,
						                                                                                              "impressions" => $p->impressions,
					                                                                                              ])
					                                                                                              ->all();
					$flightDefinition->parameters->mobile_product                      = $mobileFlight->product_id;
					$flightDefinition->parameters->mobile_additional_targeting         = $mobileFlight->additional_targeting ?? "";
					$flightDefinition->parameters->mobile_audience_targeting           = $mobileFlight->audience_targeting ?? "";
					$flightDefinition->parameters->mobile_website_retargeting          = $mobileFlight->website_retargeting;
					$flightDefinition->parameters->mobile_online_conversion_monitoring = $mobileFlight->online_conversion_monitoring;
					$flightDefinition->parameters->mobile_retail_conversion_monitoring = $mobileFlight->retail_conversion_monitoring;
					break;
			}

			$flights->push($flightDefinition);
		}

		return $flights;
	}
}
