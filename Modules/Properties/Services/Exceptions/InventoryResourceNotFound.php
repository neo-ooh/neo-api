<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - InventoryResourceNotFound.php
 */

namespace Neo\Modules\Properties\Services\Exceptions;

use Neo\Modules\Properties\Services\Resources\InventoryResourceId;
use RuntimeException;
use Throwable;

/**
 * Thrown when the requested resource could not be found on the Inventory system
 */
class InventoryResourceNotFound extends RuntimeException {
    public function __construct(InventoryResourceId $resourceId, ?Throwable $previous = null) {
        parent::__construct("Could not find resource #$resourceId->external_id of type {$resourceId->type->value}", -1, $previous);
    }
}
