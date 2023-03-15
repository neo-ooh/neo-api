<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - PullNotAllowedException.php
 */

namespace Neo\Modules\Properties\Exceptions\Synchronization;

use Neo\Exceptions\BaseException;

class PullNotAllowedException extends BaseException {
    public function __construct(int $productId, int $inventoryId, bool $allowedByProperty) {
        parent::__construct("Pull is not allowed for product #$productId on inventory $inventoryId", "inventories.pull.not-allowed");
        $this->context = [
            "property.allow" => $allowedByProperty,
            "product.allow"  => false,
        ];
    }
}
