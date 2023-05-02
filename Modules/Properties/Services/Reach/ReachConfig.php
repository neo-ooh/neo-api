<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ReachConfig.php
 */

namespace Neo\Modules\Properties\Services\Reach;

use Neo\Modules\Properties\Services\InventoryConfig;
use Neo\Modules\Properties\Services\InventoryType;

class ReachConfig extends InventoryConfig {
    public InventoryType $type = InventoryType::Reach;

    public function __construct(
        public string $name,
        public int    $inventoryID,
        public string $inventoryUUID,
        public string $auth_url,
        public string $api_url,
        public string $api_username,
        public string $api_key,
        public string $publisher_id,
        public string $client_id,
    ) {
    }

    public function getClient() {
        return new ReachClient($this);
    }
}
