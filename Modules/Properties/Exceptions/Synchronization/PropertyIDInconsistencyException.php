<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - PropertyIDInconsistencyException.php
 */

namespace Neo\Modules\Properties\Exceptions\Synchronization;

use Neo\Exceptions\BaseException;
use Neo\Modules\Properties\Services\Resources\InventoryResourceId;

class PropertyIDInconsistencyException extends BaseException {
    public function __construct(int $productId, int $inventoryId, InventoryResourceId $productPropertyRepresentation, InventoryResourceId $propertyRepresentation) {
        parent::__construct("On inventory #$inventoryId, product #$productId property ID is different from the its property representation: $productPropertyRepresentation->external_id vs $propertyRepresentation->external_id", "inventories.property-id-inconsistency");
    }
}
