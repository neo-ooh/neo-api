<?php
/*
 * Copyright 2024 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - IngestZipcodesCoordinatesCommand.php
 */

namespace Neo\Modules\Demographics\Console\Commands\OneOff;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use MatanYadaev\EloquentSpatial\Objects\Point;
use Neo\Modules\Demographics\Models\Area;
use Neo\Modules\Demographics\Models\AreaType;

class IngestZipcodesCoordinatesCommand extends Command {
    protected $signature = 'one-off:ingest-zipcodes-coordinates {sourceFile} {--skip=0}';

    protected $description = 'Command description';

    public function handle(): void {
        ini_set("memory_limit", '2048m');

        DB::disableQueryLog();
        DB::connection()->unsetEventDispatcher();
        DB::connection("neo_demographics")->disableQueryLog();
        DB::connection("neo_demographics")->unsetEventDispatcher();

        // Get a handle on the file
        $fileHandle = fopen($this->argument("sourceFile"), "rb");

        // Ignore first line/headers
        fgets($fileHandle);

        $fsalduType = AreaType::query()->where("code", "=", "FSALDU")->firstOrFail();

        $start = $this->option("skip");
        $i = 0;

        while(($line = fgets($fileHandle)) !== false) {
            if($i < $start) {
                $i++;
                continue;
            }
            $values = str_replace("\"", "", explode(",", $line));

            $zipCode = str_replace(" ", "", $values[0]);
            $lat = (float)$values[4];
            $lng = (float)$values[5];

            Area::query()
                ->where("type_id", "=", $fsalduType->getKey())
                ->where("code", "=", $zipCode)
                ->update([
                    "geolocation" => new Point($lat, $lng)
                         ]);

            $i++;

            if($i % 50 === 0) {
                $this->info("[$i] $zipCode");
            }

        }
    }
}
