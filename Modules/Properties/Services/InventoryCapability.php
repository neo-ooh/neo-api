<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - InventoryCapability.php
 */

namespace Neo\Modules\Properties\Services;

enum InventoryCapability: string {
    // Products
    case ProductsRead = "products.read";
    case ProductsWrite = "products.write";
    case ProductsQuantity = "products.quantity";
    case ProductsMediaTypes = "products.media-types";
    case ProductsAudioSupport = "products.audio-support";

    // Product Categories
    case ProductCategoriesRead = "product-categories.read";

    // Properties
    case PropertiesRead = "properties.read";
    case PropertiesProducts = "properties.products";
}
