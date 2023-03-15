<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - PropertyResource.php
 */

namespace Neo\Modules\Properties\Services\Resources;

class PropertyResource extends InventoryResource {
    public function __construct(
        /**
         * ID of the property in the inventory
         */
        public InventoryResourceId $property_id,

        /**
         * @var string name of the property
         */
        public string              $property_name,
    ) {
    }
}
