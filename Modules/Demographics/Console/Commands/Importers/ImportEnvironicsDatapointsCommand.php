<?php
/*
 * Copyright 2024 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ImportEnvironicsDatapointsCommand.php
 */

namespace Neo\Modules\Demographics\Console\Commands\Importers;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Neo\Modules\Demographics\Models\DatasetDatapoint;

class ImportEnvironicsDatapointsCommand extends Command {
    protected $signature = 'import:environics-datapoints {dataset_version_id} {filepath}';

    protected $description = 'takes an environics dataset metadata file to populate the datapoints of the specified dataset version';

    public function handle(): void {
        DB::disableQueryLog();
        DB::connection()->unsetEventDispatcher();
        DB::connection("neo_demographics")->disableQueryLog();
        DB::connection("neo_demographics")->unsetEventDispatcher();

        // Get a handle on the file
        $filePath = $this->argument("filepath");
        $handle   = fopen($filePath, 'rb');

        if (!$handle) {
            $this->error("Could not open file $filePath");
            return;
        }

        $this->info("File opened successfully");

        $datasetVersionId = (int)$this->argument("dataset_version_id");

        // List our column indexes
        $datapointCodeIndex  = 1;
        $datapointLabelIndex = 2;
        $datapointRootIndex  = 8;

        // Ignore first row, that's the headers
        fgets($handle);

        $entries = [];

        // Now loop over all the rows in the file
        while (($line = fgets($handle)) !== false) {
            $values = str_getcsv($line, ",", "\"");

            $datapointCode  = trim($values[$datapointCodeIndex]);
            $datapointLabel = trim($values[$datapointLabelIndex]);
            $datapointRoot  = trim(str_replace(["[", "]"], "", $values[$datapointRootIndex]));


            if ($datapointRoot === "") {
                $datapointRoot = null;
            }

            $this->info("$datapointCode | $datapointLabel | $datapointRoot");

            $rdp = null;
            if ($datapointRoot !== null) {
                $rdp = DatasetDatapoint::query()
                                       ->select("id")
                                       ->where("dataset_version_id", "=", $datasetVersionId)
                                       ->where("code", "=", $datapointRoot)
                                       ->first()?->id;
            }

            DatasetDatapoint::query()
                            ->upsert([
                                         "dataset_version_id" => $datasetVersionId,
                                         "code"               => $datapointCode,
                                         "label_en"              => $datapointLabel,
                                         "label_fr"              => $datapointLabel,
                                         "reference_datapoint_id"  => $rdp,
                                     ],
                                     ["dataset_version_id", "code"]
                            );
        }
        
//        DatasetDatapoint::query()
//            ->upsert($entries, ["dataset_version_id", "code"]);
    }
}
