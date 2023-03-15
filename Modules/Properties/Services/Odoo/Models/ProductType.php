<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ProductType.php
 */

namespace Neo\Modules\Properties\Services\Odoo\Models;

/**
 * @property int    $id
 * @property string $name
 * @property string $display_name
 */
class ProductType extends OdooModel {
    public static string $slug = "product.type";

    protected static array $filters = [];
}
