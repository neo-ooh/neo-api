<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - OdooConfig.php
 */

namespace Neo\Modules\Properties\Services\Odoo;

use Edujugon\Laradoo\Exceptions\OdooException;
use Neo\Modules\Properties\Services\InventoryConfig;
use Neo\Modules\Properties\Services\InventoryType;
use Neo\Modules\Properties\Services\Odoo\API\OdooClient;

class OdooConfig extends InventoryConfig {
    public InventoryType $type = InventoryType::Odoo;

    public function __construct(
        public string $name,
        public int    $inventoryID,
        public string $inventoryUUID,
        public string $api_url,
        public string $api_username,
        public string $api_key,
        public string $database,
    ) {
    }

    /**
     * @throws OdooException
     */
    public function getClient(): OdooClient {
        return new OdooClient(
            url         : $this->api_url,
            db          : $this->database,
            userLogin   : $this->api_username,
            userPassword: $this->api_key,
        );
    }
}
