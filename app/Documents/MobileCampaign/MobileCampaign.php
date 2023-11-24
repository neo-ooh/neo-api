<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - MobileCampaign.php
 */

namespace Neo\Documents\MobileCampaign;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Lang;
use Neo\Documents\XLSX\Worksheet;
use Neo\Documents\XLSX\XLSXDocument;
use Neo\Documents\XLSX\XLSXStyleFactory;
use Neo\Modules\Properties\Models\MobileProduct;
use Neo\Modules\Properties\Models\Property;
use Neo\Resources\CampaignPlannerPlan\CompiledPlan\CPCompiledFlight;
use Neo\Resources\CampaignPlannerPlan\CompiledPlan\CPCompiledPlan;
use Neo\Resources\CampaignPlannerPlan\CompiledPlan\Mobile\CPCompiledMobileFlight;
use Neo\Resources\CampaignPlannerPlan\CompiledPlan\Mobile\CPCompiledMobileProperty;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class MobileCampaign extends XLSXDocument {
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
		// Print each mobile flight on its own sheet
		/** @var CPCompiledFlight $flight */
		foreach ($this->plan->flights as $flightIndex => $flight) {
			// Ignore any flight except mobile
			if (!$flight->isMobileFlight()) {
				continue;
			}

			// Create a worksheet for the flight
			$this->worksheet = new Worksheet(null, $flight->name);
			$this->spreadsheet->addSheet($this->worksheet);
			$this->spreadsheet->setActiveSheetIndex(1);
			$this->printMobileCampaign($flight, $flight->getAsMobileFlight(), $flightIndex);
		}


		// Remove the first sheet as it is not being used
		$this->spreadsheet->removeSheetByIndex(0);
		$this->spreadsheet->setActiveSheetIndex(0);
		return true;
	}

	protected function printMobileCampaign(CPCompiledFlight $flight, CPCompiledMobileFlight $mobileFlight, int $index) {
		// Print the flight header
		$productName   = '';
		$mobileProduct = MobileProduct::query()->find($flight->getAsMobileFlight()->product_id);
		if ($mobileProduct) {
			$productName = $mobileProduct["name_" . Lang::locale()];
		}

		$this->ws->getStyle($this->ws->getRelativeRange(11))->applyFromArray(XLSXStyleFactory::flightRow());

		$this->ws->pushPosition();
		$this->ws->moveCursor(5, 0)->mergeCellsRelative(2);
		$this->ws->popPosition();

		$this->ws->getStyle($this->ws->getRelativeRange(8))->applyFromArray([
			                                                                    'font'         => [
				                                                                    'bold'  => true,
				                                                                    'color' => [
					                                                                    'argb' => "FF000000",
				                                                                    ],
				                                                                    'size'  => "13",
				                                                                    "name"  => "Calibri",
			                                                                    ],
			                                                                    "numberFormat" => [
				                                                                    "formatCode" => '#,##0_-',
			                                                                    ],
			                                                                    'alignment'    => [
				                                                                    'vertical' => Alignment::VERTICAL_CENTER,
			                                                                    ],
		                                                                    ]);

		$this->ws->printRow([
			                    $flight->name ?? ("Flight #" . ($index + 1)),
			                    $flight->start_date->toDateString(),
			                    '→',
			                    $flight->end_date->toDateString(),
			                    Lang::get("common.order-type-" . $flight->type->value),
			                    $productName,
		                    ]);


		// Print contact infos
		$this->ws->getStyle($this->ws->getRelativeRange(2, 4))->applyFromArray([
			                                                                       'font' => [
				                                                                       'bold'  => true,
				                                                                       'color' => [
					                                                                       'argb' => "FF000000",
				                                                                       ],
				                                                                       'size'  => "13",
				                                                                       "name"  => "Calibri",
			                                                                       ],
		                                                                       ]);
		$this->ws->printRow([
			                    Auth::user()->name,
			                    Auth::user()->email,
		                    ]);

		$this->ws->moveCursor(0, 1);

		$this->ws->printRow([
			                    $this->plan->contract?->client_name,
		                    ]);

		$this->ws->moveCursor(0, 1);

		// Print the flight infos
		$this->ws->getStyle($this->ws->getRelativeRange(8))->applyFromArray([
			                                                                    'font'         => [
				                                                                    'bold'  => true,
				                                                                    'color' => [
					                                                                    'argb' => "FF000000",
				                                                                    ],
				                                                                    'size'  => "13",
				                                                                    "name"  => "Calibri",
			                                                                    ],
			                                                                    "numberFormat" => [
				                                                                    "formatCode" => '#,##0_-',
			                                                                    ],
			                                                                    'alignment'    => [
				                                                                    'vertical' => Alignment::VERTICAL_CENTER,
			                                                                    ],
		                                                                    ]);
		$this->ws->printRow([
			                    Lang::get("contract.mobile-product"),
			                    Lang::get("contract.mobile-cpm"),
			                    Lang::get("contract.mobile-price"),
			                    Lang::get("contract.mobile-impressions"),
		                    ]);

		$this->ws->setRelativeCellFormat(NumberFormat::FORMAT_CURRENCY_USD_INTEGER, 1);
		$this->ws->setRelativeCellFormat(NumberFormat::FORMAT_CURRENCY_USD, 2);
		$this->ws->setRelativeCellFormat("#,##0_-", 3);
		$this->ws->printRow([
			                    $productName,
			                    $mobileFlight->cpm,
			                    $mobileFlight->price,
			                    $mobileFlight->impressions,
		                    ]);
		$this->ws->moveCursor(0, 1);

		// Print mobile flight parameters
		$this->ws->getStyle($this->ws->getRelativeRange(8))->applyFromArray([
			                                                                    'font'         => [
				                                                                    'bold'  => true,
				                                                                    'color' => [
					                                                                    'argb' => "FF000000",
				                                                                    ],
				                                                                    'size'  => "13",
				                                                                    "name"  => "Calibri",
			                                                                    ],
			                                                                    "numberFormat" => [
				                                                                    "formatCode" => '#,##0_-',
			                                                                    ],
			                                                                    'alignment'    => [
				                                                                    'vertical' => Alignment::VERTICAL_CENTER,
			                                                                    ],
		                                                                    ]);
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

		$this->ws->moveCursor(0, 1);

		$this->ws->getStyle($this->ws->getRelativeRange(8))->applyFromArray([
			                                                                    'font'         => [
				                                                                    'bold'  => true,
				                                                                    'color' => [
					                                                                    'argb' => "FF000000",
				                                                                    ],
				                                                                    'size'  => "13",
				                                                                    "name"  => "Calibri",
			                                                                    ],
			                                                                    "numberFormat" => [
				                                                                    "formatCode" => '#,##0_-',
			                                                                    ],
			                                                                    'alignment'    => [
				                                                                    'vertical' => Alignment::VERTICAL_CENTER,
			                                                                    ],
		                                                                    ]);

		$this->ws->printRow([
			                    Lang::get("contract.mobile-website-retargeting"),
			                    Lang::get("contract.mobile-online-conversion-monitoring"),
			                    Lang::get("contract.mobile-retail-conversion-monitoring"),
		                    ]);
		$this->ws->printRow([
			                    $mobileFlight->website_retargeting ? Lang::get("common.yes") : Lang::get("common.no"),
			                    $mobileFlight->online_conversion_monitoring ? Lang::get("common.yes") : Lang::get("common.no"),
			                    $mobileFlight->retail_conversion_monitoring ? Lang::get("common.yes") : Lang::get("common.no"),
		                    ]);

		$this->ws->moveCursor(0, 2);

		// Print properties list
		$this->ws->getStyle($this->ws->getRelativeRange(8))->applyFromArray([
			                                                                    'font'         => [
				                                                                    'bold'  => true,
				                                                                    'color' => [
					                                                                    'argb' => "FF000000",
				                                                                    ],
				                                                                    'size'  => "13",
				                                                                    "name"  => "Calibri",
			                                                                    ],
			                                                                    "numberFormat" => [
				                                                                    "formatCode" => '#,##0_-',
			                                                                    ],
			                                                                    'alignment'    => [
				                                                                    'vertical' => Alignment::VERTICAL_CENTER,
			                                                                    ],
		                                                                    ]);
		$this->ws->printRow([
			                    Lang::get("contract.mobile-properties"),
		                    ]);

		$propertiesId = $mobileFlight->properties->toCollection()->map(fn(CPCompiledMobileProperty $p) => $p->id);
		$properties   = Property::query()->orderBy("name")->with("address")->whereKey($propertiesId)->lazy(100);

		/** @var Property $property */
		foreach ($properties as $property) {
			$this->ws->printRow([
				                    $property->name,
				                    $property->address->line_1,
				                    $property->address->zipcode,
				                    $property->address->city->name,
				                    $property->address->city->province->slug,
			                    ]);
		}

		if ($mobileFlight->retail_conversion_monitoring) {
			$this->ws->moveCursor(0, 2);

			// Print list of retail locations
			$this->ws->getStyle($this->ws->getRelativeRange(8))->applyFromArray([
				                                                                    'font'         => [
					                                                                    'bold'  => true,
					                                                                    'color' => [
						                                                                    'argb' => "FF000000",
					                                                                    ],
					                                                                    'size'  => "13",
					                                                                    "name"  => "Calibri",
				                                                                    ],
				                                                                    "numberFormat" => [
					                                                                    "formatCode" => '#,##0_-',
				                                                                    ],
				                                                                    'alignment'    => [
					                                                                    'vertical' => Alignment::VERTICAL_CENTER,
				                                                                    ],
			                                                                    ]);
			$this->ws->printRow([
				                    Lang::get("contract.mobile-retail-locations"),
			                    ]);

			/** @var Property $property */
			foreach ($mobileFlight->retail_locations_list as $retailLocation) {
				$this->ws->moveCursor(1, 0);
				$this->ws->mergeCellsRelative(4);
				$this->ws->moveCursor(-1, 0);
				$this->ws->printRow([
					                    $retailLocation->name,
					                    $retailLocation->address,
				                    ]);
			}
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
		return 'mobile-campaign-export';
	}
}
