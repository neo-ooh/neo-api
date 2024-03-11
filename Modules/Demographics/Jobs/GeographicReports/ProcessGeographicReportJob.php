<?php
/*
 * Copyright 2024 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ProcessGeographicReportJob.php
 */

namespace Neo\Modules\Demographics\Jobs\GeographicReports;

use Illuminate\Support\Facades\DB;
use JsonException;
use MatanYadaev\EloquentSpatial\Objects\Geometry;
use Neo\Modules\Demographics\Exceptions\InvalidFileFormatException;
use Neo\Modules\Demographics\Exceptions\UnsupportedFileFormatException;
use Neo\Modules\Demographics\Jobs\DemographicJobBase;
use Neo\Modules\Demographics\Jobs\GeographicReports\Processors\CustomAreaProcessor;
use Neo\Modules\Demographics\Jobs\GeographicReports\Processors\EnvironicsCustomerFileProcessor;
use Neo\Modules\Demographics\Jobs\GeographicReports\Processors\RadiusAreaProcessor;
use Neo\Modules\Demographics\Models\Area;
use Neo\Modules\Demographics\Models\AreaType;
use Neo\Modules\Demographics\Models\Enums\GeographicReportTemplateAreaType;
use Neo\Modules\Demographics\Models\Enums\GeographicReportType;
use Neo\Modules\Demographics\Models\Enums\ReportStatus;
use Neo\Modules\Demographics\Models\GeographicReport;
use Neo\Modules\Demographics\Models\GeographicReportTemplate;
use Neo\Modules\Demographics\Models\GeographicReportValue;
use Neo\Modules\Demographics\Structures\GeographicDataEntry;
use Neo\Modules\Properties\Models\Property;
use RuntimeException;
use Spatie\LaravelData\Optional;
use Storage;
use Throwable;

class ProcessGeographicReportJob extends DemographicJobBase {

    protected $tmpFile;

    public function __construct(private readonly GeographicReport $report) {
    }

    protected function onSuccess(mixed $result): void {
        parent::onSuccess($result);

        $this->report->status = ReportStatus::Done;
        $this->report->processed_at = $this->report->freshTimestamp();
        $this->report->metadata->error = new Optional();
        $this->report->save();
    }

    protected function onFailure(Throwable $exception): void {
        parent::onFailure($exception);

        // Store the error in the report metadata
        $this->report->status = ReportStatus::Failed;
        $this->report->processed_at = $this->report->freshTimestamp();
        $this->report->metadata->error = [
            "error" => $exception->getCode(),
            "message" => $exception->getMessage(),
            "trace" => $exception->getTrace(),
        ];
        $this->report->save();
    }

    /**
     * @throws InvalidFileFormatException
     * @throws UnsupportedFileFormatException
     * @throws JsonException
     */
    public function run(): mixed {
        DB::disableQueryLog();
        DB::connection()->unsetEventDispatcher();
        DB::connection("neo_demographics")->disableQueryLog();
        DB::connection("neo_demographics")->unsetEventDispatcher();

        // Start by validating that the report still needs to be run
        if ($this->report->status !== ReportStatus::Pending) {
            // Report is not pending, stop here
            return true;
        }

        $this->report->values()->delete();

        // Report can be run. Mark it as active
        $this->report->status = ReportStatus::Active;
        $this->report->save();

        /** @var GeographicReportTemplate $template */
        $template = $this->report->template;
        // Now, we branch based on the report template type

        /** @var GeographicDataReader $reader */
        $reader = match ($template->type) {
            GeographicReportType::Area      => $this->getReaderForAreaReport(),
            GeographicReportType::Customers => $this->getReaderForCustomerReport(),
        };

        $areaTypes = AreaType::query()->get()->mapWithKeys(fn(AreaType $type) => [$type->code => $type->id]);

        $batchSize = 800;
        $rowsCount = 0;

        /** @var GeographicDataEntry[] $entries */
        foreach ($this->chunkValues($reader->getEntries(), $batchSize) as $entries) {
            // First, prepare the Area for all entries

            $areasDefinitions = collect();
            $areas            = [];

            /** @var GeographicDataEntry $entry */
            foreach ($entries as $entry) {
                if ($entry->geography_id !== null) {
                    continue;
                }

                $areaTypeId = $areaTypes[$entry->geography_type_code] ?? null;
                if (!$areaTypeId) {
                    $areaType = new AreaType([
                                                 "code" => $entry->geography_type_code,
                                             ]);
                    $areaType->save();
                    $areaTypes[$entry->geography_type_code] = $areaType->getKey();
                    $areaTypeId                             = $areaType->getKey();
                }

                $areasDefinitions[] = [
                    "type_id" => $areaTypeId,
                    "code"    => $entry->geography_code,
                ];
            }

            if (count($areasDefinitions) > 0) {
                // Create/Fetch all areas
                Area::query()->insertOrIgnore($areasDefinitions->toArray());
                $inValues = $areasDefinitions->map(fn(array $v) => "(" . $v["type_id"] . ", '" . $v["code"] . "')")
                                             ->join(",");
                $areas    = Area::query()
                                ->select(["id", "type_id", "code"])
                                ->whereRaw("(type_id, code) IN (" . $inValues . ")", [])
                                ->get()
                                ->mapWithKeys(fn(Area $area) => [$area->type_id . "|" . $area->code => $area->getKey()]);
            }

            // Prepare all the rows
            $rows = [];

            /** @var GeographicDataEntry $entry */
            foreach ($entries as $entry) {
                $rows[] = [
                    "report_id"        => $this->report->getKey(),
                    "area_id"          => $entry->geography_id ?? $areas[$areaTypes[$entry->geography_type_code] . "|" . $entry->geography_code],
                    "geography_weight" => $entry->weight,
                    "metadata"         => json_encode($entry->metadata, JSON_THROW_ON_ERROR),
                ];
            }

            GeographicReportValue::query()->insert($rows);

            $rowsCount += count($rows);
        }

        return true;
    }

    protected function getReaderForAreaReport(): GeographicDataReader|null {
        // For custom area, we can directly process it
        if($this->report->metadata->area_type === GeographicReportTemplateAreaType::Custom) {
            $area = Geometry::fromArray($this->report->metadata->area);
            return new CustomAreaProcessor($area);
        }

        // We first need to get the actual area.
        // For a radius-based area, we can simply generate it, for isochrones we need to call an external service

        $property = Property::query()->findOrFail($this->report->property_id);
        $address  = $property->address;

        if (!$address?->geolocation) {
            throw new RuntimeException("Cannot generate geographic report for property without geolocation. (Property #{$property->getKey()}: {$property->actor->name}");
        }

        if ($this->report->metadata->area_type === GeographicReportTemplateAreaType::Radius) {
            return new RadiusAreaProcessor($address->geolocation, $this->report->metadata->distance * 1_000);
        }

        return null;
    }

    /**
     * @throws UnsupportedFileFormatException
     */
    protected function getReaderForCustomerReport(): GeographicDataReader {
        // Start by getting a temporary copy of the source file
        $this->tmpFile = tmpfile();
        $filePath      = stream_get_meta_data($this->tmpFile)['uri'];
        stream_copy_to_stream(Storage::disk("public")->readStream($this->report->source_file_path), $this->tmpFile);

        return match ($this->report->metadata->source_file_format) {
            "environics" => new EnvironicsCustomerFileProcessor($filePath),
            default      => throw new UnsupportedFileFormatException("Unknown customer file format: " . $this->report->metadata->source_file_format),
        };
    }

    protected function chunkValues(iterable $values, int $chunkSize) {
        $chunk = [];
        $count = 0;
        foreach ($values as $value) {
            $chunk[] = $value;
            $count++;

            if ($count >= $chunkSize) {
                yield $chunk;

                $chunk = [];
                $count = 0;
            }
        }

        if ($count > 0) {
            yield $chunk;
        }
    }
}
