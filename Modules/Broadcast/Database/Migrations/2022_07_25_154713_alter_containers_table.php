<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2022_07_25_154713_alter_containers_table.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table("locations", static function (Blueprint $table) {
            $table->dropForeign("locations_container_id_foreign");
        });

        Schema::table('containers', static function (Blueprint $table) {
            $table->dropForeign("containers_parent_id_foreign");
        });

        Schema::table('containers', static function (Blueprint $table) {
            $table->dropPrimary();
        });

        Schema::rename("containers", "network_containers");

        Schema::table('network_containers', static function (Blueprint $table) {
            $table->string("id", 128)->nullable()->change();

            $table->renameColumn("id", "external_id");
        });

        Schema::table('network_containers', static function (Blueprint $table) {
            $table->id()->autoIncrement()->first();
            $table->unique("external_id");
        });

        // Update the parent_id reference of all containers
        $containers = DB::table("network_containers")->orderBy("id")->lazy(500);

        foreach ($containers as $container) {
            if (!$container->parent_id) {
                continue;
            }

            $parent = DB::table("network_containers")->where("external_id", "=", $container->parent_id)->first();

            DB::table("network_containers")->where("id", "=", $container->id)->update([
                "parent_id" => $parent->id ?? null,
            ]);
        }

        Schema::table("network_containers", static function (Blueprint $table) {
            $table->foreign("parent_id")
                  ->references("id")
                  ->on("network_containers")
                  ->cascadeOnUpdate()
                  ->nullOnDelete();
        });

        // Update the locations containers references
        $locations = DB::table("locations")->orderBy("id")->lazy(500);

        foreach ($locations as $location) {
            if (!$location->external_id) {
                continue;
            }

            /** @var object $container */
            $container = DB::table("network_containers")->where("external_id", "=", $location->container_id)->first();

            DB::table("locations")->where("id", "=", $location->id)->update([
                "container_id" => $container->id ?? null,
            ]);
        }

        Schema::table("locations", static function (Blueprint $table) {
            $table->foreign("container_id")->references("id")->on("network_containers")->cascadeOnUpdate()->nullOnDelete();
        });
    }
};
