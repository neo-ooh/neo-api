<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2023_07_25_165701_create_contract_screenshots_table.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('contracts_screenshots', function (Blueprint $table) {
            $table->foreignId("contract_id")->constrained("contracts", "id")->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId("flight_id")->nullable()->constrained("contracts_flights", "id")->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId("screenshot_id")->constrained("screenshots", "id")->cascadeOnUpdate()->cascadeOnDelete();
        });

        \Illuminate\Support\Facades\DB::statement(<<<EOL
        INSERT INTO `contracts_screenshots` (`contract_id`, `screenshot_id`)
        SELECT `r`.`contract_id` AS `contract_id`,
               `s`.`id`          AS `screenshot_id`
          FROM `screenshots` `s`
               JOIN `screenshots_requests` `r` ON `s`.`request_id` = `r`.`id`
         WHERE `s`.`is_locked` = 1
        EOL
        );
    }
};
