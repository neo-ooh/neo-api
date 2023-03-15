<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - InventoryMethodNotSupportedException.php
 */

namespace Neo\Modules\Properties\Services\Exceptions;

use Neo\Modules\Properties\Services\InventoryType;
use RuntimeException;
use Throwable;

/**
 * Thrown when the inventory does not support the requested method
 */
class InventoryMethodNotSupportedException extends RuntimeException {
    public function __construct(int $inventoryId, InventoryType $inventoryType, string $method, ?Throwable $previous = null) {
        parent::__construct("Inventory #$inventoryId ($inventoryType->name) does not support method `$method`.", -1, $previous);
    }
}
