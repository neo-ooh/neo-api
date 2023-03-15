<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - HivestackConfig.php
 */

namespace Neo\Modules\Properties\Services\Hivestack;

use Neo\Modules\Properties\Services\Hivestack\API\HivestackClient;
use Neo\Modules\Properties\Services\InventoryConfig;
use Neo\Modules\Properties\Services\InventoryType;

class HivestackConfig extends InventoryConfig {
    public InventoryType $type = InventoryType::Hivestack;

    public function __construct(
        public string $name,
        public int    $inventoryID,
        public string $inventoryUUID,
        public string $api_url,
        public string $api_key,
    ) {
    }

    public function getClient(): HivestackClient {
        return new HivestackClient($this);
    }
}
