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
use Neo\Helpers\Relation;
use Neo\Models\Actor;
use Neo\Models\Traits\HasPublicRelations;

/**
 * @property int         $id
 * @property int         $resource_id
 * @property int         $inventory_id
 * @property string      $event_type
 * @property boolean     $is_success
 * @property array       $result
 * @property Carbon      $triggered_at
 * @property int|null    $triggered_by
 * @property Carbon|null $reviewed_at
 * @property int|null    $reviewed_by
 */
class InventoryResourceEvent extends Model {
    use HasPublicRelations;

    protected $table = "inventory_resources_events";

    protected $primaryKey = "id";

    public $timestamps = false;

    protected $casts = [
        "is_success"   => "boolean",
        "result"       => "array",
        "triggered_at" => "datetime",
        "reviewed_at"  => "datetime",
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

    public function getPublicRelations(): array {
        return [
            "actor"    => Relation::make(load: "actor"),
            "product"  => Relation::make(load: "resource.product.property"),
            "reviewer" => Relation::make(load: "reviewer"),
        ];
    }

    public function resource(): BelongsTo {
        return $this->belongsTo(InventoryResource::class, "resource_id", "id");
    }

    public function inventory(): BelongsTo {
        return $this->belongsTo(InventoryProvider::class, "inventory_id", "id");
    }

    public function actor(): BelongsTo {
        return $this->belongsTo(Actor::class, "triggered_by", "id");
    }

    public function reviewer(): BelongsTo {
        return $this->belongsTo(Actor::class, "reviewed_by", "id");
    }
}
