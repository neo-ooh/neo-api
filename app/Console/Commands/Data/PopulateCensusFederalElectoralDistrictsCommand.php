<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - PopulateCensusFederalElectoralDistrictsCommand.php
 */

namespace Neo\Console\Commands\Data;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use JsonMachine\Items;
use MatanYadaev\EloquentSpatial\Objects\MultiPolygon;
use Neo\Models\CensusFederalElectoralDistrict;

class PopulateCensusFederalElectoralDistrictsCommand extends Command {
    protected $signature = 'data:populate-census-electoral-districts {census-file} {skip}';

    protected $description = 'Read a census 2021 Census Federal Electoral Districts file in GeoJson and populates the `census_federal_electoral_districts` table with it';

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

        $districts = Items::fromFile($filePath, ["pointer" => "/features",]);

        foreach ($districts as $key => $district) {
//            if ($district->properties->FEDUID <= 24045) {
//                $this->comment("[skipped] " . $key . " - " . $district->properties->FEDUID . " - " . $district->properties->FEDNAME . ", " . $this->provincesSlug[$district->properties->PRUID]);
//                continue;
//            }

            if ($key < $this->argument("skip")) {
                $this->comment("[skipped] " . $key . " - " . $district->properties->FEDUID . " - " . $district->properties->FEDNAME . ", " . $this->provincesSlug[$district->properties->PRUID]);
                continue;
            }

            try {
                CensusFederalElectoralDistrict::query()->insertOrIgnore([
                                                                            "id"                => $district->properties->FEDUID,
                                                                            "census"            => 2021,
                                                                            "name_en"           => $district->properties->FEDENAME,
                                                                            "name_fr"           => $district->properties->FEDFNAME,
                                                                            "province"          => $this->provincesSlug[$district->properties->PRUID],
                                                                            "landarea_sqkm"     => $district->properties->LANDAREA,
                                                                            "dissemination_uid" => $district->properties->DGUID,
                                                                            "geometry"          => DB::raw("MultiPolygonFromText('" . MultiPolygon::fromJson(json_encode($district->geometry))
                                                                                                                                                  ->toWkt() . "')"),
                                                                        ]);
                $this->info("[done] " . $key . " - " . $district->properties->FEDUID . " - " . $district->properties->FEDNAME . ", " . $this->provincesSlug[$district->properties->PRUID]);
            } catch (Exception $e) {
                $this->info(substr($e->getMessage(), 0, 250));
                $this->error("[failed] " . $key . " - " . $district->properties->FEDUID . " - " . $district->properties->FEDNAME . ", " . $this->provincesSlug[$district->properties->PRUID]);
            }
        }

        // Failed on last run:
        // 2498 - Minganie--Le Golfe-du-Saint-Laurent, QC
        // 5947 - Skeena-Queen Charlotte, BC
        // 6204 - Qikiqtaaluk, NU
        // 6208 - Kitikmeot, NU
    }
}
