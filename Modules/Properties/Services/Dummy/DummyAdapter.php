<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - DummyAdapter.php
 */

namespace Neo\Modules\Properties\Services\Dummy;

use Neo\Modules\Properties\Models\InventoryProvider;
use Neo\Modules\Properties\Services\InventoryAdapter;
use Neo\Modules\Properties\Services\InventoryConfig;

/**
 * @extends InventoryAdapter<DummyConfig>
 */
class DummyAdapter extends InventoryAdapter {
    protected array $capabilities = [];

    /**
     * @inheritDoc
     */
    public static function buildConfig(InventoryProvider $provider): InventoryConfig {
        return new DummyConfig(
            name         : $provider->name,
            inventoryID  : $provider->id,
            inventoryUUID: $provider->uuid,
        );
    }
}
