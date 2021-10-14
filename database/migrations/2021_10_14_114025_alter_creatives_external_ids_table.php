<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2021_10_14_114025_alter_creatives_external_ids_table.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterCreativesExternalIdsTable extends Migration {
    public function up() {
        Schema::table('creatives_external_ids', function (Blueprint $table) {
            $table->dropForeign(["creative_id"]);
            $table->dropForeign(["network_id"]);

            $table->foreign("creative_id")->on("creatives")->references("id")->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreign("network_id")->on("networks")->references("id")->cascadeOnDelete()->cascadeOnUpdate();
        });
    }

    public function down() {
    }
}
