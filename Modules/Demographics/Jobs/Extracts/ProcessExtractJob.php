<?php
/*
 * Copyright 2024 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ProcessExtractJob.php
 */

namespace Neo\Modules\Demographics\Jobs\Extracts;

use Illuminate\Support\Facades\DB;
use JsonException;
use Neo\Modules\Demographics\Exceptions\InvalidFileFormatException;
use Neo\Modules\Demographics\Exceptions\UnsupportedFileFormatException;
use Neo\Modules\Demographics\Jobs\DemographicJobBase;
use Neo\Modules\Demographics\Jobs\Extracts\DatasetValuesExtractors\DatasetValuesExtractor;
use Neo\Modules\Demographics\Jobs\Extracts\DatasetValuesExtractors\HierarchizedDatasetExtractor;
use Neo\Modules\Demographics\Models\DatasetDatapoint;
use Neo\Modules\Demographics\Models\DatasetVersion;
use Neo\Modules\Demographics\Models\Enums\DatasetStructure;
use Neo\Modules\Demographics\Models\Enums\ReportStatus;
use Neo\Modules\Demographics\Models\Extract;
use Throwable;

/**
 * Processes a single extract.
 * Extract `status` at the end of process will either be `ReportStatus::Done` or `ReportStatus::Failed`
 */
class ProcessExtractJob extends DemographicJobBase {

    public function __construct(protected readonly Extract $extract) {
    }

    protected function onSuccess(mixed $result): void {
        parent::onSuccess($result);

        $this->extract->status          = ReportStatus::Done;
        $this->extract->processed_at    = $this->extract->freshTimestamp();
        $this->extract->metadata->error = null;
        $this->extract->save();
    }

    protected function onFailure(Throwable $exception): void {
        parent::onFailure($exception);

        // Store the error in the report metadata
        $this->extract->status          = ReportStatus::Failed;
        $this->extract->processed_at    = $this->extract->freshTimestamp();
        $this->extract->metadata->error = [
            "error"   => $exception->getCode(),
            "message" => $exception->getMessage(),
            "trace"   => $exception->getTrace(),
        ];
        $this->extract->save();
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

        // Start by validating that the extract still needs to be run
        if ($this->extract->status !== ReportStatus::Pending) {
            // Extract is not pending, stop here
            return true;
        }

        // cleanup any values that might have been left by a previous run
        $this->extract->values()->delete();

        // Report can be run. Mark it as active
        $this->extract->status                    = ReportStatus::Active;
        $this->extract->metadata->executionTimeMs = null;
        $this->extract->metadata->error           = null;
        $this->extract->save();

        /** @var DatasetVersion $datasetVersion */
        $datasetVersion = DatasetVersion::query()->findOrFail($this->extract->template->dataset_version_id);

        /** @var class-string<DatasetValuesExtractor> $extractor */
        $extractor = match ($datasetVersion->structure) {
            DatasetStructure::Hierarchy => HierarchizedDatasetExtractor::class,
            DatasetStructure::Flat      => HierarchizedDatasetExtractor::class,
        };

        $demoDB    = DB::connection("neo_demographics");
        $startTime = microtime(true);

        // For extracts, we want to disable sequential scans to force the hand of the query planner in using indexes
        $demoDB->statement(/** @lang PostgreSQL */ "SET enable_seqscan = OFF");
        $datapoints = DatasetDatapoint::query()
                                      ->where("dataset_version_id", "=", $this->extract->template->dataset_version_id)
                                      ->orderBy("id")
                                      ->lazy(50);

        foreach ($datapoints as $i => $datapoint) {
            console_log("$i $datapoint->code");
            $extractor::extract($demoDB, $datapoint, $this->extract->geographic_report_id, $this->extract->getKey());
        }

        // Get the execution time
        $duration                                 = microtime(true) - $startTime;
        $this->extract->metadata->executionTimeMs = (int)round($duration * 1_000); // seconds to ms

        // Reset our sequential scans setting
        $demoDB->statement(/** @lang PostgreSQL */ "SET enable_seqscan = ON");

        // Done
        return true;
    }
}
