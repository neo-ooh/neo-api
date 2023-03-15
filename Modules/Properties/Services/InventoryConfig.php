<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - InventoryConfig.php
 */

namespace Neo\Modules\Properties\Services;

abstract class InventoryConfig {
    /**
     * @var InventoryType The type of the adapter: Odoo, Hivestack, etc.
     */
    public InventoryType $type;

    /**
     * @var string Name of the connection as set in Connect
     */
    public string $name;

    /**
     * @var int Connect ID for this inventory
     */
    public int $inventoryID;

    /**
     * @var string Connect UUID for this inventory
     */
    public string $inventoryUUID;
}
