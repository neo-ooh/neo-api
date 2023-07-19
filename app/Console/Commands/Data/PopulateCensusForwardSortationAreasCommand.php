<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - PopulateCensusForwardSortationAreasCommand.php
 */

namespace Neo\Console\Commands\Data;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use JsonMachine\Items;
use MatanYadaev\EloquentSpatial\Objects\MultiPolygon;
use Neo\Models\CensusForwardSortationArea;

class PopulateCensusForwardSortationAreasCommand extends Command {
    protected $signature = 'data:populate-census-fsas {census-file} {skip}';

    protected $description = 'Read a census 2021 Census forward sortation areas file in GeoJson and populates the `census_forward_sortation_areas` table with it';

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

        foreach ($divisions as $key => $division) {
//            if ($division->properties->CDUID <= 6208087) {
//                $this->comment("[skipped] " . $key . " - " . $subdivision->properties->CFSAUID . ", " . $this->provincesSlug[$subdivision->properties->PRUID]);
//                continue;
//            }

            if ($key < $this->argument("skip")) {
                $this->comment("[skipped] " . $key . " - " . $division->properties->CFSAUID . ", " . $this->provincesSlug[$division->properties->PRUID]);
                continue;
            }

            try {
                CensusForwardSortationArea::query()->insertOrIgnore([
                                                                        "id"                => $division->properties->CFSAUID,
                                                                        "census"            => 2021,
                                                                        "province"          => $this->provincesSlug[$division->properties->PRUID],
                                                                        "landarea_sqkm"     => $division->properties->LANDAREA,
                                                                        "dissemination_uid" => $division->properties->DGUID,
                                                                        "geometry"          => DB::raw("MultiPolygonFromText('" . MultiPolygon::fromJson(json_encode($division->geometry))
                                                                                                                                              ->toWkt() . "')"),
                                                                    ]);
                $this->info("[done] " . $key . " - " . $division->properties->CFSAUID . ", " . $this->provincesSlug[$division->properties->PRUID]);
            } catch (Exception $e) {
                $this->error("[failed] " . $key . " - " . $division->properties->CFSAUID . ", " . $this->provincesSlug[$division->properties->PRUID]);
            }
        }

        // Failed on last run:
        // 2498 - Minganie--Le Golfe-du-Saint-Laurent, QC
        // 5947 - Skeena-Queen Charlotte, BC
        // 6204 - Qikiqtaaluk, NU
        // 6208 - Kitikmeot, NU
    }
}
