<?php
/*
 * Copyright 2022 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2022_12_05_153152_contract_bursts_soft_delete.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ContractBurstsSoftDelete extends Migration {
    public function up(): void {
        Schema::table('contracts_bursts', function (Blueprint $table) {
            $table->softDeletes();
        });
    }
}
