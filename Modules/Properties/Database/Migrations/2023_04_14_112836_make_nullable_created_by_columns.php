<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2023_04_14_112836_make_nullable_created_by_columns.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('unavailabilities', function (Blueprint $table) {
            $table->foreignId("created_by")->nullable(true)->default(null)->change();
            $table->foreignId("updated_by")->nullable(true)->default(null)->change();
        });

        Schema::table('inventory_providers', function (Blueprint $table) {
            $table->foreignId("created_by")->nullable(true)->default(null)->change();
            $table->foreignId("updated_by")->nullable(true)->default(null)->change();
        });

        Schema::table('resource_inventories_settings', function (Blueprint $table) {
            $table->foreignId("created_by")->nullable(true)->default(null)->change();
            $table->foreignId("updated_by")->nullable(true)->default(null)->change();
        });

        Schema::table('external_inventories_resources', function (Blueprint $table) {
            $table->foreignId("created_by")->nullable(true)->default(null)->change();
            $table->foreignId("updated_by")->nullable(true)->default(null)->change();
        });

        Schema::table('external_inventories_resources', function (Blueprint $table) {
            $table->foreignId("created_by")->nullable(true)->default(null)->change();
            $table->foreignId("updated_by")->nullable(true)->default(null)->change();
        });
    }
};
