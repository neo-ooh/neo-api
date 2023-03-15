<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - UnsupportedInventoryFunctionalityException.php
 */

namespace Neo\Modules\Properties\Exceptions\Synchronization;

use Neo\Exceptions\BaseException;
use Neo\Modules\Properties\Services\InventoryCapability;

class UnsupportedInventoryFunctionalityException extends BaseException {
    public function __construct(int $inventoryId, InventoryCapability $capability) {
        parent::__construct("`$capability->value` is not supported by inventory #$inventoryId", "inventories.unsupported-functionality");
    }
}
