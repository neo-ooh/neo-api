<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - InventoryAdapterFactory.php
 */

namespace Neo\Modules\Properties\Services;

use Neo\Modules\Properties\Models\InventoryProvider;
use Neo\Modules\Properties\Services\Exceptions\InvalidInventoryAdapterException;

class InventoryAdapterFactory {
    /**
     * Instantiate an Inventory adapter for the provided `InventoryProvider`
     *
     * @param InventoryProvider $provider
     * @return InventoryAdapter
     * @throws InvalidInventoryAdapterException
     */
    public static function make(InventoryProvider $provider): InventoryAdapter {
        /** @var array<string, class-string<InventoryAdapter>> $adapters */
        $adapters = config("properties.adapters");

        /** @var class-string<InventoryAdapter>|null $adapter */
        $adapter = $adapters[$provider->provider->value] ?? null;

        if (!$adapter) {
            throw new InvalidInventoryAdapterException($provider->provider->value);
        }

        $config   = $adapter::buildConfig($provider);
        $delegate = new InventoryAdapterDelegate($provider);

        return new $adapter($config, $delegate);
    }
}
