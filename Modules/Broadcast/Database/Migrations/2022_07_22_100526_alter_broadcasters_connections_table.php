<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2022_07_22_100526_alter_broadcasters_connections_table.php
 */

use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up() {
        $broadcasters = \Illuminate\Support\Facades\DB::table("broadcasters_connections")
                                                      ->where("broadcaster", "=", "broadsign")
                                                      ->get();

        foreach ($broadcasters as $broadcaster) {
            $settings = json_decode($broadcaster->settings, true, 512, JSON_THROW_ON_ERROR);
            unset($settings["default_tracking_id"]);

            $settings["customer_id"] = $settings["default_customer_id"];

            DB::table("broadcasters_connections")->where("id", "=", $broadcaster->id)->update([
                "settings" => json_encode($settings, JSON_THROW_ON_ERROR),
            ]);
        }
    }
};
