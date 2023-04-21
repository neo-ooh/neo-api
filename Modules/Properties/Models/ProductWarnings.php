<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ProductWarnings.php
 */

namespace Neo\Modules\Properties\Models;

use Neo\Models\DBView;

/**
 * @property-read int  $product_id
 * @property-read bool $missing_locations
 */
class ProductWarnings extends DBView {
    protected $table = "products_warnings";

    protected $casts = [
        "missing_locations" => "boolean",
    ];
}
