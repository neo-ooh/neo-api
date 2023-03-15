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
                                                       "api_url"      => "https://odoo.neo-ooh.com/xmlrpc/2",
                                                       "api_key"      => "dummy123",
                                                       "api_username" => "dummy123",
                                                       "database"     => "production",
                                                   ]),
                       "updated_at" => Carbon::now(),
                   ]);
    }
};
