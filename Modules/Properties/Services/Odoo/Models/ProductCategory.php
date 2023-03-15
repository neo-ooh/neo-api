<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ProductCategory.php
 */

namespace Neo\Modules\Properties\Services\Odoo\Models;

/**
 * @property int    $id
 * @property string $name
 * @property string $complete_name
 * @property string $display_name
 * @property int    $parent_id
 * @property int    $parent_path
 * @property int    $child_id
 * @property int    $product_count
 */
class ProductCategory extends OdooModel {
    public static string $slug = "product.category";

    protected static array $filters = [];
}
