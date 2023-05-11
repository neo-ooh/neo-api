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
        public int|Optional        $variant_id,

        /**
         * @var int|Optional Odoo's product categories production product
         */
        public int|Optional        $production_product_id,

        /**
         * @var int|Optional Hivestack|Vistar units network id
         */
        public int|string|Optional $network_id,

        /**
         * @var int|Optional Hivestack's units media type id
         */
        public int|Optional        $media_type_id,

        /**
         * @var array{id: string, name: string}|Optional Hivestack|PlaceExchange units ids for products
         */
        public array|Optional      $units,

        /**
         * @var array|Optional Reach venue type id
         */
        public int|Optional        $venue_type_id,

        /**
         * @var array{id: string, name: string}|Optional Reach screens ids for products
         */
        public array|Optional      $screens,

        /**
         * @var array|Optional Vistar venue type
         */
        public string|Optional     $venue_type,

        /**
         * @var array{id: string, name: string}|Optional Vistar venue ids for products
         */
        public array|Optional      $venues,
    ) {
    }
}
