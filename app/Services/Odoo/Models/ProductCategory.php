<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - Property.php
 */

namespace Neo\Services\Odoo\Models;

use Neo\Services\API\Odoo\Model;

/**
 * @property int $id
 * @property string $name
 * @property string $complete_name
 * @property string $display_name
 * @property int $parent_id
 * @property int $parent_path
 * @property int $child_id
 * @property int $product_count
 */
class ProductCategory extends Model {
    public static string $slug = "product.category";

    protected static array $filters = [];
}
