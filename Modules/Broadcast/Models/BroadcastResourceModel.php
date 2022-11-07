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
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Neo\Models\SecuredModel;
use Neo\Modules\Broadcast\Enums\BroadcastResourceType;

/**
 * Umbrella model for resources using the `BroadcastResource` ID pool.
 * The `resourceType` property must be defined in the OdooModel implementation
 *
 * @property int                          $id
 *
 * @property Collection<ExternalResource> $external_representations
 * @property Collection<BroadcastTag>     $broadcast_tags
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
                "type" => $model->resourceType,
            ]);

            $model->id = $resource->getKey();
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    /**
     * @return HasMany<ExternalResource>
     */
    public function external_representations(): HasMany {
        return $this->hasMany(ExternalResource::class, "resource_id", "id");
    }

    /**
     * @return BelongsToMany<BroadcastTag>
     */
    public function broadcast_tags(): BelongsToMany {
        return $this->belongsToMany(BroadcastTag::class, 'broadcast_resource_tags', 'resource_id', 'broadcast_tag_id');
    }

    /**
     * @return HasMany<ResourcePerformance>
     */
    public function performances(): HasMany {
        return $this->hasMany(ResourcePerformance::class, "resource_id", "id");
    }
}
