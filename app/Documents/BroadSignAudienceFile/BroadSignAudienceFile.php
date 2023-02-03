<?php
/*
 * Copyright 2022 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - BroadSignAudienceFile.php
 */

namespace Neo\Documents\BroadSignAudienceFile;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Neo\Documents\DocumentFormat;
use Neo\Documents\XLSX\XLSXDocument;
use Neo\Exceptions\InvalidOpeningHoursException;
use Neo\Exceptions\LocationNotAssociatedWithProductException;
use Neo\Models\OpeningHours;
use Neo\Models\Product;
use Neo\Models\Property;
use Neo\Modules\Broadcast\Exceptions\InvalidBroadcasterAdapterException;
use Neo\Modules\Broadcast\Exceptions\InvalidBroadcastResource;
use Neo\Modules\Broadcast\Models\Location;
use Neo\Modules\Broadcast\Services\BroadcasterAdapterFactory;
use Neo\Modules\Broadcast\Services\BroadcasterType;
use Neo\Modules\Broadcast\Services\BroadSign\BroadSignAdapter;
use Neo\Modules\Broadcast\Services\Resources\Frame;
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

        $this->property->rolling_weekly_traffic = $this->property->traffic->getRollingWeeklyTraffic($this->property->network_id);

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

        // Dump a failover row for every frame
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

        /**
         * How many minutes the property is open on each day
         */
        $openLengthsMinutes = $this->property->opening_hours->mapWithKeys(/**
         * @param OpeningHours $hours
         * @return array
         */ fn(OpeningHours $hours) => [$hours->weekday => $hours->open_at->diffInMinutes($hours->close_at, true)]);

        // Make sure all the lengths are valid
        if ($openLengthsMinutes->some(fn($length) => $length === 0)) {
            throw new InvalidOpeningHoursException($this->property);
        }

        // For each week
        do {
            // Get the week traffic
            $dailyTraffic = floor($this->property->rolling_weekly_traffic[(int)strftime("%W", $datePointer->timestamp)] / 7);

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
                        "traffic" => $dailyTraffic,
                        "faces"   => $this->product->quantity,
                        "spots"   => 1,
                    ],
                    $impressionsModel->variables
                ));

                // Because the impression for the product is spread on all the display unit attached to it,
                // we divide the number of impressions by the number of display unit for the product
                $impressionsPerDay /= $this->product->locations_count;

                /** @var OpeningHours|null $hours */
                $hours = $this->property->opening_hours->firstWhere("weekday", "=", $weekday);

                // If no hours, no calculations
                if (!$hours) {
                    continue;
                }

                /** @var Frame $frame */
                foreach ($this->frames as $frame) {
                    /**
                     * How many times the loop runs in one day, or,
                     * How many times a single ad will be shown in a day
                     */
                    $loopsPerDay = $openLengthsMinutes[$weekday] * 60_000 / $loopConfiguration->loop_length_ms;

                    /**
                     * How many impressions a single play of an ad is gonna generate
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
                                            $hours->open_at->startOf('minute')->format('H:m:s'),
                                            $hours->close_at->endOf('minute')->format('H:m:s'),
                                            $i === 0 ? 1 : 0, // Monday
                                            $i === 1 ? 1 : 0, // Tuesday
                                            $i === 2 ? 1 : 0, // Wednesday
                                            $i === 3 ? 1 : 0, // Thursday
                                            $i === 4 ? 1 : 0, // Friday
                                            $i === 5 ? 1 : 0, // Saturday
                                            $i === 6 ? 1 : 0, // Sunday
                                            $impressionsPerHour,
                                        ]);

                    // We have to set the start and end date explicitly, otherwise PHPSpreadsheet is gonna remove the leading zero fo the opening hours
                    $this->ws->setCellValueExplicit([5, $this->ws->getCursorRow() - 1],
                                                    $hours->open_at->startOf('minute')->format('H:m:s'),
                                                    DataType::TYPE_STRING);
                    $this->ws->setCellValueExplicit([6, $this->ws->getCursorRow() - 1],
                                                    $hours->close_at->endOf('minute')->format('H:m:s'),
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

    public function customizeOutput(BaseWriter $writer) {
        $writer->setPreCalculateFormulas(false);

        // Writer is actually a Csv writer as we export only to csv
        $writer->setEnclosure('');
    }
}
