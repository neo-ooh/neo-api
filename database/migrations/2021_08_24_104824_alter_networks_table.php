<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2021_08_24_104824_alter_networks_table.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Neo\Models\Network;
use Neo\Models\UnstructuredData\NetworkSettingsBroadSign;
use Neo\Models\UnstructuredData\NetworkSettingsPiSignage;

class AlterNetworksTable extends Migration {
    public function up() {
        Schema::table('networks', function (Blueprint $table) {
            $table->json("settings")->after("name");
        });

        $broadsignSettings = \Illuminate\Support\Facades\DB::table("network_settings_broadsign")->get();

        foreach ($broadsignSettings as $settings) {
            $network = Network::query()->find($settings->network_id);
            $network->settings = new NetworkSettingsBroadSign((array)$settings);
            $network->save();
        }

        $pisignageSettings = DB::table("network_settings_pisignage")->get();

        foreach ($pisignageSettings as $settings) {
            $network = Network::find($settings->network_id);
            $network->settings = new NetworkSettingsPiSignage((array)$settings);
            $network->save();
        }

        Schema::drop("network_settings_broadsign");
        Schema::drop("network_settings_pisignage");
    }

    public function down() {
        Schema::table('networks', function (Blueprint $table) {
            //
        });
    }
}
