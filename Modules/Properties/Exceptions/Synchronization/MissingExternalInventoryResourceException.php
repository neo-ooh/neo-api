<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - MissingExternalInventoryResourceException.php
 */

namespace Neo\Modules\Properties\Exceptions\Synchronization;

use Neo\Exceptions\BaseException;

class MissingExternalInventoryResourceException extends BaseException {
    public function __construct(int $productId, int $inventoryId) {
        parent::__construct("Product #$productId has no valid representation for inventory #$inventoryId", "inventories.missing-representation");
    }
}
