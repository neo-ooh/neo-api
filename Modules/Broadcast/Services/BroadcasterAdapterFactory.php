<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
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
     * @throws InvalidBroadcasterAdapterException
     */
    public static function make(BroadcasterConnection $connection, Network $network) {
        /** @var array<string, class-string> $adapters */
        $adapters = config("broadcast.adapters");

        /** @var class-string<BroadcasterOperator>|null $adapter */
        $adapter = $adapters[$connection->broadcaster->value] ?? null;

        if (!$adapter) {
            throw new InvalidBroadcasterAdapterException($connection->broadcaster);
        }

        $config = $adapter::buildConfig($connection, $network);

        return new $adapter($connection->broadcaster, $config);
    }

    /**
     * @throws InvalidBroadcasterAdapterException
     */
    public static function makeForNetwork(int $networkId) {
        /** @var Network $network */
        $network = Network::query()->with(["broadcaster_connection"])->find($networkId);

        return static::make($network->broadcaster_connection, $network);
    }
}
