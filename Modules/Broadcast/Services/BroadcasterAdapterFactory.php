<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - BroadcasterAdapterFactory.php
 */

namespace Neo\Modules\Broadcast\Services;

use Neo\Modules\Broadcast\Exceptions\InvalidBroadcasterAdapterException;
use Neo\Modules\Broadcast\Models\BroadcasterConnection;
use Neo\Modules\Broadcast\Models\Network;

class BroadcasterAdapterFactory {
    /**
     * Give a new broadcaster operator for the given network.
     * Return type is set as mixed, as the returned broadcaster instance may have different interfaces depending
     * on its capabilities.
     *
     * @throws InvalidBroadcasterAdapterException
     */
    public static function make(BroadcasterConnection $connection, Network $network) {
        /** @var array<string, class-string> $adapters */
        $adapters = config("broadcast.adapters");

        /** @var class-string<BroadcasterOperator>|null $adapter */
        $adapter = $adapters[$connection->broadcaster->value] ?? null;

        if (!$adapter) {
            throw new InvalidBroadcasterAdapterException($connection->broadcaster->value);
        }

        $config = $adapter::buildConfig($connection, $network);

        return new $adapter($connection->broadcaster, $config);
    }

    /**
     * Give a new broadcaster operator for the given network.
     * Return type is set as mixed, as the returned broadcaster instance may have different interfaces depending
     * on its capabilities.
     *
     * @throws InvalidBroadcasterAdapterException
     */
    public static function makeForNetwork(int $networkId): mixed {
        /** @var Network $network */
        $network = Network::query()->with(["broadcaster_connection"])
                          ->find($networkId);

        return static::make($network->broadcaster_connection, $network);
    }

    /**
     * Builds a BroadacsterOperator instance for the provided broadcaster Id.
     * The specific network attached to the returned instance can be any of the networks of the provider, no guarantee is
     * provided here.
     *
     * @throws InvalidBroadcasterAdapterException
     */
    public static function makeForBroadcaster(int $broadcasterId) {
        /** @var Network|null $network */
        $network = Network::query()
                          ->with(["broadcaster_connection"])
                          ->where("connection_id", "=", $broadcasterId)
                          ->first();

        if (!$network) {
            throw new InvalidBroadcasterAdapterException("#$broadcasterId");
        }

        return static::make($network->broadcaster_connection, $network);
    }
}
