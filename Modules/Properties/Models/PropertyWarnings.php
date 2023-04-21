<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - PropertyWarnings.php
 */

namespace Neo\Modules\Properties\Models;

use Neo\Models\DBView;

/**
 * @property-read int  $property_id
 * @property-read bool $missing_opening_hours
 * @property-read bool $missing_products_locations
 * @property-read bool $missing_tenants
 * @property-read bool $incomplete_traffic
 */
class PropertyWarnings extends DBView {
    protected $table = "properties_warnings";

    protected $casts = [
        "missing_opening_hours"      => "boolean",
        "missing_products_locations" => "boolean",
        "missing_tenants"            => "boolean",
        "incomplete_traffic"         => "boolean",
    ];
}
