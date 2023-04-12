<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - DestroyProductJob.php
 */

namespace Neo\Modules\Properties\Jobs\Products;

use Illuminate\Contracts\Queue\ShouldBeUniqueUntilProcessing;
use Neo\Modules\Properties\Jobs\InventoryJobBase;
use Neo\Modules\Properties\Jobs\InventoryJobType;
use Neo\Modules\Properties\Models\ExternalInventoryResource;
use Neo\Modules\Properties\Models\InventoryProvider;
use Neo\Modules\Properties\Services\Exceptions\InvalidInventoryAdapterException;

class DestroyProductJob extends InventoryJobBase implements ShouldBeUniqueUntilProcessing {
    public function __construct(
        private readonly int $resourceID,
        private readonly int $inventoryID,
    ) {
        parent::__construct(InventoryJobType::Destroy, $this->resourceID, $this->inventoryID);
    }

    public function uniqueId() {
        return "$this->resourceID-$this->inventoryID";
    }

    /**
     * @return mixed
     * @throws InvalidInventoryAdapterException
     */
    protected function run(): mixed {
        // Find an active representation for the representation for the given inventory
        $provider = InventoryProvider::query()->findOrFail($this->inventoryID);

        /** @var ExternalInventoryResource|null $representation */
        $representation = ExternalInventoryResource::query()
                                                   ->where("inventory_id", "=", $this->inventoryID)
                                                   ->where("resource_id", "=", $this->resourceID)
                                                   ->first();

        if (!$representation) {
            return ["result" => "nothing to do"];
        }

        $inventory = $provider->getAdapter();
        $result    = $inventory->removeProduct($representation->toInventoryResourceId());

        $representation->delete();

        return ["result" => $result];
    }
}
