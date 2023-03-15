<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - IncompatibleResourceAndInventoryException.php
 */

namespace Neo\Modules\Properties\Services\Exceptions;

use Neo\Exceptions\BaseException;
use Neo\Modules\Properties\Services\InventoryType;

class IncompatibleResourceAndInventoryException extends BaseException {
    public function __construct(int $resourceId, int $inventoryId, InventoryType $type) {
        parent::__construct("Resource #$resourceId is not compatible with inventory #$inventoryId ($type->name)", "inventory.resource.not-compatible");
    }
}
