<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - BroadcastResourceModel.php
 */

namespace Neo\Modules\Broadcast\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Neo\Models\SecuredModel;
use Neo\Modules\Broadcast\Enums\BroadcastResourceType;

/**
 * Umbrella model for resources using the `BroadcastResource` ID pool.
 * The `resourceType` property must be defined in the Model implementation
 *
 * @property int                          $id;
 *
 * @property Collection<ExternalResource> $external_representations
 */
abstract class BroadcastResourceModel extends SecuredModel {
    /**
     * Disable auto-increment
     *
     * @var bool
     */
    public $incrementing = false;

    public BroadcastResourceType $resourceType;

    protected static function booted(): void {
        parent::boot();

        static::creating(static function (BroadcastResourceModel $model) {
            $resource = BroadcastResource::query()->create([
                "type" => $model->resourceType
            ]);

            $model->id = $resource->getKey();
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    public function external_representations(): HasMany {
        return $this->hasMany(ExternalResource::class, "id", "id");
    }
}
