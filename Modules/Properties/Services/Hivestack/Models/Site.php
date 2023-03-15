<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - Site.php
 */

namespace Neo\Modules\Properties\Services\Hivestack\Models;

use Neo\Modules\Properties\Services\Resources\Enums\InventoryResourceType;
use Neo\Modules\Properties\Services\Resources\InventoryResourceId;

/**
 * @property int     $site_id
 * @property int     $owner_id
 * @property string  $uuid
 * @property boolean $active
 * @property string  $name
 * @property string  $description  The field that is shown in the UI effectively as its name
 * @property string  $external_id
 * @property float   $latitude
 * @property float   $longitude
 * @property int     $locations_id No longer required. We try to automatically map the location based on the lat/lon
 *
 *
 * @property string  $created_on_utc
 * @property string  $modified_on_utc
 */
class Site extends HivestackModel {
    public string $key = "site_id";

    public function toInventoryResourceId(int $inventoryId): InventoryResourceId {
        return new InventoryResourceId(
            inventory_id: $inventoryId,
            external_id : $this->site_id,
            type        : InventoryResourceType::Property,
            context     : [],
        );
    }
}
