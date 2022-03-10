<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2021_05_13_142351_create_frame_settings_broadsign.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Schema table name to migrate
     *
     * @var string
     */
    public string $tableName = "frame_settings_broadsign";

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create($this->tableName, function (Blueprint $table) {
            $table->foreignId("frame_id")->primary()->constrained("frames")->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId("criteria_id")->nullable()->constrained("broadsign_criteria")->cascadeOnUpdate()->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists($this->tableName);
    }
};
