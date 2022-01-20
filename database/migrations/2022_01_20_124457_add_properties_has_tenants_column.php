<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2022_01_20_124457_add_properties_has_tenants_column.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPropertiesHasTenantsColumn extends Migration {
    public function up() {
        Schema::table('properties', function (Blueprint $table) {
            $table->boolean("has_tenants")->default(false)->after("networK_id");
            $table->timestamp("tenants_updated_at")->nullable()->after("has_tenants");
        });
    }

    public function down() {
        Schema::table('properties', function (Blueprint $table) {
            $table->dropColumn(["has_tenants", "tenants_updated_at"]);
        });
    }
}
