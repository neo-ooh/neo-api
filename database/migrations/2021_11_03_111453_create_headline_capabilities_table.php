<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2021_11_03_111453_create_headline_capabilities_table.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHeadlineCapabilitiesTable extends Migration {
    public function up() {
        Schema::create('headline_capabilities', function (Blueprint $table) {
            $table->foreignId("headline_id")->constrained("headlines")->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId("capability_id")->constrained("capabilities")->cascadeOnUpdate()->cascadeOnDelete();
        });
    }

    public function down() {
        Schema::dropIfExists('headline_capabilities');
    }
}
