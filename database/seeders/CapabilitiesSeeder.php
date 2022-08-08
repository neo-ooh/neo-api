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
        $allCapabilities = \Neo\Enums\Capability::cases();

        /** @var Role $role */
        $role = Role::query()->firstOrCreate(["name" => "Admin"]);

        $caps = [];

        foreach ($allCapabilities as $capabilityEnum) {
            $cap = Capability::query()
                             ->firstOrCreate(["slug" => $capabilityEnum->value], ["service" => "", "standalone" => true]);

            $caps[] = $cap->id;
        }

        // Make sure all capabilities are always assigned to the Admin role
        $role->capabilities()->sync($caps);

        // Define the capabilities groups
        $prefixGroups = [
            "ACTORS"     => [
                "actors",
                "roles",
                "brandings",
            ],
            "DIRECT"     => [
                "campaigns",
                "libraries",
                "contents",
            ],
            "SALES"      => [
                "contracts",
                "bursts",
                "tools",
                "planning",
            ],
            "PROPERTIES" => [
                "properties",
                "odoo",
                "products",
                "pricelists",
                "loops",
                "tags",
                "traffic",
                "impressions",
            ],
            "NETWORK"    => [
                "networks",
                "formats",
                "broadcast",
            ],
            "DYNAMICS"   => [
                "dynamics",
            ],
            "INTERNAL"   => [
                "tos",
                "headlines",
                "access",
                "documents",
                "chores",
                "tests",
            ],
        ];

        foreach ($prefixGroups as $group => $prefixes) {
            $q = Capability::query();

            foreach ($prefixes as $prefix) {
                $q->where("slug", "like", $prefix . "_%");
            }

            $q->update(["service" => $group]);
        }

        // Delete missing capabilities
        Capability::query()->whereNotIn("id", $caps)->delete();
    }
}
