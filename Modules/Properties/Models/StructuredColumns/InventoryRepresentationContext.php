<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - InventoryRepresentationContext.php
 */

namespace Neo\Modules\Properties\Models\StructuredColumns;

use Neo\Models\Utils\JSONDBColumn;
use Spatie\LaravelData\Optional;

class InventoryRepresentationContext extends JSONDBColumn {
    public function __construct(
        /**
         * @var int|Optional Odoo's products variant id to use in contracts
         */
        public int|Optional   $variant_id,

        /**
         * @var int|Optional Odoo's product categories production product
         */
        public int|Optional   $production_product_id,

        /**
         * @var int|Optional Hivestack's units network id
         */
        public int|Optional   $network_id,

        /**
         * @var array|Optional Hivestack units ids for products
         */
        public array|Optional $units,
    ) {
    }
}
