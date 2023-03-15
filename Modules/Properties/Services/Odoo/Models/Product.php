<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - Product.php
 */

namespace Neo\Modules\Properties\Services\Odoo\Models;

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
 * @property array  $shopping_center_id
 */
class Product extends OdooModel {
    public static string $slug = "product.template";

    protected static array $filters = [
        ["active", "=", true],
        ["is_a_neo_rental_product", "=", true],
    ];

    public function getType(): \Neo\Modules\Properties\Enums\ProductType {
        return match ($this->product_type_id[0]) {
            1 => \Neo\Modules\Properties\Enums\ProductType::Digital,
            2 => \Neo\Modules\Properties\Enums\ProductType::Static,
            3 => \Neo\Modules\Properties\Enums\ProductType::Specialty,
        };
    }
}
