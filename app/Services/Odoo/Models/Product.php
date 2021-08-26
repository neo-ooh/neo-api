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
 * @property int $sequence
 * @property string $description
 * @property bool $bonus
 * @property mixed $product_type_id
 */
class Product extends Model {
    protected static string $slug = "product.template";

    protected static array $filters = [
        ["active", "=", true],
        ["is_a_neo_rental_product", "=", true],
    ];
}
