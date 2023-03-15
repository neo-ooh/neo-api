<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - InventoryResourceId.php
 */

namespace Neo\Modules\Properties\Services\Resources;

use Neo\Modules\Properties\Services\Resources\Enums\InventoryResourceType;
use Spatie\LaravelData\Data;

class InventoryResourceId extends Data {
    public function __construct(
        /**
         * Id of the inventory system the resource is part of
         */
        public int                   $inventory_id,

        /**
         * @var string Actual ID of the resource in the inventory system
         */
        public string                $external_id,

        /**
         * @var InventoryResourceType The resource type
         */
        public InventoryResourceType $type,

        /**
         * @var array Any other information the adapter which to attach to the ID
         */
        public array                 $context = [],
    ) {
    }
}
