<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - BroadSignAudienceFile.php
 */

namespace Neo\Documents\BroadSignAudienceFile;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Neo\Documents\DocumentFormat;
use Neo\Documents\XLSX\XLSXDocument;
use Neo\Exceptions\InvalidOpeningHoursException;
use Neo\Exceptions\LocationNotAssociatedWithProductException;
use Neo\Modules\Broadcast\Exceptions\InvalidBroadcasterAdapterException;
use Neo\Modules\Broadcast\Exceptions\InvalidBroadcastResource;
use Neo\Modules\Broadcast\Models\Location;
use Neo\Modules\Broadcast\Services\BroadcasterAdapterFactory;
use Neo\Modules\Broadcast\Services\BroadcasterType;
use Neo\Modules\Broadcast\Services\BroadSign\BroadSignAdapter;
use Neo\Modules\Broadcast\Services\Resources\Frame;
use Neo\Modules\Properties\Models\OpeningHours;
use Neo\Modules\Properties\Models\Product;
use Neo\Modules\Properties\Models\Property;
use Neo\Modules\Properties\Services\Resources\DayOperatingHours;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Writer\BaseWriter;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

class BroadSignAudienceFile extends XLSXDocument {

	protected Location $location;
	protected Product $product;
	protected Property $property;
	/**
	 * @var Frame[]
	 */
	protected array $frames;

	/**
	 * @param Location $data
	 * @return bool
	 * @throws InvalidBroadcasterAdapterException
	 * @throws InvalidBroadcastResource
	 * @throws LocationNotAssociatedWithProductException
	 */
	protected function ingest($data): bool {
		$this->location = $data;

		// As of 2022-10-31, only BroadSign is supported
		// Validated the location is for a supported broadcaster
		/** @var BroadSignAdapter $broadcaster */
		$broadcaster = BroadcasterAdapterFactory::makeForNetwork($this->location->network_id);

		if ($broadcaster->getBroadcasterType() !== BroadcasterType::BroadSign) {
			throw new InvalidBroadcasterAdapterException($broadcaster->getBroadcasterType()->value);
		}

		$product = $this->location->products()
		                          ->where("is_bonus", "=", false)
		                          ->with([
			                                 "impressions_models",
			                                 "loop_configurations",
			                                 "category.impressions_models",
			                                 "category.loop_configurations",
		                                 ])
		                          ->withCount("locations")
		                          ->first();

		if ($product === null) {
			Log::error("Location {$this->location->getKey()} ({$this->location->name}) is not associated with any product.");
			throw new LocationNotAssociatedWithProductException();
		}

		$this->product = $product;

		/** @var Property $property */
		$property       = Property::query()
		                          ->with(["opening_hours", "traffic.weekly_data"])
		                          ->find($this->product->property_id);
		$this->property = $property;

		$this->property->rolling_weekly_traffic = $this->property->traffic->getRollingWeeklyTraffic();

		// Load all the frames, of the display unit, and load their loop policies as well
		$this->frames = $broadcaster->getLocationFrames($this->location->toExternalBroadcastIdResource());

		return true;
	}

	/**
	 * @inheritDoc
	 * @throws InvalidOpeningHoursException
	 */
	public function build(): bool {
		$this->ws->printRow([
			                    "Display Unit Id",
			                    "Frame Id",
			                    "Start Date",
			                    "End Date",
			                    "Start Time",
			                    "End Time",
			                    "Monday",
			                    "Tuesday",
			                    "Wednesday",
			                    "Thursday",
			                    "Friday",
			                    "Saturday",
			                    "Sunday",
			                    "Total Impressions per hour",
		                    ]);
		// For each frame of the display unit, for every day of the week, we have to calculate the hourly impressions.
		$datePointer = Carbon::now()->startOf("week");

		// Define how many days should be generated. Here: a month
		$endBoundary = $datePointer->clone()->addMonth();

		// Dump a fail-over row for every frame
		foreach ($this->frames as $frame) {
			$this->ws->printRow([
				                    $this->location->external_id,
				                    $frame->external_id,
				                    "",
				                    "",
				                    "",
				                    "",
				                    1,
				                    1,
				                    1,
				                    1,
				                    1,
				                    1,
				                    1,
				                    1,
			                    ]);
		}

		// Calculate how many minutes the property is open on each day
		$openLengthsMinutes = collect();
		/**
		 * @var Collection<int, DayOperatingHours> Resolved opening hours. Default the 24 hours if missing
		 */
		$hours = collect();

		for ($weekday = 1; $weekday <= 7; $weekday++) {
			/** @var OpeningHours|null $dayHours */
			$dayHours  = $this->property->opening_hours->firstWhere("weekday", "===", $weekday);
			$startTime = ($dayHours ? Carbon::createFromTimeString($dayHours->open_at) : Carbon::createFromTime(0, 0))->startOfMinute();
			$endTime   = ($dayHours ? Carbon::createFromTimeString($dayHours->close_at) : Carbon::createFromTime(23, 59))->startOfMinute();
			$endTime->setDateFrom($startTime);
			$isClosed = $dayHours?->is_closed ?? false;

			$hours[$weekday] = new DayOperatingHours(
				day            : $weekday,
				is_closed      : $isClosed,
//				start_at       : $startTime->format('H:i:s'),
//				end_at         : $endTime->format('H:i:s'),
				start_at       : "00:00:00",
				end_at         : "23:59:00",
				open_length_min: $isClosed ? 0 : $startTime->diffInMinutes($endTime, absolute: true),
			);

			$openLengthsMinutes[$weekday] = $startTime->diffInMinutes($endTime, true);
		}

		// Make sure all the lengths are valid
		if ($openLengthsMinutes->some(fn($length) => $length === 0)) {
			throw new InvalidOpeningHoursException($this->property);
		}

		$openDaysPerWeek = $hours->where("is_closed", "=", false)->count();

		// For each week
		do {
            // Around the new year, week number calculation get funky.
            // We make sure our week index is always between 1 and 53.
            $weekIndex = max(1, $datePointer->weekOfYear - 1);
			// Get the week traffic
			$dailyTraffic = floor($this->property->rolling_weekly_traffic[$weekIndex] / $openDaysPerWeek);

			// For each day of the week
			for ($i = 0; $i < 7; $i++) {
				$weekday = $i + 1;
				$date    = $datePointer->clone()->addDays($i);

				// Get the loop configuration
				$loopConfiguration = $this->product->getLoopConfiguration($date);

				// Get the appropriate impressions model
				$impressionsModel = $this->product->getImpressionModel($date);

				// If the impressions model or the loop configuration is missing, ignore
				if (!$impressionsModel || !$loopConfiguration) {
					continue;
				}

				$el                = new ExpressionLanguage();
				$impressionsPerDay = $el->evaluate($impressionsModel->formula, array_merge(
					[
						"traffic"       => $dailyTraffic,
						"faces"         => $this->product->quantity,
						"spots"         => 1,
						"loopLengthMin" => $loopConfiguration->loop_length_ms / (1_000 * 60), // ms to minutes
					],
					$impressionsModel->variables
				));

				// Because the impression for the product is spread on all the display unit attached to it,
				// we divide the number of impressions by the number of display unit for the product
				$impressionsPerDay /= $this->product->locations_count;

				/** @var Frame $frame */
				foreach ($this->frames as $frame) {
					/**
					 * How many times the loop runs in one day, or,
					 * How many times a single ad will be shown in a day
					 */
					$loopsPerDay = $openLengthsMinutes[$weekday] * 60_000 / $loopConfiguration->loop_length_ms;

					/**
					 * How many impressions a single play of an ad is going to generate
					 */
					$impressionsPerPlay = $impressionsPerDay / $loopsPerDay;

					/**
					 * How many loops are played in an hour
					 */
					$playsPerHour = 3_600_000 /* 3600 * 1000 (ms) */ / $loopConfiguration->loop_length_ms;

					/**
					 * How many impressions are generated in an hour
					 */
					$impressionsPerHour = ceil($impressionsPerPlay * $playsPerHour) + 2; // This +2 is to be `extra-generous` on the number of impressions delivered

					$this->ws->printRow([
						                    $this->location->external_id,
						                    $frame->external_id,
						                    $date->toDateString(),
						                    $date->clone()->endOfDay()->toDateString(),
						                    $hours[$weekday]->start_at,
						                    $hours[$weekday]->end_at,
						                    $weekday === 1 ? 1 : 0, // Monday
						                    $weekday === 2 ? 1 : 0, // Tuesday
						                    $weekday === 3 ? 1 : 0, // Wednesday
						                    $weekday === 4 ? 1 : 0, // Thursday
						                    $weekday === 5 ? 1 : 0, // Friday
						                    $weekday === 6 ? 1 : 0, // Saturday
						                    $weekday === 7 ? 1 : 0, // Sunday
						                    $impressionsPerHour,
					                    ]);

					// We have to set the start and end date explicitly, otherwise PHPSpreadsheet is going to remove the leading zero fo the opening hours
					$this->ws->setCellValueExplicit([5, $this->ws->getCursorRow() - 1],
					                                $hours[$weekday]->start_at,
					                                DataType::TYPE_STRING);
					$this->ws->setCellValueExplicit([6, $this->ws->getCursorRow() - 1],
					                                $hours[$weekday]->end_at,
					                                DataType::TYPE_STRING);
				}
			}

			$datePointer->addWeek();
		} while ($datePointer->isBefore($endBoundary));

		return true;
	}

	public function format(): DocumentFormat {
		return DocumentFormat::CSV;
	}

	/**
	 * @inheritDoc
	 */
	public function getName(): string {
		return "AudienceFile-{$this->location->external_id}";
	}

	public function customizeOutput(BaseWriter $writer): void {
		$writer->setPreCalculateFormulas(false);

		// Writer is actually a Csv writer as we export only to csv
		$writer->setEnclosure('');
	}
}
