<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - PopulateCensusDivisionsCommand.php
 */

namespace Neo\Console\Commands\Data;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use JsonMachine\Items;
use MatanYadaev\EloquentSpatial\Objects\MultiPolygon;
use Neo\Models\CensusDivision;

class PopulateCensusDivisionsCommand extends Command {
    protected $signature = 'data:populate-census-divisions {census-file} {skip}';

    protected $description = 'Read a census 2021 Census divisions file in GeoJson and populates the `census-divisions` table with it';

    protected $provincesSlug = [
        10 => "NL",
        11 => "PE",
        12 => "NS",
        13 => "NB",
        24 => "QC",
        35 => "ON",
        46 => "MB",
        47 => "SK",
        48 => "AB",
        59 => "BC",
        60 => "YT",
        61 => "NT",
        62 => "NU",
    ];

    public function handle(): void {
        $filePath = $this->argument("census-file");

        $divisions = Items::fromFile($filePath, ["pointer" => "/features",]);

//        $this->info(iterator_count($subdivisions));

        foreach ($divisions as $key => $division) {
//            if ($division->properties->CDUID <= 6208087) {
//                $this->comment("[skipped] " . $key . " - " . $subdivision->properties->CDUID . " - " . $subdivision->properties->CDNAME . ", " . $this->provincesSlug[$subdivision->properties->PRUID]);
//                continue;
//            }

            if ($key < $this->argument("skip")) {
                $this->comment("[skipped] " . $key . " - " . $division->properties->CDUID . " - " . $division->properties->CDNAME . ", " . $this->provincesSlug[$division->properties->PRUID]);
                continue;
            }

            try {
                CensusDivision::query()->insertOrIgnore([
                                                            "id"                => $division->properties->CDUID,
                                                            "census"            => 2021,
                                                            "name"              => $division->properties->CDNAME,
                                                            "type"              => $division->properties->CDTYPE,
                                                            "province"          => $this->provincesSlug[$division->properties->PRUID],
                                                            "landarea_sqkm"     => $division->properties->LANDAREA,
                                                            "dissemination_uid" => $division->properties->DGUID,
                                                            "geometry"          => DB::raw("MultiPolygonFromText('" . MultiPolygon::fromJson(json_encode($division->geometry))
                                                                                                                                  ->toWkt() . "')"),
                                                        ]);
                $this->info("[done] " . $key . " - " . $division->properties->CDUID . " - " . $division->properties->CDNAME . ", " . $this->provincesSlug[$division->properties->PRUID]);
            } catch (Exception $e) {
                $this->error("[failed] " . $key . " - " . $division->properties->CDUID . " - " . $division->properties->CDNAME . ", " . $this->provincesSlug[$division->properties->PRUID]);
            }
        }

        // Failed on last run:
        // 2498 - Minganie--Le Golfe-du-Saint-Laurent, QC
        // 5947 - Skeena-Queen Charlotte, BC
        // 6204 - Qikiqtaaluk, NU
        // 6208 - Kitikmeot, NU
    }
}
