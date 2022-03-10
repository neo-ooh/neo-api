<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2021_05_20_160727_migrate_creatives_external_ids.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Neo\Models\Creative;
use Neo\Models\CreativeExternalId;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        $creatives = Creative::all();

        /** @var Creative $creative */
        foreach ($creatives as $creative) {
            if (!$creative->external_id_broadsign) {
                continue;
            }

            $schedule = $creative->content->schedules()->has("campaign", ">=", "1", "and", function ($query) {
                $query->whereNotNull("network_id");
            })->first();

            if (!$schedule || !$schedule->campaign->network_id) {
                continue;
            }

            $extId              = new CreativeExternalId();
            $extId->creative_id = $creative->id;
            $extId->network_id  = $schedule->campaign->network_id;
            $extId->external_id = $creative->external_id_broadsign;
            $extId->save();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        //
    }
};
