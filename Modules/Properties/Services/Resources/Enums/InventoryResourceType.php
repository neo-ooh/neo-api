<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - InventoryResourceType.php
 */

namespace Neo\Modules\Properties\Services\Resources\Enums;

enum InventoryResourceType: string {
    case ProductCategory = "product-category";
    case Product = "product";
    case Property = "property";

    case PropertyType = "property-type";
    case ScreenType = "screen-type";
}
