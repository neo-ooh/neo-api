<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ProductCategoryResource.php
 */

namespace Neo\Modules\Properties\Services\Resources;

class ProductCategoryResource extends InventoryResource {
    public function __construct(
        /**
         * ID of the product category in the inventory
         */
        public InventoryResourceId $category_id,

        /**
         * @var string name of the product category
         */
        public string              $category_name,
    ) {
    }
}
