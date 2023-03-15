<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - InventoryResourceEvent.php
 */

namespace Neo\Modules\Properties\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int     $id
 * @property int     $resource_id
 * @property int     $inventory_id
 * @property string  $event_type
 * @property boolean $is_success
 * @property array   $result
 * @property Carbon  $triggered_at
 * @property int     $triggered_by
 */
class InventoryResourceEvent extends Model {
    protected $table = "inventory_resources_events";

    protected $primaryKey = "id";

    public $timestamps = false;

    protected $casts = [
        "is_success"   => "boolean",
        "result"       => "array",
        "triggered_at" => "datetime",
    ];

    protected $fillable = [
        "resource_id",
        "inventory_id",
        "event_type",
        "is_success",
        "result",
        "triggered_at",
        "triggered_by",
    ];

    public function resource(): BelongsTo {
        return $this->belongsTo(InventoryResource::class, "resource_id", "id");
    }

    public function inventory(): BelongsTo {
        return $this->belongsTo(InventoryProvider::class, "inventory_id", "id");
    }
}
