<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - POPFlightDetails.php
 */

namespace Neo\Modules\Properties\Documents\POP\components;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Neo\Models\ContractLine;
use Neo\Models\Screenshot;
use Neo\Modules\Properties\Documents\POP\PDFPOP;
use Neo\Modules\Properties\Documents\POP\POPFlight;
use Neo\Modules\Properties\Documents\POP\POPFlightGroup;
use Neo\Modules\Properties\Documents\POP\POPFlightNetwork;

class POPFlightDetails {
	public function __construct(protected POPFlight $flight, protected PDFPOP $pop) {

	}

	/**
	 * @return void
	 */
	public function render(): void {
		$showBreakdown   = $this->flight->breakdown !== null;
		$showScreenshots = $this->flight->screenshots->count() > 0;

		if ($showBreakdown || $showScreenshots) {
			$this->pop->addPage("legal", "main");

			// Render the flight header
			$this->pop->appendHTML(view("properties::pop.flight-details-header", [
				"flight" => $this->flight,
			])->render());
		}

		if ($showBreakdown) {
			$this->renderLines();
		}

		if ($showScreenshots) {
			if ($showBreakdown) {
				$this->pop->addPage("legal");
			}

			$this->renderScreenshots();
		}
	}

	public function renderLines() {
		// First step, is to group the flight lines by the defined groups
		/** @var Collection<POPFlightGroup> $groups */
		$groups = $this->flight->groups->toCollection();

		if ($groups->count() === 0) {
			// Add default group
			$groups->push(new POPFlightGroup(
				              name     : null,
				              provinces: [],
				              markets  : [],
				              cities   : [],
				              tags     : [],
			              ));
		}

		// Now we need to dispatch all the lines to their appropriate groups
		/** @var Collection<ContractLine> $remainingLines */
		$remainingLines = $this->flight->lines;

		/** @var POPFlightGroup $group */
		foreach ($groups as $group) {
			if ($group->name === null) {
				$group->lines   = $this->flight->lines;
				$remainingLines = [];
				continue;
			}

			$matchProvince = count($group->provinces) > 0;
			$matchMarkets  = count($group->markets) > 0;
			$matchCities   = count($group->cities) > 0;
			$matchTags     = count($group->tags) > 0;

			$groupLines    = [];
			$nonGroupLines = [];

			foreach ($remainingLines as $line) {
				$match = (
					(!$matchProvince || in_array($line->product->property->address->city->province->slug, $group->provinces))
					&& (!$matchMarkets || in_array($line->product->property->address->city->market_id, $group->markets))
					&& (!$matchCities || in_array($line->product->property->address->city_id, $group->cities))
					&& (!$matchTags || in_array($line->product->property->actor->tags->pluck("id"), $group->tags))
				);

				if ($match) {
					$groupLines[] = $line;
				} else {
					$nonGroupLines[] = $line;
				}
			}

			$group->lines   = $groupLines;
			$remainingLines = $nonGroupLines;
		}

		if (count($remainingLines) > 0) {
			$groups->push(new POPFlightGroup(
				              name     : __("pop.group-remaining"),
				              provinces: [],
				              markets  : [],
				              cities   : [],
				              tags     : [],
				              lines    : $remainingLines,
			              ));
		}

		$flightBreakdown = [
			                   "properties" => ["market", "property"],
			                   "categories" => ["market", "category"],
			                   "products"   => ["market", "property", "product"],
		                   ][$this->flight->breakdown];

		// Now render the groups
		/** @var POPFlightGroup $group */
		foreach ($groups as $group) {
			if ($group->name !== null) {
				// Render the group header
				$this->pop->appendHTML(view("properties::pop.flight-details-group", [
					"group" => $group,
				])->render());
			}

			// Group the products by network
			$linesByNetwork = collect($group->lines)->groupBy("product.property.network_id");

			foreach ($linesByNetwork as $networkId => $lines) {
				/** @var POPFlightNetwork $popNetwork */
				$popNetwork = $this->flight->networks->first(fn(POPFlightNetwork $network) => $network->network_id === $networkId);

				// Build the lines for this network
				$lineGroups = collect($this->buildLines(0, $flightBreakdown, $lines, $popNetwork))->chunkWhile(fn($l) => $l["level"] !== 0);

				// Render the network
				$this->pop->appendHTML(view("properties::pop.flight-details-network", [
					"network" => $lines[0]->product->property->network,
				])->render());

				// Render the lines groups
				foreach ($lineGroups as $renderLines) {
					$this->pop->appendHTML(view("properties::pop.flight-details-network-group", [
						"header"  => $renderLines->shift(),
						"footer"  => $renderLines->pop(),
						"lines"   => $renderLines,
						"network" => $lines[0]->product->property->network,
						"flight"  => $this->flight,
					])->render());
				}
			}
		}
	}

	public function renderScreenshots() {
		// Start by printing the screenshots section title
		$this->pop->appendHTML(view("properties::pop.flight-screenshots-header", [
			"flight" => $this->flight,
		])->render());

		$requestScreenshots = $this->flight->screenshots->toCollection();
		$screenshots        = Screenshot::query()->findMany($requestScreenshots->pluck("screenshot_id"))
		                                ->load(["product.property.address", "location"]);

		$formattedScreenshots = $screenshots->map(fn(Screenshot $screenshot) => ([
			"screenshot"    => $screenshot,
			"url"           => $screenshot->url,
			"display_name"  => $screenshot->product
				? $screenshot->product->{"name_" . App::getLocale()}
				: ($screenshot->location
					? $screenshot->location->name
					: ($screenshot->player
						? $screenshot->player->name
						: "-")
				),
			"location_name" => $screenshot->product
				? $screenshot->product->property->actor->name
				: ($screenshot->location
					? $screenshot->location->city . ", " . $screenshot->location->province
					: "-"
				),
			"received_at"   => $screenshot->received_at->setTimezone(array_filter([$screenshot->product?->property->address?->timezone])[0] ?? 'America/Toronto'),
			"mockup"        => $requestScreenshots->firstWhere("screenshot_id", "=", $screenshot->getKey())->mockup,
		]));

		$this->pop->appendHTML(view("properties::pop.flight-screenshots-thumbnails", [
			"screenshots" => $formattedScreenshots,
		]));

		// Print mockups
		$mockups = $formattedScreenshots->where("mockup", "=", true)->map(fn(array $screenshot) => ([
			...$screenshot,
			"url" => $screenshot["screenshot"]->mockup_path,
		]));

		if ($mockups->count() > 0) {
			$this->pop->addPage("legal", "mockups");

			$this->pop->appendHTML(view("properties::pop.flight-screenshots-mockups", [
				"mockups" => $mockups,
			]));
		}
	}

	/**
	 * @param int        $levelIndex
	 * @param array      $levels
	 * @param Collection $lines
	 * @param float      $deliveryFactor
	 * @return array
	 */
	public function buildLines(int $levelIndex, array $levels, Collection $lines, POPFlightNetwork $network): array {
		$nextLevels = [...$levels];
		/** @var string $currentLevel */
		$currentLevel = array_shift($nextLevels);

		$printLines = [];

		$linesGroups = match ($currentLevel) {
			"market"   => $lines->groupBy("product.property.address.city.market_id")->values(),
			"property" => $lines->groupBy("product.property_id")->values(),
			"category" => $lines->groupBy("product.category_id")->values(),
			default    => $lines->map(fn($l) => collect([$l])),
		};

		foreach ($linesGroups as $linesGroup) {
			$label = match ($currentLevel) {
				"market"   => $linesGroup[0]->product->property->address->city->market?->{"name_" . App::getLocale()} ?? '',
				"property" => $linesGroup[0]->product->property->actor->name,
				"category" => $linesGroup[0]->product->category->{"name_" . App::getLocale()},
				default    => $linesGroup[0]->product->{"name_" . App::getLocale()},
			};

			$contractedImpressions = $linesGroup->sum("impressions");
			$deliveredImpressions  = $linesGroup->sum("performances.impressions") * $network->delivered_impressions_factor;
			$deliveryProgress      = $contractedImpressions > 0 ? $deliveredImpressions / $contractedImpressions : 0;

			$levelLine = [
				"level"                  => $levelIndex,
				"type"                   => $currentLevel,
				"label"                  => $label,
				"contracted_impressions" => $contractedImpressions * $network->contracted_impressions_factor,
				"counted_impressions"    => $deliveredImpressions,
				"media_value"            => ($linesGroup->sum("media_value") * $network->contracted_media_value_factor) * $deliveryProgress,
				"cpm"                    => $deliveredImpressions > 0 ? ($linesGroup->sum("price") * $network->contracted_net_investment_factor) / $deliveredImpressions * 1000 : 0,
			];

			$printLines[] = $levelLine;

			if (count($nextLevels) > 0) {
				$printLines = [...$printLines, ...$this->buildLines($levelIndex + 1, $nextLevels, $linesGroup, $network)];
			}

			// If we are at the root level, we want a total row as well after the child ones.
			if ($levelIndex === 0) {
				$printLines[] = [
					...$levelLine,
					"level" => -1,
					"type"  => "total",
				];
			}
		}

		return $printLines;
	}
}
