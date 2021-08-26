<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - Province.php
 */

namespace Neo\Services\Odoo\Models;

use Neo\Services\API\Odoo\Model;

/**
 * @property int $id
 * @property array $country_id
 * @property string $code
 * @property string $name
 * @property string $display_name
 */
class Province extends Model {
    public static string $slug = "res.country.state";

    protected static array $filters = [];
}
