<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ProductType.php
 */

namespace Neo\Models\Odoo;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $odoo_id
 * @property string $name
 * @property string $internal_name
 */
class ProductType extends Model {
    protected $table = "odoo_product_types";

    protected $fillable = [
        "odoo_id",
        "name",
        "internal_name",
    ];
}
