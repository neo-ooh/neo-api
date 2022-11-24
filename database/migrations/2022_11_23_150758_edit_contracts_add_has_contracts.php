<?php
/*
 * Copyright 2022 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2022_11_23_150758_edit_contracts_add_has_contracts.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class EditContractsAddHasContracts extends Migration {
    public function up() {
        Schema::table('contracts', static function (Blueprint $table) {
            $table->boolean("has_plan")->default(false)->after("advertiser_id");
        });
    }
}
