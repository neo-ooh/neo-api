<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2023_02_08_160825_properties_add_is_sellable.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::table('properties', static function (Blueprint $table) {
            $table->boolean("is_sellable")->default(true)->after("pricelist_id");
        });

        $properties     = \Illuminate\Support\Facades\DB::table("properties")->get();
        $odooProperties = \Illuminate\Support\Facades\DB::table("odoo_properties")->get();

        foreach ($properties as $property) {
            if ($odooProperties->doesntContain("property_id", "=", $property->actor_id)) {
                \Illuminate\Support\Facades\DB::table("properties")
                                              ->where("actor_id", "=", $property->actor_id)
                                              ->update([
                                                           "is_sellable" => false
                                                       ]);
            }
        }

        Schema::table('products', static function (Blueprint $table) {
            $table->boolean("is_sellable")->default(true)->after("quantity");
        });
    }
};
