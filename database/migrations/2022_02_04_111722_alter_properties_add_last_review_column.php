<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2022_02_04_111722_alter_properties_add_last_review_column.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Neo\Models\Property;

class AlterPropertiesAddLastReviewColumn extends Migration {
    public function up() {
        Schema::table('properties', function (Blueprint $table) {
            $table->dropColumn("tenants_updated_at");
            $table->timestamp("last_review_at");
        });

        Property::query()->get()->each(function (Property $property) {
            $property->last_review_at = $property->updated_at;
            $property->save();
        });
    }

    public function down() {
        Schema::table('properties', function (Blueprint $table) {
            $table->dropColumn("last_review_at");
        });
    }
}
