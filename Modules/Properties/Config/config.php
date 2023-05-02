<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - config.php
 */

use Neo\Modules\Properties\Services\Dummy\DummyAdapter;
use Neo\Modules\Properties\Services\Hivestack\HivestackAdapter;
use Neo\Modules\Properties\Services\InventoryType;
use Neo\Modules\Properties\Services\Odoo\OdooAdapter;
use Neo\Modules\Properties\Services\Reach\ReachAdapter;

return [
    'name' => 'Properties',

    "adapters" => [
        InventoryType::Odoo->value      => OdooAdapter::class,
        InventoryType::Hivestack->value => HivestackAdapter::class,
        InventoryType::Reach->value     => ReachAdapter::class,

        InventoryType::Dummy->value => DummyAdapter::class,
    ],
];
