<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - InventoryType.php
 */

namespace Neo\Modules\Properties\Services;

enum InventoryType: string {
    case Odoo = "odoo";
    case Hivestack = "hivestack";
    case Vistar = "vistar";
    case Reach = "reach";
    case PlaceExchange = "place-exchange";

    case Dummy = "dummy";
}
