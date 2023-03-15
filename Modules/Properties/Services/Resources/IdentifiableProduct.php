<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - IdentifiableProduct.php
 */

namespace Neo\Modules\Properties\Services\Resources;

class IdentifiableProduct {
    public function __construct(
        public InventoryResourceId $resourceId,
        public ProductResource     $product,
    ) {
    }
}
