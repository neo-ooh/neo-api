<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2023_02_28_143213_insert_odoo_inventory_provider.php
 */

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Neo\Modules\Properties\Services\InventoryType;

return new class extends Migration {
    public function up() {
        // Seed the database with the odoo connection as part of the migration
        DB::table("inventory_providers")
          ->insert([
                       "provider"   => InventoryType::Odoo->value,
                       "name"       => "Odoo",
                       "is_active"  => true,
                       "settings"   => json_encode([
                                                       "api_url"      => config('modules-legacy.odoo.server-url'),
                                                       "api_key"      => config('modules-legacy.odoo.password'),
                                                       "api_username" => config('modules-legacy.odoo.username'),
                                                       "database"     => config('modules-legacy.odoo.database'),
                                                   ]),
                       "updated_at" => Carbon::now(),
                   ]);
    }
};
