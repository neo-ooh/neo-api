<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2023_03_23_130739_migrate_capabilities.php
 */

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Migrations\Migration;
use Neo\Enums\Capability;
use Neo\Models\Actor;
use Neo\Models\Role;

return new class extends Migration {
    public function up(): void {
        $roles = Role::query()->with(["capabilities"])
                     ->get();

        $capabilities = \Neo\Models\Capability::all();

        $propertyEditNewCapabilities = [
            $capabilities->firstWhere("slug", "=", Capability::properties_export)->getKey(),
            $capabilities->firstWhere("slug", "=", Capability::properties_address_edit)
                         ->getKey(),
            $capabilities->firstWhere("slug", "=", Capability::properties_opening_hours_edit)
                         ->getKey(),
            $capabilities->firstWhere("slug", "=", Capability::properties_infos_edit)
                         ->getKey(),
            $capabilities->firstWhere("slug", "=", Capability::properties_pictures_view)
                         ->getKey(),
            $capabilities->firstWhere("slug", "=", Capability::properties_pictures_edit)
                         ->getKey(),
            $capabilities->firstWhere("slug", "=", Capability::properties_contacts_view)
                         ->getKey(),
            $capabilities->firstWhere("slug", "=", Capability::properties_contacts_edit)
                         ->getKey(),
            $capabilities->firstWhere("slug", "=", Capability::properties_tags_view)
                         ->getKey(),
            $capabilities->firstWhere("slug", "=", Capability::properties_tags_create)
                         ->getKey(),
            $capabilities->firstWhere("slug", "=", Capability::properties_tags_edit)
                         ->getKey(),
            $capabilities->firstWhere("slug", "=", Capability::properties_demographics_view)
                         ->getKey(),
            $capabilities->firstWhere("slug", "=", Capability::properties_demographics_edit)
                         ->getKey(),
            $capabilities->firstWhere("slug", "=", Capability::properties_traffic_view)
                         ->getKey(),
            $capabilities->firstWhere("slug", "=", Capability::properties_traffic_manage)
                         ->getKey(),
            $capabilities->firstWhere("slug", "=", Capability::properties_tenants_view)
                         ->getKey(),
            $capabilities->firstWhere("slug", "=", Capability::properties_tenants_edit)
                         ->getKey(),
            $capabilities->firstWhere("slug", "=", Capability::properties_pricelist_view)
                         ->getKey(),
            $capabilities->firstWhere("slug", "=", Capability::properties_pricelist_assign)
                         ->getKey(),
            $capabilities->firstWhere("slug", "=", Capability::properties_unavailabilities_view)
                         ->getKey(),
            $capabilities->firstWhere("slug", "=", Capability::properties_unavailabilities_edit)
                         ->getKey(),
            $capabilities->firstWhere("slug", "=", Capability::properties_inventories_view)
                         ->getKey(),
            $capabilities->firstWhere("slug", "=", Capability::properties_inventories_edit)
                         ->getKey(),
            $capabilities->firstWhere("slug", "=", Capability::products_view)->getKey(),
            $capabilities->firstWhere("slug", "=", Capability::products_edit)->getKey(),
            $capabilities->firstWhere("slug", "=", Capability::products_attachments_edit)
                         ->getKey(),
            $capabilities->firstWhere("slug", "=", Capability::products_impressions_view)
                         ->getKey(),
            $capabilities->firstWhere("slug", "=", Capability::products_impressions_edit)
                         ->getKey(),
            $capabilities->firstWhere("slug", "=", Capability::products_locations_view)
                         ->getKey(),
            $capabilities->firstWhere("slug", "=", Capability::products_locations_edit)
                         ->getKey(),
            $capabilities->firstWhere("slug", "=", Capability::product_categories_edit)
                         ->getKey(),
            $capabilities->firstWhere("slug", "=", Capability::pricelists_edit)->getKey(),
        ];

        $propertyTrafficNewCapabilties = [
            $capabilities->firstWhere("slug", "=", Capability::properties_traffic_view)
                         ->getKey(),
            $capabilities->firstWhere("slug", "=", Capability::properties_traffic_fill)
                         ->getKey(),
        ];

        foreach ($roles as $role) {
            if ($role->capabilities->contains("slug.value", "=", "properties.edit")) {
                $roleCapabilities = $role->capabilities->pluck("id");
                $roleCapabilities->push(...$propertyEditNewCapabilities);
                $roleCapabilities = $roleCapabilities->unique();
                $role->capabilities()->sync($roleCapabilities);
                $role->refresh();
            }

            if ($role->capabilities->contains("slug.value", "=", "properties.traffic")) {
                $roleCapabilities = $role->capabilities->pluck("id");
                $roleCapabilities->push(...$propertyTrafficNewCapabilties);
                $roleCapabilities = $roleCapabilities->unique();
                $role->capabilities()->sync($roleCapabilities);
                $role->refresh();
            }
        }

        $actors = Actor::query()->whereHas("standalone_capabilities", function (Builder $query) {
            $query->where("slug", "=", "properties.edit");
        })->with(["standalone_capabilities"])->get();

        /** @var $actor Actor */
        foreach ($actors as $actor) {
            $newCapabilities = $actor->standalone_capabilities->pluck("id");
            $newCapabilities->push(...$propertyEditNewCapabilities);
            $newCapabilities = $newCapabilities->unique();
            
            $actor->standalone_capabilities()->sync($newCapabilities);
            $actor->refresh();
        }

        $actors = Actor::query()->whereHas("standalone_capabilities", function (Builder $query) {
            $query->where("slug", "=", "properties.traffic");
        })->with(["standalone_capabilities"])->get();

        /** @var $actor Actor */
        foreach ($actors as $actor) {
            $newCapabilities = $actor->standalone_capabilities->pluck("id");
            $newCapabilities->push(...$propertyTrafficNewCapabilties);
            $newCapabilities = $newCapabilities->unique();

            $actor->standalone_capabilities()->sync($newCapabilities);
            $actor->refresh();
        }
    }
};
