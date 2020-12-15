<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <Valentin Dufois>
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
    public static function run (): void {
        $allCapabilities = \Neo\Enums\Capability::asArray();

        /** @var Role $admin */
        $admin = Role::query()->firstOrCreate([ "name" => "Admin" ]);

        foreach ($allCapabilities as $capability => $value) {
            $cap = Capability::query()->firstOrCreate([ "slug" => $value, "standalone" => true ], ["service" => ""]);
            if (!$admin->capabilities->contains($cap)) {
                $admin->capabilities()->attach($cap->id);
            }
        }

        // Assign proper service to each capability
        Capability::where("slug", "=", "actors.edit")->update(["service" => "ACTORS"]);
        Capability::where("slug", "=", "actors.create")->update(["service" => "ACTORS"]);
        Capability::where("slug", "=", "actors.delete")->update(["service" => "ACTORS"]);

        Capability::where("slug", "=", "roles.edit")->update(["service" => "ACTORS"]);
        Capability::where("slug", "=", "brandings.edit")->update(["service" => "ACTORS"]);

        Capability::where("slug", "=", "libraries.edit")->update(["service" => "DIRECT"]);
        Capability::where("slug", "=", "libraries.create")->update(["service" => "DIRECT"]);
        Capability::where("slug", "=", "libraries.destroy")->update(["service" => "DIRECT"]);

        Capability::where("slug", "=", "campaigns.edit")->update(["service" => "DIRECT"]);
        Capability::where("slug", "=", "contents.edit")->update(["service" => "DIRECT"]);
        Capability::where("slug", "=", "contents.schedule")->update(["service" => "DIRECT"]);

        Capability::where("slug", "=", "contents.review")->update(["service" => "DIRECT"]);
        Capability::where("slug", "=", "formats.edit")->update(["service" => "DIRECT"]);

        Capability::where("slug", "=", "test.capability")->update(["service" => "TEST"]);

        Capability::where("slug", "=", "locations.edit")->update(["service" => "ACTORS"]);

        Capability::where("slug", "=", "bursts.request")->update(["service" => "REPORTS"]);
        Capability::where("slug", "=", "reports.create")->update(["service" => "REPORTS"]);
        Capability::where("slug", "=", "reports.edit")->update(["service" => "REPORTS"]);
    }
}
