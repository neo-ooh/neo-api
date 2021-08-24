<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - Property.php
 */

namespace Neo\Services\Broadcast\Odoo\Models;

use Neo\Services\API\Odoo\Model;

class Property extends Model {
    protected static string $slug = "res.partner";

    protected static array $filters = [
        ["is_company", "=", true],
        ["center_type", "<>", false],
        ["center_type", "<>", "group"],
    ];


}
