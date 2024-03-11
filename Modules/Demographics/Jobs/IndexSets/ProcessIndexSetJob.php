<?php
/*
 * Copyright 2024 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ProcessIndexSetJob.php
 */

namespace Neo\Modules\Demographics\Jobs\IndexSets;

use Illuminate\Support\Facades\DB;
use JsonException;
use Neo\Modules\Demographics\Exceptions\InvalidFileFormatException;
use Neo\Modules\Demographics\Exceptions\UnsupportedFileFormatException;
use Neo\Modules\Demographics\Jobs\DemographicJobBase;
use Neo\Modules\Demographics\Models\Enums\ReportStatus;
use Neo\Modules\Demographics\Models\IndexSet;
use Neo\Modules\Demographics\Models\IndexSetValue;
use Throwable;

/**
 * Processes a single index set.
 * Index Set `status` at the end of process will either be `ReportStatus::Done` or `ReportStatus::Failed`
 */
class ProcessIndexSetJob extends DemographicJobBase {

    public function __construct(protected readonly IndexSet $set) {
    }

    protected function onSuccess(mixed $result): void {
        parent::onSuccess($result);

        $this->set->status          = ReportStatus::Done;
        $this->set->processed_at    = $this->set->freshTimestamp();
        $this->set->metadata->error = null;
        $this->set->save();
    }

    protected function onFailure(Throwable $exception): void {
        parent::onFailure($exception);

        // Store the error in the report metadata
        $this->set->status          = ReportStatus::Failed;
        $this->set->processed_at    = $this->set->freshTimestamp();
        $this->set->metadata->error = [
            "error"   => $exception->getCode(),
            "message" => $exception->getMessage(),
            "trace"   => $exception->getTrace(),
        ];
        $this->set->save();
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
        if ($this->set->status !== ReportStatus::Pending) {
            // Extract is not pending, stop here
            return true;
        }

        // cleanup any values that might have been left by a previous run
        $this->set->values()->delete();

        // Report can be run. Mark it as active
        $this->set->status                    = ReportStatus::Active;
        $this->set->metadata->executionTimeMs = null;
        $this->set->metadata->error           = null;
        $this->set->save();

        // Fetch all the values for the set in a single query, batched of course
        $batchSize = 250;
        $offset = 0;

        do {
            console_log("Processing values " . $offset . " to " . ($offset+$batchSize) . "...");
            $values = DB::connection("neo_demographics")
                ->select(<<<EOF
                    SELECT
                        ? as "set_id",
                        evp.datapoint_id as "datapoint_id",
                        evp.value as "primary_value",
                        evr.value as "reference_value",
                        ROUND(evp.value / evr.value * 100) as "index"
                    FROM extracts_values evp
                    JOIN extracts_values evr ON evp.datapoint_id = evr.datapoint_id
                    WHERE evp.extract_id = ?
                    AND evr.extract_id = ?
                    ORDER BY evp.datapoint_id
                    LIMIT ? OFFSET ?;
                    EOF
                    , [
                        $this->set->getKey(),
                        $this->set->primary_extract_id,
                        $this->set->reference_extract_id,
                        $batchSize,
                        $offset
                      ]);
            
            // Insert values
            IndexSetValue::query()->insert(array_map(fn($value) => (array)$value, $values));

            $retrievedValuesCount = count($values);
            $offset += $retrievedValuesCount;
        } while($retrievedValuesCount === $batchSize);

        console_log("done");
        // Done
        return true;
    }
}
