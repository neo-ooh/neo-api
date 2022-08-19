<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2022_07_27_143958_alter_networks_table.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Neo\Modules\Broadcast\Models\StructuredColumns\NetworkSettings;

return new class extends Migration {
    public function up() {
        Schema::table('networks', static function (Blueprint $table) {
            $table->timestamp("last_sync_at")->nullable()->after("settings");
        });

        // Update the network settings json to match the new format
        $networks = \Illuminate\Support\Facades\DB::table("networks")->get();

        foreach ($networks as $network) {
            $legacySettings = json_decode($network->settings, false, 512, JSON_THROW_ON_ERROR);

            $settings                         = new NetworkSettings();
            $settings->customer_id            = $legacySettings->customer_id ?? null;
            $settings->root_container_id      = $legacySettings->container_id ?? null;
            $settings->campaigns_container_id = $legacySettings->reservations_container_id ?? null;
            $settings->creatives_container_id = $legacySettings->ad_copies_container_id ?? null;

            DB::table("networks")->where("id", "=", $network->id)
              ->update([
                  "settings" => $settings->toJson()
              ]);
        }
    }
};
