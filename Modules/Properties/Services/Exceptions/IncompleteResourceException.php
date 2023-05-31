<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - IncompleteResourceException.php
 */

namespace Neo\Modules\Properties\Services\Exceptions;

use Neo\Exceptions\BaseException;
use Neo\Modules\Properties\Services\InventoryType;

class IncompleteResourceException extends BaseException {
    public function __construct(int $resourceId, string $field, int $inventoryId, InventoryType $type) {
        parent::__construct("Resource #$resourceId is missing property `$field` for inventory #$inventoryId ($type->name)", "inventory.resource.not-compatible");
    }
}
