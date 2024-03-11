<?php
/*
 * Copyright 2024 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ImportEnvironicsDatasetCommand.php
 */

namespace Neo\Modules\Demographics\Console\Commands\Importers;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Neo\Modules\Demographics\Models\Area;
use Neo\Modules\Demographics\Models\AreaType;
use Neo\Modules\Demographics\Models\DatasetDatapoint;
use Neo\Modules\Demographics\Models\DatasetValue;
use Neo\Modules\Demographics\Models\DatasetVersion;

class ImportEnvironicsDatasetCommand extends Command {
    protected $signature = 'import:environics-dataset {dataset_version_id} {filepath} {--skip=0}';

    protected $description = 'Import an Environics dataset flat file under the specified dataset version id';

    public function handle(): void {
        ini_set("memory_limit", '2048m');

        DB::disableQueryLog();
        DB::connection()->unsetEventDispatcher();
        DB::connection("neo_demographics")->disableQueryLog();
        DB::connection("neo_demographics")->unsetEventDispatcher();

        // Get a handle to the file
        $filePath = $this->argument("filepath");
        $handle   = fopen($filePath, 'rb');

        if (!$handle) {
            $this->error("Could not open file $filePath");
            return;
        }

        $this->info("File opened successfully");

        // Validate the specified dataset version exist
        $versionId = $this->argument("dataset_version_id");
        $version   = DatasetVersion::query()->find($versionId);

        if (!$version) {
            $this->error("Unknown dataset version with id $versionId");
            return;
        }

        $this->info("Dataset version validated");
        $this->comment("Loading datapoints...");

        // Parse all the headers
        $headers = explode(",", fgets($handle));

        $areaTypes = AreaType::query()->get()->mapWithKeys(fn(AreaType $type) => [$type->code => $type->id]);

        $datapoints = [];

        $headersCount = count($headers);
        $progressBar  = $this->getOutput()->createProgressBar($headersCount - 2);
        $progressBar->setFormat("%current%/%max% [%bar%] %percent:3s%% %message%");
        $progressBar->start();

        // First two headers are area identification, we can ignore
        for ($i = 2, $iMax = $headersCount; $i < $iMax; $i++) {
            $datapointCode = $headers[$i];

            $progressBar->setMessage($datapointCode);
            $progressBar->advance();

            $datapoint = DatasetDatapoint::query()->firstOrCreate([
                                                                      "dataset_version_id" => $versionId,
                                                                      "code"               => $datapointCode,
                                                                  ], [
                                                                      "label_en" => "",
                                                                      "label_fr" => "",
                                                                  ]);

            $datapoints[$i - 2] = $datapoint->getKey();
        }

        $progressBar->finish();

        $this->info("Datapoints loaded");

        $this->info("Parsing areas and values...");

        $start = $this->option("skip");
        $l = 0;
        
        // Now we parse all the other lines and store their data
        while (($line = fgets($handle)) !== false) {
            if($l < $start) {
                $l++;
                continue;
            }

            $values       = explode(",", $line);
            $areaCode     = array_shift($values);
            $areaTypeCode = array_shift($values);

            if($l % 25 === 0) {
                $this->info("[$l] $areaTypeCode $areaCode");
            }

            // Resolve area and its type
            $areaTypeId = $areaTypes[$areaTypeCode] ?? null;
            if (!$areaTypeId) {
                $areaType = new AreaType([
                                             "code" => $areaTypeCode,
                                         ]);
                $areaType->save();
                $areaTypes[$areaTypeCode] = $areaType->getKey();
                $areaTypeId               = $areaType->getKey();
            }

            $area = Area::query()->firstOrCreate([
                                                     "type_id" => $areaTypeId,
                                                     "code"    => $areaCode,
                                                 ]);

            // Prepare inserts
            $insertValues = [];
            for ($i = 0, $iMax = count($values); $i < $iMax; $i++) {
                $value = $values[$i];

                $insertValues[] = [
                    "datapoint_id" => $datapoints[$i],
                    "area_id"      => $area->getKey(),
                    "value"        => (double)$value,
                ];
            }

            // Perform inserts
            DatasetValue::query()->upsert($insertValues, ["datapoint_id", "area_id"]);

            $l++;
        }

        $this->info("Done!");
    }
}
