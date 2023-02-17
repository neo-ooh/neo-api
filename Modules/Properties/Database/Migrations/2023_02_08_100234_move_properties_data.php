<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2023_02_08_100234_move_properties_data.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up() {
        // For each entry in the `properties_data` table, we want to move the `website` field value to the `properties`.`website` field, and the `description_<locale>` fields to new rows in the `properties_translations` table

        $propertiesData = DB::table("properties_data")->get();

        foreach ($propertiesData as $propertyData) {
            DB::table("properties")
              ->where("actor_id", "=", $propertyData->property_id)
              ->update([
                           "website" => $propertyData->website,
                       ]);

            DB::table("properties_translations")
              ->insert([
                           [
                               "property_id" => $propertyData->property_id,
                               "locale"      => "fr-CA",
                               "description" => trim($propertyData->description_fr),
                               "created_at"  => Date::now(),
                               "updated_at"  => Date::now(),
                           ],
                           [
                               "property_id" => $propertyData->property_id,
                               "locale"      => "en-CA",
                               "description" => trim($propertyData->description_en),
                               "created_at"  => Date::now(),
                               "updated_at"  => Date::now(),
                           ],
                       ]);
        }
    }
};
