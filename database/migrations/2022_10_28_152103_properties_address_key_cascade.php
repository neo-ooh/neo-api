<?php
/*
 * Copyright 2022 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2022_10_28_152103_properties_address_key_cascade.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class PropertiesAddressKeyCascade extends Migration {
    public function up() {
        Schema::table('properties', static function (Blueprint $table) {
            $table->dropForeign(['address_id']);
            $table->foreign('address_id')
                  ->references('id')
                  ->on('addresses')
                  ->cascadeOnUpdate()
                  ->nullOnDelete();
        });
    }
}
