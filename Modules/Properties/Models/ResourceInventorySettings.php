<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ResourceInventorySettings.php
 */

namespace Neo\Modules\Properties\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Neo\Models\Traits\HasCreatedByUpdatedBy;

/**
 * @property int    $resource_id
 * @property int    $inventory_id
 * @property bool   $is_enabled
 * @property bool   $pull_enabled
 * @property bool   $push_enabled
 * @property bool   $auto_import_products
 * @property array  $settings
 *
 * @property Carbon $created_at
 * @property int    $created_by
 * @property Carbon $updated_at
 * @property int    $updated_by
 */
class ResourceInventorySettings extends Pivot {
    use HasCreatedByUpdatedBy;

    protected $table = "resource_inventories_settings";

    protected $primaryKey = null;

    protected $fillable = [
        "resource_id",
        "inventory_id",
        "is_enabled",
        "pull_enabled",
        "push_enabled",
        "auto_import_products",
        "settings",
    ];

    protected $casts = [
        "is_enabled"           => "boolean",
        "pull_enabled"         => "boolean",
        "push_enabled"         => "boolean",
        "auto_import_products" => "boolean",
        "settings"             => "array",
    ];
}
