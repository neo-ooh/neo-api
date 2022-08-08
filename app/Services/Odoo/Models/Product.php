<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - Product.php
 */

namespace Neo\Services\Odoo\Models;

use Neo\Services\Odoo\OdooModel;

/**
 * @property int    $id
 * @property string $name
 * @property int    $sequence
 * @property string $description
 * @property bool   $bonus
 * @property mixed  $product_type_id
 * @property mixed  $categ_id
 * @property int    $nb_screen
 * @property int    $nb_spots
 * @property int    $nb_extra_spots
 * @property int    $message_duration
 * @property int    $list_price
 * @property array  $product_variant_id
 * @property array  $linked_product_id
 */
class Product extends OdooModel {
    public static string $slug = "product.template";

    protected static array $filters = [
        ["active", "=", true],
        ["is_a_neo_rental_product", "=", true],
    ];
}
