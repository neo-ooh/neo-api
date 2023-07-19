<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - PopulateCensusSubdivisionsCommand.php
 */

namespace Neo\Console\Commands\Data;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use JsonMachine\Items;
use MatanYadaev\EloquentSpatial\Objects\MultiPolygon;
use Neo\Models\CensusSubdivision;

class PopulateCensusSubdivisionsCommand extends Command {
    protected $signature = 'data:populate-census-subdivisions {census-file}';

    protected $description = 'Read a census 2021 Census Subdivisions file in GeoJson and populates the `census-subdivisions` table with it';

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

        $subdivisions = Items::fromFile($filePath, ["pointer" => "/features",]);

        foreach ($subdivisions as $key => $subdivision) {
//            if ($subdivision->properties->CSDUID <= 6208087) {
//                $this->comment("[skipped] " . $key . " - " . $subdivision->properties->CSDUID . " - " . $subdivision->properties->CSDNAME . ", " . $this->provincesSlug[$subdivision->properties->PRUID]);
//                continue;
//            }


            try {
                CensusSubdivision::query()->insertOrIgnore([
                                                               "id"                => $subdivision->properties->CSDUID,
                                                               "census"            => 2021,
                                                               "name"              => $subdivision->properties->CSDNAME,
                                                               "type"              => $subdivision->properties->CSDTYPE,
                                                               "province"          => $this->provincesSlug[$subdivision->properties->PRUID],
                                                               "landarea_sqkm"     => $subdivision->properties->LANDAREA,
                                                               "dissemination_uid" => $subdivision->properties->DGUID,
                                                               "geometry"          => DB::raw("MultiPolygonFromText('" . MultiPolygon::fromJson(json_encode($subdivision->geometry))
                                                                                                                                     ->toWkt() . "')"),
                                                           ]);

                $this->info("[done] " . $key . " - " . $subdivision->properties->CSDUID . " - " . $subdivision->properties->CSDNAME . ", " . $this->provincesSlug[$subdivision->properties->PRUID]);
            } catch (Exception $e) {
                $this->error("[failed] " . $key . " - " . $subdivision->properties->CSDUID . " - " . $subdivision->properties->CSDNAME . ", " . $this->provincesSlug[$subdivision->properties->PRUID]);
            }
        }
    }
}
