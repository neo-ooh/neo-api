<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - CapabilitiesSeeder.php
 */

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Neo\Models\Capability;
use Neo\Models\Role;

class CapabilitiesSeeder extends Seeder {
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public static function run(): void {
        $allCapabilities = \Neo\Enums\Capability::asArray();

        /** @var Role $role */
        $role = Role::query()->firstOrCreate(["name" => "Admin"]);

        $caps = [];

        foreach ($allCapabilities as $value) {
            $cap = Capability::query()->firstOrCreate(["slug" => $value], ["service" => "", "standalone" => true]);

            $caps[] = $cap->id;
        }

        // Make sure all capabilities are always assigned to the Admin role
        $role->capabilities()->sync($caps);

        // Define the capabilities groups
        $capGroups = [
            "ACTORS" => [
                \Neo\Enums\Capability::actors_create,
                \Neo\Enums\Capability::actors_delete,
                \Neo\Enums\Capability::actors_edit,

                \Neo\Enums\Capability::roles_edit,
                \Neo\Enums\Capability::brandings_edit,
            ],
            "DIRECT" => [
                \Neo\Enums\Capability::libraries_edit,
                \Neo\Enums\Capability::libraries_create,
                \Neo\Enums\Capability::libraries_destroy,

                \Neo\Enums\Capability::formats_edit,

                \Neo\Enums\Capability::campaigns_edit,

                \Neo\Enums\Capability::contents_edit,
                \Neo\Enums\Capability::contents_dynamic,
                \Neo\Enums\Capability::contents_schedule,
                \Neo\Enums\Capability::contents_review,
            ],
            "NETWORK" => [
                \Neo\Enums\Capability::properties_view,
                \Neo\Enums\Capability::properties_traffic,
                \Neo\Enums\Capability::properties_edit,

                \Neo\Enums\Capability::bursts_request,
                \Neo\Enums\Capability::bursts_quality,

                \Neo\Enums\Capability::contracts_edit,
                \Neo\Enums\Capability::contracts_manage,

                \Neo\Enums\Capability::documents_generation,
                \Neo\Enums\Capability::inventory_read,
            ],
            "DYNAMICS" => [
                \Neo\Enums\Capability::dynamics_news,
                \Neo\Enums\Capability::dynamics_weather,
            ],
            "INTERNAL" => [
                \Neo\Enums\Capability::networks_edit,
                \Neo\Enums\Capability::networks_connections
                ,
                \Neo\Enums\Capability::tos_update,
                \Neo\Enums\Capability::headlines_edit,
                \Neo\Enums\Capability::chores_broadsign,
                \Neo\Enums\Capability::access_token_edit,

                \Neo\Enums\Capability::tests,
            ],
        ];

        foreach($capGroups as $key => $caps) {
            Capability::query()->whereIn("slug", $caps)
                ->update(["service" => $key]);
        }
    }
}
