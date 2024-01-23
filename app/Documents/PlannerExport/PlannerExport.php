<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - PlannerExport.php
 */

namespace Neo\Documents\PlannerExport;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Lang;
use Neo\Documents\XLSX\Worksheet;
use Neo\Documents\XLSX\XLSXDocument;
use Neo\Documents\XLSX\XLSXStyleFactory;
use Neo\Modules\Properties\Models\MobileProduct;
use Neo\Modules\Properties\Models\ProductCategory;
use Neo\Modules\Properties\Models\PropertyNetwork;
use Neo\Resources\CampaignPlannerPlan\CompiledPlan\CPCompiledFlight;
use Neo\Resources\CampaignPlannerPlan\CompiledPlan\CPCompiledGroup;
use Neo\Resources\CampaignPlannerPlan\CompiledPlan\CPCompiledPlan;
use Neo\Resources\CampaignPlannerPlan\CompiledPlan\Mobile\CPCompiledMobileFlight;
use Neo\Resources\CampaignPlannerPlan\CompiledPlan\Mobile\CPCompiledMobileProperty;
use Neo\Resources\CampaignPlannerPlan\CompiledPlan\OOH\CPCompiledOOHCategory;
use Neo\Resources\CampaignPlannerPlan\CompiledPlan\OOH\CPCompiledOOHFlight;
use Neo\Resources\CampaignPlannerPlan\CompiledPlan\OOH\CPCompiledOOHProduct;
use Neo\Resources\CampaignPlannerPlan\CompiledPlan\OOH\CPCompiledOOHProperty;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

class PlannerExport extends XLSXDocument {
	protected CPCompiledPlan $plan;

	/**
	 * @param $data
	 * @return bool
	 */
	protected function ingest($data): bool {
		$this->plan = CPCompiledPlan::from($data);
		return true;
	}

	/**
	 * @inheritDoc
	 * @throws Exception
	 */
	public function build(): bool {
		$firstSheetName  = $this->sanitizeSheetName(Lang::get("contract.summary"));
		$this->worksheet = new Worksheet(null, $firstSheetName);
		$this->spreadsheet->addSheet($this->worksheet);
		$this->spreadsheet->setActiveSheetIndex(1);

		// Remove the first sheet as it is not being used
		$this->spreadsheet->removeSheetByIndex(0);

		// Print the summary page
		$this->printSummary();

		// Print each flight's details page
		/** @var CPCompiledFlight $flight */
		foreach ($this->plan->flights as $flightIndex => $flight) {
			if ($flight->isOOHFlight()) {
				$this->printOOHFlight($flight, $flight->getAsOOHFlight(), $flightIndex);
			} else if ($flight->isMobileFlight()) {
				$this->printMobileFlight($flight, $flight->getAsMobileFlight(), $flightIndex);
			}
		}

		$this->spreadsheet->setActiveSheetIndexByName($firstSheetName);
		return true;
	}

	/**
	 * @param int $width
	 * @return void
	 * @throws Exception
	 */
	protected function printHeader(int $width): void {
		$this->ws->pushPosition();

		// Set the header style
		$this->ws->getStyle($this->ws->getRelativeRange($width, 5))->applyFromArray([
			                                                                            'font'      => [
				                                                                            'bold'  => true,
				                                                                            'color' => [
					                                                                            'argb' => "FFFFFFFF",
				                                                                            ],
				                                                                            'size'  => "13",
				                                                                            "name"  => "Calibri",
			                                                                            ],
			                                                                            'alignment' => [
				                                                                            'horizontal' => Alignment::HORIZONTAL_CENTER,
				                                                                            'vertical'   => Alignment::VERTICAL_CENTER,
			                                                                            ],
			                                                                            'fill'      => [
				                                                                            'fillType'   => Fill::FILL_SOLID,
				                                                                            'startColor' => [
					                                                                            'argb' => XLSXStyleFactory::COLORS["dark-blue"],
				                                                                            ],
			                                                                            ],
		                                                                            ]);

		// Add the Neo logo
		$drawing = new Drawing();
		$drawing->setName('Neo-OOH');
		$drawing->setDescription('Neo Out of Home');
		$drawing->setPath(resource_path("logos/main.light.png"));
		$drawing->setHeight(65);
		$drawing->setWorksheet($this->ws);
		$drawing->setCoordinates('D2');

		// Date
		$this->ws->printRow(["Date", Date::now()->toFormattedDateString()]);
		if ($this->plan->contract !== null) {
			$this->ws->printRow([Lang::get("common.header-contract"), $this->plan->contract->contract_id]);
			$this->ws->printRow([Lang::get("contract.header-advertiser"), $this->plan->contract->advertiser_name]);
			$this->ws->printRow([Lang::get("contract.header-customer"), $this->plan->contract->client_name]);
		}

		$this->ws->popPosition();
		$this->ws->moveCursor(0, 5);
	}

	/**
	 * @throws Exception
	 */
	protected function printSummary(): void {
		$this->printHeader(11);

		$flightsValues = collect();

		// Flights
		foreach ($this->plan->flights as $flightIndex => $flight) {
			$flightsValues->push($this->printFlightSummary($flight, $flightIndex));
		}

		$this->ws->getStyle($this->ws->getRelativeRange(11, 2))->applyFromArray(XLSXStyleFactory::totals());
		$this->ws->mergeCellsRelative(1, 2);

		// Print Totals headers
		$this->ws->printRow([
			                    'Total',
			                    Lang::get("contract.table-properties"),
			                    in_array("faces", $this->plan->columns, true) ? Lang::get("contract.table-faces") : "",
			                    in_array("traffic", $this->plan->columns, true) ? Lang::get("contract.table-traffic") : "",
			                    in_array("impressions", $this->plan->columns, true) ? Lang::get("contract.table-impressions") : "",
			                    in_array("media-value", $this->plan->columns, true) ? Lang::get("contract.table-media-value") : "",
			                    in_array("media-investment", $this->plan->columns, true) ? Lang::get("contract.table-media-investment") : "",
			                    in_array("production-cost", $this->plan->columns, true) ? Lang::get("contract.table-production-cost") : "",
			                    in_array("price", $this->plan->columns, true) ? Lang::get("contract.table-net-investment") : "",
			                    in_array("cpm", $this->plan->columns, true) ? Lang::get("contract.table-cpm") : "",
		                    ]);

		$this->ws->setRelativeCellFormat(NumberFormat::FORMAT_CURRENCY_USD, 5);
		$this->ws->setRelativeCellFormat(NumberFormat::FORMAT_CURRENCY_USD, 6);
		$this->ws->setRelativeCellFormat(NumberFormat::FORMAT_CURRENCY_USD, 7);
		$this->ws->setRelativeCellFormat(NumberFormat::FORMAT_CURRENCY_USD, 8);
		$this->ws->setRelativeCellFormat(NumberFormat::FORMAT_CURRENCY_USD_INTEGER, 9);

		$impressions = $flightsValues->sum("impressions");
		$cpm         = $impressions > 0 ? $flightsValues->sum("cpmPrice") / $impressions * 1000 : 0;

		// Print Totals values
		$this->ws->printRow([
			                    '',
			                    $flightsValues->sum("propertiesCount"),
			                    in_array("faces", $this->plan->columns, true) ? $flightsValues->sum("faces") : "",
			                    in_array("traffic", $this->plan->columns, true) ? $flightsValues->sum("traffic") : "",
			                    in_array("impressions", $this->plan->columns, true) ? $flightsValues->sum("impressions") : "",
			                    in_array("media-value", $this->plan->columns, true) ? $flightsValues->sum("mediaValue") : "",
			                    in_array("media-investment", $this->plan->columns, true) ? $flightsValues->sum("mediaInvestment") : "",
			                    in_array("production-cost", $this->plan->columns, true) ? $flightsValues->sum("productionCost") : "",
			                    in_array("price", $this->plan->columns, true) ? $flightsValues->sum("price") : "",
			                    in_array("cpm", $this->plan->columns, true) ? $cpm : "",
		                    ]);

		// Autosize columns
		$this->ws->getColumnDimension("A")->setAutoSize(true);
		$this->ws->getColumnDimension("B")->setAutoSize(true);
		$this->ws->getColumnDimension("C")->setAutoSize(true);
		$this->ws->getColumnDimension("D")->setAutoSize(true);
		$this->ws->getColumnDimension("E")->setAutoSize(true);
		$this->ws->getColumnDimension("F")->setAutoSize(true);
		$this->ws->getColumnDimension("G")->setAutoSize(true);
		$this->ws->getColumnDimension("H")->setAutoSize(true);
		$this->ws->getColumnDimension("I")->setAutoSize(true);
		$this->ws->getColumnDimension("J")->setAutoSize(true);
	}

	/**
	 * @throws Exception
	 */
	protected function printFlightSummary(CPCompiledFlight $flight, int $flightIndex): array {
		$this->ws->getStyle($this->ws->getRelativeRange(11))->applyFromArray(XLSXStyleFactory::flightRow());

		$this->ws->pushPosition();
		$this->ws->moveCursor(5, 0)->mergeCellsRelative(2);
		$this->ws->popPosition();

		$this->ws->printRow([
			                    $flight->name ?? ("Flight #" . ($flightIndex + 1)),
			                    $flight->start_date->toDateString(),
			                    '→',
			                    $flight->end_date->toDateString(),
			                    Lang::get("common.order-type-" . $flight->type->value),
		                    ]);

		$this->ws->getStyle($this->ws->getRelativeRange(11))->applyFromArray(XLSXStyleFactory::simpleTableHeader());
		$this->ws->getStyle($this->ws->getRelativeRange(11, 7))->applyFromArray([
			                                                                        "fill" => [
				                                                                        'fillType'   => Fill::FILL_SOLID,
				                                                                        'startColor' => [
					                                                                        'argb' => "FFFFFFFF",
				                                                                        ],
			                                                                        ],
		                                                                        ]);

		$flightValues = [
			"propertiesCount" => 0,
			"faces"           => 0,
			"traffic"         => 0,
			"impressions"     => 0,
			"mediaValue"      => 0,
			"mediaInvestment" => 0,
			"productionCost"  => 0,
			"price"           => 0,
			"cpmPrice"        => 0,
		];

		if ($flight->isOOHFlight()) {
			$oohFlight = $flight->getAsOOHFlight();

			$this->ws->printRow([
				                    Lang::get("contract.table-networks"),
				                    Lang::get("contract.table-properties"),
				                    in_array("faces", $this->plan->columns, true) ? Lang::get("contract.table-faces") : "",
				                    in_array("traffic", $this->plan->columns, true) ? Lang::get("contract.table-traffic") : "",
				                    in_array("impressions", $this->plan->columns, true) ? Lang::get("contract.table-impressions") : "",
				                    in_array("media-value", $this->plan->columns, true) ? Lang::get("contract.table-media-value") : "",
				                    in_array("media-investment", $this->plan->columns, true) ? Lang::get("contract.table-media-investment") : "",
				                    in_array("production-cost", $this->plan->columns, true) ? Lang::get("contract.table-production-cost") : "",
				                    in_array("price", $this->plan->columns, true) ? Lang::get("contract.table-net-investment") : "",
				                    in_array("cpm", $this->plan->columns, true) ? Lang::get("contract.table-cpm") : "",
				                    in_array("weeks", $this->plan->columns, true) ? Lang::get("contract.table-weeks") : "",
			                    ]);

			if ($oohFlight->groups->count() === 1 && $oohFlight->groups[0]->name === null) {
				$this->printOOHFlightSummaryByNetwork($flight, $oohFlight);
			} else {
				$this->printOOHFlightSummaryByGroup($flight, $oohFlight);
			}

			$this->ws->getStyle($this->ws->getRelativeRange(11))->applyFromArray(XLSXStyleFactory::simpleTableTotals());

			$flightValues = [
				"propertiesCount" => $oohFlight->properties->count(),
				"faces"           => $oohFlight->faces_count,
				"traffic"         => $oohFlight->traffic,
				"impressions"     => $oohFlight->impressions,
				"mediaValue"      => $oohFlight->media_value,
				"mediaInvestment" => $oohFlight->discounted_media_value,
				"productionCost"  => $oohFlight->production_cost_value,
				"price"           => $oohFlight->price,
				"cpmPrice"        => $oohFlight->cpmPrice,
			];

			$this->ws->setRelativeCellFormat(NumberFormat::FORMAT_CURRENCY_USD, 5);
			$this->ws->setRelativeCellFormat(NumberFormat::FORMAT_CURRENCY_USD, 6);
			$this->ws->setRelativeCellFormat(NumberFormat::FORMAT_CURRENCY_USD, 7);
			$this->ws->setRelativeCellFormat(NumberFormat::FORMAT_CURRENCY_USD, 8);
			$this->ws->setRelativeCellFormat(NumberFormat::FORMAT_CURRENCY_USD_INTEGER, 9);

			$this->ws->printRow([
				                    "Total",
				                    $flightValues["propertiesCount"],
				                    in_array("faces", $this->plan->columns, true) ? $flightValues["faces"] : "",
				                    in_array("traffic", $this->plan->columns, true) ? $flightValues["traffic"] : "",
				                    in_array("impressions", $this->plan->columns, true) ? $flightValues["impressions"] : "",
				                    in_array("media-value", $this->plan->columns, true) ? $flightValues["mediaValue"] : "",
				                    in_array("media-investment", $this->plan->columns, true) ? $flightValues["mediaInvestment"] : "",
				                    in_array("production-cost", $this->plan->columns, true) ? $flightValues["productionCost"] : "",
				                    in_array("price", $this->plan->columns, true) ? $flightValues["price"] : "",
				                    in_array("cpm", $this->plan->columns, true) ? $oohFlight->cpm : "",
				                    in_array("weeks", $this->plan->columns, true) ? $flight->getWeekLength() : "",
			                    ]);
		} else if ($flight->isMobileFlight()) {
			$mobileFlight = $flight->getAsMobileFlight();

			$this->ws->printRow([
				                    Lang::get("contract.table-networks"),
				                    Lang::get("contract.table-properties"),
				                    "",
				                    "",
				                    in_array("impressions", $this->plan->columns, true) ? Lang::get("contract.table-impressions") : "",
				                    in_array("media-value", $this->plan->columns, true) ? Lang::get("contract.table-media-value") : "",
				                    in_array("media-investment", $this->plan->columns, true) ? Lang::get("contract.table-media-investment") : "",
				                    "",
				                    in_array("price", $this->plan->columns, true) ? Lang::get("contract.table-net-investment") : "",
				                    in_array("cpm", $this->plan->columns, true) ? Lang::get("contract.table-cpm") : "",
				                    in_array("weeks", $this->plan->columns, true) ? Lang::get("contract.table-weeks") : "",
			                    ]);

			if ($mobileFlight->groups->count() > 1 || $mobileFlight->groups[0]->name !== null) {
				$this->printMobileFlightSummaryByGroup($flight, $mobileFlight);
			}

			$this->ws->getStyle($this->ws->getRelativeRange(11))->applyFromArray(XLSXStyleFactory::simpleTableTotals());

			$flightValues = [
				"propertiesCount" => $mobileFlight->properties->count(),
				"faces"           => 0,
				"traffic"         => 0,
				"impressions"     => $mobileFlight->impressions,
				"mediaValue"      => $mobileFlight->media_value,
				"mediaInvestment" => $mobileFlight->media_value,
				"productionCost"  => 0,
				"price"           => $mobileFlight->price,
				"cpmPrice"        => $mobileFlight->price,
			];

			$this->ws->setRelativeCellFormat(NumberFormat::FORMAT_CURRENCY_USD, 5);
			$this->ws->setRelativeCellFormat(NumberFormat::FORMAT_CURRENCY_USD, 6);
			$this->ws->setRelativeCellFormat(NumberFormat::FORMAT_CURRENCY_USD, 7);
			$this->ws->setRelativeCellFormat(NumberFormat::FORMAT_CURRENCY_USD, 8);
			$this->ws->setRelativeCellFormat(NumberFormat::FORMAT_CURRENCY_USD_INTEGER, 9);

			$this->ws->printRow([
				                    "Total",
				                    $flightValues["propertiesCount"],
				                    in_array("faces", $this->plan->columns, true) ? $flightValues["faces"] : "",
				                    in_array("traffic", $this->plan->columns, true) ? $flightValues["traffic"] : "",
				                    in_array("impressions", $this->plan->columns, true) ? $flightValues["impressions"] : "",
				                    in_array("media-value", $this->plan->columns, true) ? $flightValues["mediaValue"] : "",
				                    in_array("media-investment", $this->plan->columns, true) ? $flightValues["mediaInvestment"] : "",
				                    in_array("production-cost", $this->plan->columns, true) ? $flightValues["productionCost"] : "",
				                    in_array("price", $this->plan->columns, true) ? $flightValues["price"] : "",
				                    in_array("cpm", $this->plan->columns, true) ? $mobileFlight->cpm : "",
				                    in_array("weeks", $this->plan->columns, true) ? $flight->getWeekLength() : "",
			                    ]);
		}

		$this->ws->moveCursor(0, 2);

		return $flightValues;
	}

	/**
	 * @param CPCompiledFlight    $flight
	 * @param CPCompiledOOHFlight $oohFlight
	 * @return void
	 * @throws Exception
	 */
	public function printOOHFlightSummaryByNetwork(CPCompiledFlight $flight, CPCompiledOOHFlight $oohFlight): void {
		$properties = \Neo\Modules\Properties\Models\Property::query()
		                                                     ->withoutEagerLoads()
		                                                     ->select(["actor_id", "network_id"])
		                                                     ->findMany($oohFlight->properties->toCollection()
		                                                                                      ->map(fn(CPCompiledOOHProperty $p) => $p->id));

		$networksIds = $properties->pluck("network_id")->unique();
		$networks    = PropertyNetwork::query()->whereIn("id", $networksIds)->orderBy("id")->get();

		/** @var PropertyNetwork $network */
		foreach ($networks as $network) {
			$networkProperties  = $properties->where("network_id", "=", $network->getKey());
			$compiledProperties = $networkProperties->map(fn(\Neo\Modules\Properties\Models\Property $p) => $oohFlight->properties->toCollection()
			                                                                                                                      ->firstWhere("id", "=", $p->getKey()));

			$this->ws->setRelativeCellFormat("#,##0_-", 1);
			$this->ws->getStyle($this->ws->getRelativeRange(1))->applyFromArray([
				                                                                    "font" => [
					                                                                    "color" => [
						                                                                    "argb" => "FF" . $network->color,
					                                                                    ],
				                                                                    ],
			                                                                    ]);
			$this->ws->setRelativeCellFormat("#,##0_-", 2);
			$this->ws->setRelativeCellFormat("#,##0_-", 3);
			$this->ws->setRelativeCellFormat("#,##0_-", 4);
			$this->ws->setRelativeCellFormat(NumberFormat::FORMAT_CURRENCY_USD, 5);
			$this->ws->setRelativeCellFormat(NumberFormat::FORMAT_CURRENCY_USD, 6);
			$this->ws->setRelativeCellFormat(NumberFormat::FORMAT_CURRENCY_USD, 7);
			$this->ws->setRelativeCellFormat(NumberFormat::FORMAT_CURRENCY_USD, 8);
			$this->ws->setRelativeCellFormat(NumberFormat::FORMAT_CURRENCY_USD_INTEGER, 9);

			$impressions = $compiledProperties->sum("impressions");
			$cpmPrice    = $compiledProperties->sum("cpmPrice");
			$cpm         = $impressions > 0 ? $cpmPrice / $impressions * 1000 : 0;

			$this->ws->printRow([
				                    $network->name,
				                    $properties->count(),
				                    in_array("faces", $this->plan->columns, true) ? $compiledProperties->sum("faces") : "",
				                    in_array("traffic", $this->plan->columns, true) ? $compiledProperties->sum("traffic") : "",
				                    in_array("impressions", $this->plan->columns, true) ? $impressions : "",
				                    in_array("media-value", $this->plan->columns, true) ? $compiledProperties->sum("media_value") : "",
				                    in_array("media-investment", $this->plan->columns, true) ? $compiledProperties->sum("discounted_media_value") : "",
				                    in_array("production-cost", $this->plan->columns, true) ? $compiledProperties->sum("production_cost") : "",
				                    in_array("price", $this->plan->columns, true) ? $compiledProperties->sum("price") : "",
				                    in_array("cpm", $this->plan->columns, true) ? $cpm : "",
				                    in_array("weeks", $this->plan->columns, true) ? $flight->getWeekLength() : "",
			                    ]);
		}
	}

	/**
	 * @param CPCompiledFlight    $flight
	 * @param CPCompiledOOHFlight $oohFlight
	 * @return void
	 * @throws Exception
	 */
	public function printOOHFlightSummaryByGroup(CPCompiledFlight $flight, CPCompiledOOHFlight $oohFlight): void {
		/** @var CPCompiledGroup $group */
		foreach ($oohFlight->groups as $group) {
			$compiledProperties = $oohFlight->properties->toCollection()
			                                            ->filter(fn(CPCompiledOOHProperty $p) => in_array($p->id, $group->properties));

			$this->ws->setRelativeCellFormat("#,##0_-", 1);
			$this->ws->getStyle($this->ws->getRelativeRange(1))->applyFromArray([
				                                                                    "font" => [
					                                                                    "color" => [
						                                                                    "argb" => "FF" . ($group->color ?? "000000"),
					                                                                    ],
				                                                                    ],
			                                                                    ]);
			$this->ws->setRelativeCellFormat("#,##0_-", 2);
			$this->ws->setRelativeCellFormat("#,##0_-", 3);
			$this->ws->setRelativeCellFormat("#,##0_-", 4);
			$this->ws->setRelativeCellFormat(NumberFormat::FORMAT_CURRENCY_USD, 5);
			$this->ws->setRelativeCellFormat(NumberFormat::FORMAT_CURRENCY_USD, 6);
			$this->ws->setRelativeCellFormat(NumberFormat::FORMAT_CURRENCY_USD, 7);
			$this->ws->setRelativeCellFormat(NumberFormat::FORMAT_CURRENCY_USD, 8);
			$this->ws->setRelativeCellFormat(NumberFormat::FORMAT_CURRENCY_USD_INTEGER, 9);

			$impressions = $compiledProperties->sum("impressions");
			$cpmPrice    = $compiledProperties->sum("cpm");
			$cpm         = $impressions > 0 ? $cpmPrice / $impressions : 0;

			$this->ws->printRow([
				                    $group->name ?? Lang::get("contract.group-remaining-properties"),
				                    $compiledProperties->count(),
				                    in_array("faces", $this->plan->columns, true) ? $compiledProperties->sum("faces_count") : "",
				                    in_array("traffic", $this->plan->columns, true) ? $compiledProperties->sum("traffic") : "",
				                    in_array("impressions", $this->plan->columns, true) ? $impressions : "",
				                    in_array("media-value", $this->plan->columns, true) ? $compiledProperties->sum("media_value") : "",
				                    in_array("media-investment", $this->plan->columns, true) ? $compiledProperties->sum("discounted_media_value") : "",
				                    in_array("production-cost", $this->plan->columns, true) ? $compiledProperties->sum("production_cost") : "",
				                    in_array("price", $this->plan->columns, true) ? $compiledProperties->sum("price") : "",
				                    in_array("cpm", $this->plan->columns, true) ? $cpm : "",
				                    in_array("weeks", $this->plan->columns, true) ? $flight->getWeekLength() : "",
			                    ]);
		}
	}

	/**
	 * @throws Exception
	 */
	public function printMobileFlightSummaryByGroup(CPCompiledFlight $flight, CPCompiledMobileFlight $mobileFlight): void {
		/** @var CPCompiledGroup $group */
		foreach ($mobileFlight->groups as $group) {
			$compiledProperties = $mobileFlight->properties->toCollection()
			                                               ->filter(fn(CPCompiledMobileProperty $p) => in_array($p->id, $group->properties));

			$this->ws->setRelativeCellFormat("#,##0_-", 1);
			$this->ws->getStyle($this->ws->getRelativeRange(1))->applyFromArray([
				                                                                    "font" => [
					                                                                    "color" => [
						                                                                    "argb" => "FF" . ($group->color ?? "000000"),
					                                                                    ],
				                                                                    ],
			                                                                    ]);
			$this->ws->setRelativeCellFormat("#,##0_-", 2);
			$this->ws->setRelativeCellFormat(NumberFormat::FORMAT_CURRENCY_USD, 5);
			$this->ws->setRelativeCellFormat(NumberFormat::FORMAT_CURRENCY_USD, 6);
			$this->ws->setRelativeCellFormat(NumberFormat::FORMAT_CURRENCY_USD, 8);
			$this->ws->setRelativeCellFormat(NumberFormat::FORMAT_CURRENCY_USD_INTEGER, 9);

			$media_value = $compiledProperties->sum("media_value");
			$impressions = $compiledProperties->sum("impressions");
			$price       = $compiledProperties->sum("price");
			$cpm         = $impressions > 0 ? $price / $impressions * 1000 : 0;

			$this->ws->printRow([
				                    $group->name ?? Lang::get("contract.group-remaining-properties"),
				                    $compiledProperties->count(),
				                    "",
				                    "",
				                    in_array("impressions", $this->plan->columns, true) ? $impressions : "",
				                    in_array("media-value", $this->plan->columns, true) ? $media_value : "",
				                    in_array("media-investment", $this->plan->columns, true) ? $media_value : "",
				                    "",
				                    in_array("price", $this->plan->columns, true) ? $price : "",
				                    in_array("cpm", $this->plan->columns, true) ? $cpm : "",
				                    in_array("weeks", $this->plan->columns, true) ? $flight->getWeekLength() : "",
			                    ]);
		}
	}

	/**
	 * @throws Exception
	 */
	public function printFlightHeader(CPCompiledFlight $flight, int $flightIndex, int $width = 8): void {
		$this->ws->getStyle($this->ws->getRelativeRange($width))->applyFromArray(XLSXStyleFactory::flightRow());

		$this->ws->pushPosition();
		$this->ws->moveCursor(5, 0)->mergeCellsRelative(2);
		$this->ws->popPosition();

		$product = "";

		if ($flight->isMobileFlight()) {
			$mobileProduct = MobileProduct::query()->find($flight->getAsMobileFlight()->product_id);
			if ($mobileProduct) {
				$product = $mobileProduct["name_" . Lang::locale()];
			}
		}

		$this->ws->printRow([
			                    $flight->name ?? ("Flight #" . ($flightIndex + 1)),
			                    $flight->start_date->toDateString(),
			                    '→',
			                    $flight->end_date->toDateString(),
			                    Lang::get("common.order-type-" . $flight->type->value),
			                    $product,
		                    ]);
	}

	/**
	 * @throws Exception
	 */
	public function printOOHFlight(CPCompiledFlight $flight, CPCompiledOOHFlight $oohFlight, int $flightIndex): void {
        $sheetname =  $this->sanitizeSheetName($flight->name);
		$this->worksheet = new Worksheet(null, $sheetname);
		$this->spreadsheet->addSheet($this->worksheet);
		$this->spreadsheet->setActiveSheetIndexByName($sheetname);

		$this->printHeader(12);
		$this->printFlightHeader($flight, $flightIndex, width: 12);

		$groups      = $oohFlight->groups;
		$groupsCount = $groups->count();

		/** @var Collection<ProductCategory> $allCategories */
		$allCategories = ProductCategory::query()
		                                ->findMany($oohFlight->properties
			                                           ->toCollection()
			                                           ->flatMap(fn(CPCompiledOOHProperty $p) => $p->categories->toCollection()
			                                                                                                   ->map(fn(CPCompiledOOHCategory $c) => $c->id))
			                                           ->unique()
		                                );

		/** @var Collection<\Neo\Modules\Properties\Models\Product> $allProducts */
		$allProducts = \Neo\Modules\Properties\Models\Product::query()
		                                                     ->findMany($oohFlight->properties
			                                                                ->toCollection()
			                                                                ->flatMap(
				                                                                fn(CPCompiledOOHProperty $p) => $p->categories->toCollection()
				                                                                                                              ->flatMap(fn(CPCompiledOOHCategory $c) => $c->products->toCollection()
				                                                                                                                                                                    ->map(fn(CPCompiledOOHProduct $p) => $p->id)
				                                                                                                              ))
			                                                                ->unique()
		                                                     );

		/** @var CPCompiledGroup $group */
		foreach ($groups as $group) {
			// Load all properties in group
			$groupProperties = \Neo\Modules\Properties\Models\Property::query()
			                                                          ->with(["address.city"])
			                                                          ->findMany($group->properties);

			$groupCompiledProperties = $oohFlight->properties->toCollection()
			                                                 ->filter(fn(CPCompiledOOHProperty $p) => in_array($p->id, $group->properties));

			$networks = PropertyNetwork::query()
			                           ->whereIn("id", $groupProperties->pluck("network_id")->unique())
			                           ->orderBy("id")
			                           ->get();

			if ($groupsCount !== 1 || ($group->name !== null && $group->name !== "remaining")) {
				// Group header
				$this->ws->getStyle($this->ws->getRelativeRange(12))->applyFromArray(XLSXStyleFactory::simpleTableHeader());
				$this->ws->getStyle($this->ws->getRelativeRange(12))->applyFromArray([
					                                                                     "font" => [
						                                                                     'size'  => "14",
						                                                                     "color" => [
							                                                                     "argb" => "FF" . ($group->color ?? "000000"),
						                                                                     ],
					                                                                     ],
					                                                                     "fill" => [
						                                                                     'fillType'   => Fill::FILL_SOLID,
						                                                                     'startColor' => [
							                                                                     'argb' => "FFFFFFFF",
						                                                                     ],
					                                                                     ],
				                                                                     ]);

				$this->ws->printRow([
					                    $group->name === 'remaining' ? Lang::get("contract.group-remaining-properties") : $group->name ?? "",
				                    ]);
			}

			/** @var PropertyNetwork $network */
			foreach ($networks as $network) {
				// Network header
				$this->ws->getStyle($this->ws->getRelativeRange(12))->applyFromArray(XLSXStyleFactory::simpleTableHeader());
				$this->ws->getStyle($this->ws->getRelativeRange(12))->applyFromArray([
					                                                                     "font" => [
						                                                                     'size'  => "14",
						                                                                     "color" => [
							                                                                     "argb" => "FF" . $network->color,
						                                                                     ],
					                                                                     ],
					                                                                     "fill" => [
						                                                                     'fillType'   => Fill::FILL_SOLID,
						                                                                     'startColor' => [
							                                                                     'argb' => "FFFFFFFF",
						                                                                     ],
					                                                                     ],
				                                                                     ]);

				$this->ws->printRow([
					                    $network->name,
					                    in_array("zipcode", $this->plan->columns, true) ? Lang::get("contract.table-zipcode") : "",
					                    in_array("location", $this->plan->columns, true) ? Lang::get("contract.table-location") : "",
					                    in_array("faces", $this->plan->columns, true) ? Lang::get("contract.table-faces") : "",
					                    in_array("spots", $this->plan->columns, true) ? Lang::get("contract.table-spots") : "",
					                    in_array("traffic", $this->plan->columns, true) ? Lang::get("contract.table-traffic") : "",
					                    in_array("impressions", $this->plan->columns, true) ? Lang::get("contract.table-impressions") : "",
					                    in_array("media-value", $this->plan->columns, true) ? Lang::get("contract.table-media-value") : "",
					                    in_array("media-investment", $this->plan->columns, true) ? Lang::get("contract.table-media-investment") : "",
					                    in_array("production-cost", $this->plan->columns, true) ? Lang::get("contract.table-production-cost") : "",
					                    in_array("price", $this->plan->columns, true) ? Lang::get("contract.table-net-investment") : "",
					                    in_array("cpm-lines", $this->plan->columns, true) ? Lang::get("contract.table-cpm") : "",
				                    ]);


				$networkProperties = $groupProperties->where("network_id", "=", $network->getKey());
				$propertiesIds     = $networkProperties->pluck("actor_id");

				$compiledProperties = $oohFlight->properties->toCollection()
				                                            ->filter(fn(CPCompiledOOHProperty $p) => $propertiesIds->contains($p->id));
				$properties         = $compiledProperties->map(fn(CPCompiledOOHProperty $p) => $groupProperties->find($p->id))
				                                         ->whereNotNull()
				                                         ->sortBy("name");

				/** @var \Neo\Modules\Properties\Models\Property $property */
				foreach ($properties as $property) {
					/** @var CPCompiledOOHProperty $compiledProperty */
					$compiledProperty = $compiledProperties->firstWhere("id", "=", $property->getKey());


					$this->ws->getStyle($this->ws->getRelativeRange(12))->applyFromArray([
						                                                                     "font" => [
							                                                                     "size" => 12,
							                                                                     'bold' => true,
						                                                                     ],
						                                                                     "fill" => [
							                                                                     'fillType'   => Fill::FILL_SOLID,
							                                                                     'startColor' => [
								                                                                     'argb' => "FFFFFFFF",
							                                                                     ],
						                                                                     ],
					                                                                     ]);

					$this->ws->setRelativeCellFormat("#,##0_-", 3);
					$this->ws->setRelativeCellFormat("#,##0_-", 4);
					$this->ws->setRelativeCellFormat("#,##0_-", 5);
					$this->ws->setRelativeCellFormat("#,##0_-", 6);
					$this->ws->setRelativeCellFormat(NumberFormat::FORMAT_CURRENCY_USD, 7);
					$this->ws->setRelativeCellFormat(NumberFormat::FORMAT_CURRENCY_USD, 8);
					$this->ws->setRelativeCellFormat(NumberFormat::FORMAT_CURRENCY_USD, 9);
					$this->ws->setRelativeCellFormat(NumberFormat::FORMAT_CURRENCY_USD, 10);
					$this->ws->setRelativeCellFormat(NumberFormat::FORMAT_CURRENCY_USD_INTEGER, 11);

					$this->ws->printRow([
						                    $property->actor->name,
						                    in_array("zipcode", $this->plan->columns, true) ? substr($property->address->zipcode, 0, 3) . " " . substr($property->address->zipcode, 3) : "",
						                    in_array("location", $this->plan->columns, true) ? $property->address->city->name : "",
						                    in_array("faces", $this->plan->columns, true) ? $compiledProperty->faces_count : "",
						                    "",
						                    in_array("traffic", $this->plan->columns, true) ? $compiledProperty->traffic : "",
						                    in_array("impressions", $this->plan->columns, true) ? $compiledProperty->impressions : "",
						                    in_array("media-value", $this->plan->columns, true) ? $compiledProperty->media_value : "",
						                    in_array("media-investment", $this->plan->columns, true) ? $compiledProperty->discounted_media_value : "",
						                    in_array("production-cost", $this->plan->columns, true) ? $compiledProperty->production_cost_value : "",
						                    in_array("price", $this->plan->columns, true) ? $compiledProperty->price : "",
						                    in_array("cpm-lines", $this->plan->columns, true) ? $compiledProperty->cpm : "",
					                    ]);

					$compiledCategories = $compiledProperty->categories->toCollection();
					$productCategories  = $compiledCategories->map(fn(CPCompiledOOHCategory $c) => $allCategories->find($c->id))
					                                         ->whereNotNull()
					                                         ->sortBy("name_" . Lang::locale());

					/** @var ProductCategory $category */
					foreach ($productCategories as $category) {
						/** @var CPCompiledOOHCategory $compiledCategory */
						$compiledCategory = $compiledCategories->firstWhere("id", "=", $category->getKey());

						$compiledProducts = $compiledCategory->products->toCollection();
						$products         = $compiledProducts->map(fn(CPCompiledOOHProduct $p) => $allProducts->find($p->id))
						                                     ->whereNotNull()
						                                     ->sortBy("product.name_" . Lang::locale());

						/** @var \Neo\Modules\Properties\Models\Product $product */
						foreach ($products as $product) {
							/** @var CPCompiledOOHProduct $compiledProduct */
							$compiledProduct = $compiledProducts->firstWhere("id", "=", $product->getKey());

							$this->ws->getStyle($this->ws->getRelativeRange(12))->applyFromArray([
								                                                                     "font" => [
									                                                                     "size" => 10,
								                                                                     ],
								                                                                     "fill" => [
									                                                                     'fillType'   => Fill::FILL_SOLID,
									                                                                     'startColor' => [
										                                                                     'argb' => "FFFFFFFF",
									                                                                     ],
								                                                                     ],
							                                                                     ]);

							$this->ws->getStyle($this->ws->getRelativeRange(1))->applyFromArray([
								                                                                    'alignment' => [
									                                                                    "indent" => 8,
								                                                                    ],
							                                                                    ]);

							$this->ws->setRelativeCellFormat("#,##0_-", 3);
							$this->ws->setRelativeCellFormat("#,##0.0_-", 4);
							$this->ws->setRelativeCellFormat("#,##0_-", 5);
							$this->ws->setRelativeCellFormat("#,##0_-", 6);
							$this->ws->setRelativeCellFormat(NumberFormat::FORMAT_CURRENCY_USD, 7);
							$this->ws->setRelativeCellFormat(NumberFormat::FORMAT_CURRENCY_USD, 8);
							$this->ws->setRelativeCellFormat(NumberFormat::FORMAT_CURRENCY_USD, 9);
							$this->ws->setRelativeCellFormat(NumberFormat::FORMAT_CURRENCY_USD, 10);
							$this->ws->setRelativeCellFormat(NumberFormat::FORMAT_CURRENCY_USD_INTEGER, 11);

							$this->ws->printRow([
								                    $product["name_" . Lang::locale()],
								                    "",
								                    "",
								                    in_array("faces", $this->plan->columns, true) ? $compiledProduct->quantity : "",
								                    in_array("spots", $this->plan->columns, true) ? $compiledProduct->spots : "",
								                    "",
								                    in_array("impressions", $this->plan->columns, true) ? $compiledProduct->impressions : "",
								                    in_array("media-value", $this->plan->columns, true) ? $compiledProduct->media_value : "",
								                    in_array("media-investment", $this->plan->columns, true) ? $compiledProduct->discounted_media_value : "",
								                    in_array("production-cost", $this->plan->columns, true) ? $compiledProduct->production_cost_value : "",
								                    in_array("price", $this->plan->columns, true) ? $compiledProduct->price : "",
								                    in_array("cpm-lines", $this->plan->columns, true) ? $compiledProduct->cpm : "",
							                    ]);
						}
					}
				}

				$this->ws->getStyle($this->ws->getRelativeRange(12, 1))->applyFromArray([
					                                                                        "fill" => [
						                                                                        'fillType'   => Fill::FILL_SOLID,
						                                                                        'startColor' => [
							                                                                        'argb' => "FFFFFFFF",
						                                                                        ],
					                                                                        ],
				                                                                        ]);

				$this->ws->moveCursor(0, 1);
			}

			// Group footer
			$this->ws->getStyle($this->ws->getRelativeRange(12))->applyFromArray(XLSXStyleFactory::totals());
			$this->ws->setRelativeCellFormat("#,##0_-", 3);
			$this->ws->setRelativeCellFormat("#,##0_-", 5);
			$this->ws->setRelativeCellFormat("#,##0_-", 6);
			$this->ws->setRelativeCellFormat(NumberFormat::FORMAT_CURRENCY_USD, 7);
			$this->ws->setRelativeCellFormat(NumberFormat::FORMAT_CURRENCY_USD, 8);
			$this->ws->setRelativeCellFormat(NumberFormat::FORMAT_CURRENCY_USD, 9);
			$this->ws->setRelativeCellFormat(NumberFormat::FORMAT_CURRENCY_USD, 10);
			$this->ws->setRelativeCellFormat(NumberFormat::FORMAT_CURRENCY_USD_INTEGER, 11);

			$faces                  = $groupCompiledProperties->sum("faces");
			$traffic                = $groupCompiledProperties->sum("traffic");
			$impressions            = $groupCompiledProperties->sum("impressions");
			$media_value            = $groupCompiledProperties->sum("media_value");
			$disocunted_media_value = $groupCompiledProperties->sum("discounted_media_value");
			$production_cost        = $groupCompiledProperties->sum("production_cost");
			$price                  = $groupCompiledProperties->sum("price");
			$cpmPrice               = $groupCompiledProperties->sum("cpmPrice");
			$cpm                    = $impressions > 0 ? $cpmPrice / $impressions * 1000 : 0;

			$this->ws->printRow([
				                    "Total",
				                    "",
				                    "",
				                    in_array("faces", $this->plan->columns, true) ? $faces : "",
				                    "",
				                    in_array("impressions", $this->plan->columns, true) ? $traffic : "",
				                    in_array("impressions", $this->plan->columns, true) ? $impressions : "",
				                    in_array("media-value", $this->plan->columns, true) ? $media_value : "",
				                    in_array("media-investment", $this->plan->columns, true) ? $disocunted_media_value : "",
				                    in_array("production-cost", $this->plan->columns, true) ? $production_cost : "",
				                    in_array("price", $this->plan->columns, true) ? $price : "",
				                    in_array("cpm", $this->plan->columns, true) ? $cpm : "",
			                    ]);

			$this->ws->getStyle($this->ws->getRelativeRange(12, 2))->applyFromArray([
				                                                                        "fill" => [
					                                                                        'fillType'   => Fill::FILL_SOLID,
					                                                                        'startColor' => [
						                                                                        'argb' => "FFFFFFFF",
					                                                                        ],
				                                                                        ],
			                                                                        ]);

			$this->ws->moveCursor(0, 2);
		}

		$this->ws->getColumnDimension("A")->setAutoSize(true);
		$this->ws->getColumnDimension("B")->setAutoSize(true);
		$this->ws->getColumnDimension("C")->setAutoSize(true);
		$this->ws->getColumnDimension("D")->setAutoSize(true);
		$this->ws->getColumnDimension("E")->setAutoSize(true);
		$this->ws->getColumnDimension("F")->setAutoSize(true);
		$this->ws->getColumnDimension("G")->setAutoSize(true);
		$this->ws->getColumnDimension("H")->setAutoSize(true);
		$this->ws->getColumnDimension("I")->setAutoSize(true);
		$this->ws->getColumnDimension("J")->setAutoSize(true);
		$this->ws->getColumnDimension("K")->setAutoSize(true);
	}


	/**
	 * @throws Exception
	 */
	public function printMobileFlight(CPCompiledFlight $flight, CPCompiledMobileFlight $mobileFlight, int $flightIndex): void {
        $sheetName = $this->sanitizeSheetName($flight->name);
		$this->worksheet = new Worksheet(null, $sheetName);
		$this->spreadsheet->addSheet($this->worksheet);
		$this->spreadsheet->setActiveSheetIndexByName($sheetName);

		$this->printHeader(8);
		$this->printFlightHeader($flight, $flightIndex, width: 8);

		// Print mobile flight parameters
		$this->ws->getStyle($this->ws->getRelativeRange(8))->applyFromArray(XLSXStyleFactory::simpleTableHeader());
		$this->ws->printRow([
			                    Lang::get("contract.mobile-audience-targeting"),
			                    "",
			                    Lang::get("contract.mobile-additional-targeting"),
		                    ]);
		$this->ws->printRow([
			                    $mobileFlight->audience_targeting,
			                    "",
			                    $mobileFlight->additional_targeting,
		                    ]);

		$this->ws->printRow([]);

		$this->ws->getStyle($this->ws->getRelativeRange(8))->applyFromArray(XLSXStyleFactory::simpleTableHeader());
		$this->ws->printRow([
			                    $mobileFlight->website_retargeting ? Lang::get("contract.mobile-website-retargeting") : "",
			                    "",
			                    $mobileFlight->online_conversion_monitoring ? Lang::get("contract.mobile-online-conversion-monitoring") : "",
			                    "",
			                    $mobileFlight->retail_conversion_monitoring ? Lang::get("contract.mobile-retail-conversion-monitoring") : "",
		                    ]);
		$this->ws->printRow([
			                    $mobileFlight->website_retargeting ? Lang::get("common.yes") : "",
			                    "",
			                    $mobileFlight->online_conversion_monitoring ? Lang::get("common.yes") : "",
			                    "",
			                    $mobileFlight->retail_conversion_monitoring ? Lang::get("common.yes") : "",
		                    ]);

		$this->ws->moveCursor(0, 2);

		// Print the flight content
		$groups      = $mobileFlight->groups;
		$groupsCount = $groups->count();

		/** @var CPCompiledGroup $group */
		foreach ($groups as $group) {
			// Load all properties in group
			$groupProperties = \Neo\Modules\Properties\Models\Property::query()
			                                                          ->with(["address.city"])
			                                                          ->findMany($group->properties);

			$groupCompiledProperties = $mobileFlight->properties->toCollection()
			                                                    ->filter(fn(CPCompiledMobileProperty $p) => in_array($p->id, $group->properties));

			if ($groupsCount !== 1 || ($group->name !== null && $group->name !== "remaining")) {
				// Group header
				$this->ws->getStyle($this->ws->getRelativeRange(8))->applyFromArray(XLSXStyleFactory::simpleTableHeader());
				$this->ws->getStyle($this->ws->getRelativeRange(8))->applyFromArray([
					                                                                    "font" => [
						                                                                    'size'  => "14",
						                                                                    "color" => [
							                                                                    "argb" => "FF" . ($group->color ?? "000000"),
						                                                                    ],
					                                                                    ],
					                                                                    "fill" => [
						                                                                    'fillType'   => Fill::FILL_SOLID,
						                                                                    'startColor' => [
							                                                                    'argb' => "FFFFFFFF",
						                                                                    ],
					                                                                    ],
				                                                                    ]);

				$this->ws->printRow([
					                    $group->name === 'remaining' ? Lang::get("contract.group-remaining-properties") : $group->name ?? "",
					                    in_array("zipcode", $this->plan->columns, true) ? Lang::get("contract.table-zipcode") : "",
					                    in_array("location", $this->plan->columns, true) ? Lang::get("contract.table-location") : "",
					                    in_array("impressions", $this->plan->columns, true) ? Lang::get("contract.table-impressions") : "",
					                    in_array("media-value", $this->plan->columns, true) ? Lang::get("contract.table-media-value") : "",
					                    in_array("media-investment", $this->plan->columns, true) ? Lang::get("contract.table-media-investment") : "",
					                    in_array("price", $this->plan->columns, true) ? Lang::get("contract.table-net-investment") : "",
					                    in_array("cpm-lines", $this->plan->columns, true) ? Lang::get("contract.table-cpm") : "",
				                    ]);
			}

			/** @var \Neo\Modules\Properties\Models\Property $property */
			foreach ($groupProperties as $property) {
				/** @var CPCompiledMobileProperty $compiledProperty */
				$compiledProperty = $groupCompiledProperties->firstWhere("id", "=", $property->getKey());


				$this->ws->getStyle($this->ws->getRelativeRange(8))->applyFromArray([
					                                                                    "font" => [
						                                                                    "size" => 12,
						                                                                    'bold' => true,
					                                                                    ],
					                                                                    "fill" => [
						                                                                    'fillType'   => Fill::FILL_SOLID,
						                                                                    'startColor' => [
							                                                                    'argb' => "FFFFFFFF",
						                                                                    ],
					                                                                    ],
				                                                                    ]);

				$this->ws->setRelativeCellFormat("#,##0_-", 3);
				$this->ws->setRelativeCellFormat(NumberFormat::FORMAT_CURRENCY_USD, 4);
				$this->ws->setRelativeCellFormat(NumberFormat::FORMAT_CURRENCY_USD, 5);
				$this->ws->setRelativeCellFormat(NumberFormat::FORMAT_CURRENCY_USD, 6);
				$this->ws->setRelativeCellFormat(NumberFormat::FORMAT_CURRENCY_USD_INTEGER, 7);

				$this->ws->printRow([
					                    $property->actor->name,
					                    in_array("zipcode", $this->plan->columns, true) ? substr($property->address->zipcode, 0, 3) . " " . substr($property->address->zipcode, 3) : "",
					                    in_array("location", $this->plan->columns, true) ? $property->address->city->name : "",
					                    in_array("impressions", $this->plan->columns, true) ? $compiledProperty->impressions : "",
					                    in_array("media-value", $this->plan->columns, true) ? $compiledProperty->media_value : "",
					                    in_array("media-investment", $this->plan->columns, true) ? $compiledProperty->media_value : "",
					                    in_array("price", $this->plan->columns, true) ? $compiledProperty->price : "",
					                    in_array("cpm-lines", $this->plan->columns, true) ? $compiledProperty->cpm : "",
				                    ]);
			}

			$this->ws->getStyle($this->ws->getRelativeRange(8, 1))->applyFromArray([
				                                                                       "fill" => [
					                                                                       'fillType'   => Fill::FILL_SOLID,
					                                                                       'startColor' => [
						                                                                       'argb' => "FFFFFFFF",
					                                                                       ],
				                                                                       ],
			                                                                       ]);

			$this->ws->moveCursor(0, 1);


			// Group footer
			$this->ws->getStyle($this->ws->getRelativeRange(8))->applyFromArray(XLSXStyleFactory::totals());
			$this->ws->setRelativeCellFormat("#,##0_-", 3);
			$this->ws->setRelativeCellFormat(NumberFormat::FORMAT_CURRENCY_USD, 4);
			$this->ws->setRelativeCellFormat(NumberFormat::FORMAT_CURRENCY_USD, 5);
			$this->ws->setRelativeCellFormat(NumberFormat::FORMAT_CURRENCY_USD, 6);
			$this->ws->setRelativeCellFormat(NumberFormat::FORMAT_CURRENCY_USD_INTEGER, 7);

			$impressions = $groupCompiledProperties->sum("impressions");
			$media_value = $groupCompiledProperties->sum("media_value");
			$price       = $groupCompiledProperties->sum("price");
			$cpmPrice    = $groupCompiledProperties->sum("price");
			$cpm         = $impressions > 0 ? $cpmPrice / $impressions * 1000 : 0;

			$this->ws->printRow([
				                    "Total",
				                    "",
				                    "",
				                    in_array("impressions", $this->plan->columns, true) ? $impressions : "",
				                    in_array("media-value", $this->plan->columns, true) ? $media_value : "",
				                    in_array("media-investment", $this->plan->columns, true) ? $media_value : "",
				                    in_array("price", $this->plan->columns, true) ? $price : "",
				                    in_array("cpm", $this->plan->columns, true) ? $cpm : "",
			                    ]);

			$this->ws->getStyle($this->ws->getRelativeRange(8, 2))->applyFromArray([
				                                                                       "fill" => [
					                                                                       'fillType'   => Fill::FILL_SOLID,
					                                                                       'startColor' => [
						                                                                       'argb' => "FFFFFFFF",
					                                                                       ],
				                                                                       ],
			                                                                       ]);

			$this->ws->moveCursor(0, 2);
		}

		$this->ws->getColumnDimension("A")->setAutoSize(true);
		$this->ws->getColumnDimension("B")->setAutoSize(true);
		$this->ws->getColumnDimension("C")->setAutoSize(true);
		$this->ws->getColumnDimension("D")->setAutoSize(true);
		$this->ws->getColumnDimension("E")->setAutoSize(true);
		$this->ws->getColumnDimension("F")->setAutoSize(true);
		$this->ws->getColumnDimension("G")->setAutoSize(true);
		$this->ws->getColumnDimension("H")->setAutoSize(true);
		$this->ws->getColumnDimension("I")->setAutoSize(true);
		$this->ws->getColumnDimension("J")->setAutoSize(true);
		$this->ws->getColumnDimension("K")->setAutoSize(true);
	}

	/**
	 * @inheritDoc
	 */
	public function getName(): string {
		return $this->contractReference ?? 'planner-export';
	}
}
