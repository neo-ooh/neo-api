<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - InventoryExternalResource.php
 */

namespace Neo\Modules\Properties\Resources;

use Neo\Modules\Properties\Services\Resources\InventoryResourceId;
use Spatie\LaravelData\Data;

class InventoryExternalResource extends Data {
    public function __construct(
        public string              $type,
        public string              $name,
        public InventoryResourceId $external_id,
    ) {

    }
}
