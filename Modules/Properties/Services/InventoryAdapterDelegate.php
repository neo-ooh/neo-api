<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - InventoryAdapterDelegate.php
 */

namespace Neo\Modules\Properties\Services;

use Illuminate\Cache\TaggedCache;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Neo\Modules\Properties\Models\InventoryProvider;

/**
 * This delegate is provided to each InventoryAdapter on instantiation.
 * All actions requiring the use of the Laravel application (Eloquent, Cache, etc) should go through this file
 */
class InventoryAdapterDelegate {
    /**
     * @var Collection<InventoryProvider>|null
     */
    protected Collection|null $inventories = null;

    public function __construct(protected InventoryProvider $provider) {
    }

    /**
     * @return TaggedCache Get a cache instance for this inventory to cache stuff
     */
    public function getCache() {
        return Cache::tags([$this->provider->provider->value . "-data", "inventory-{$this->provider->getKey()}"]);
    }

    /**
     * Give the type of inventory based on its ID
     *
     * @param int $inventoryID
     * @return InventoryType|null
     */
    public function getInventoryProviderType(int $inventoryID): InventoryType|null {
        if (!$this->inventories) {
            $this->inventories = InventoryProvider::query()->get();
        }

        return $this->inventories->firstWhere("id", "=", $inventoryID)->provider ?? null;
    }
}
