<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - BroadcastResource.php
 */

namespace Neo\Modules\Broadcast\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Neo\Modules\Broadcast\Enums\BroadcastResourceType;

/**
 * @property int                   $id
 * @property BroadcastResourceType $type
 */
class BroadcastResource extends Model {
    protected $table = "broadcast_resources";

    protected $casts = [
        "type" => BroadcastResourceType::class,
    ];

    protected $fillable = [
        "type",
    ];

    public $timestamps = false;

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    /**
     * @return HasMany<BroadcastJob>
     */
    public function jobs(): HasMany {
        return $this->hasMany(BroadcastJob::class, "resource_id", "id");
    }

    /**
     * @return HasMany<ExternalResource>
     */
    public function external_representations(): HasMany {
        return $this->hasMany(ExternalResource::class, "resource_id", "id")->withTrashed();
    }
}
