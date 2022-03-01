<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2022_03_01_161220_drop_contracts_networks_data_table.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class DropContractsNetworksDataTable extends Migration {
    public function up() {
        Schema::drop("contracts_networks_data");
    }

    public function down() {
    }
}
