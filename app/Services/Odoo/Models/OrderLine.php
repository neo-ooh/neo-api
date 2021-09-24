<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - OrderLine.php
 */

namespace Neo\Services\Odoo\Models;

use Neo\Services\API\Odoo\Model;

/**
 * @property int    $id
 * @property array  $order_id
 * @property string $name
 * @property number $sequence
 * @property array  $invoice_lines
 * @property int    $price_unit
 * @property int    $price_subtotal
 * @property int    $price_tax
 * @property int    $price_total
 * @property int    $price_reduce
 * @property array  $product_id
 * @property array  $product_template_id
 * ...
 */
class OrderLine extends Model {
    public static string $slug = "sale.order.line";

    protected static array $filters = [];
}

