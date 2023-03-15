<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - OrderLine.php
 */

namespace Neo\Modules\Properties\Services\Odoo\Models;

use Neo\Services\Odoo\OdooModel;

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
 * @property int    $over_qty
 * @property int    $product_uom_qty
 * @property int    $customer_lead
 * @property string $rental_start
 * @property string $rental_end
 * @property int    $is_rental_line
 * @property int    $is_linked_line
 * @property int    $discount
 * @property int    $nb_weeks
 * @property int    $nb_screen
 * @property int    $connect_impression
 * @property int    $impression
 * ...
 */
class OrderLine extends OdooModel {
    public static string $slug = "sale.order.line";

    protected static array $filters = [];
}
