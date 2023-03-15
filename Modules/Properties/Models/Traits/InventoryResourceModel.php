<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - InventoryResourceModel.php
 */

namespace Neo\Modules\Properties\Models\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Neo\Modules\Properties\Models\ExternalInventoryResource;
use Neo\Modules\Properties\Models\InventoryResource;
use Neo\Modules\Properties\Models\ResourceInventorySettings;
use Neo\Modules\Properties\Services\Resources\Enums\InventoryResourceType;

/**
 * @property InventoryResourceType $inventoryResourceType
 * @mixin Model
 */
trait InventoryResourceModel {
    protected static function bootInventoryResourceModel(): void {
        static::creating(static function (Model $model) {
            $resource = InventoryResource::query()
                                         ->create([
                                                      "type" => $model->inventoryResourceType->value,
                                                  ]);

            $model->inventory_resource_id = $resource->getKey();
        });
    }

    /**
     * @return HasOne<InventoryResource>
     */
    public function inventory_resource(): HasOne {
        return $this->hasOne(InventoryResource::class, "id", "inventory_resource_id");
    }

    /**
     * @return HasMany<ResourceInventorySettings>
     */
    public function inventories_settings(): HasMany {
        return $this->hasMany(ResourceInventorySettings::class, "resource_id", "inventory_resource_id");
    }

    /**
     * @return HasMany<ExternalInventoryResource>
     */
    public function external_representations(): HasMany {
        return $this->hasMany(ExternalInventoryResource::class, "resource_id", "inventory_resource_id");
    }
}
