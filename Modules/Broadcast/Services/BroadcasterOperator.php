<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - BroadcasterAdapter.php
 */

namespace Neo\Modules\Broadcast\Services;

use Neo\Modules\Broadcast\Enums\ExternalResourceType;
use Neo\Modules\Broadcast\Models\BroadcasterConnection;
use Neo\Modules\Broadcast\Models\Network;
use Neo\Modules\Broadcast\Services\Exceptions\MissingExternalResourceException;
use Neo\Modules\Broadcast\Services\Resources\ExternalBroadcasterResourceId;

/**
 * @template TConfig of BroadcasterConfig
 */
abstract class BroadcasterOperator {
    /**
     * @var array<BroadcasterCapability>
     */
    protected array $capabilities;

    /**
     * @param BroadcasterType $broadcasterType
     * @param TConfig         $config
     */
    public function __construct(protected BroadcasterType $broadcasterType, protected BroadcasterConfig $config) {
    }

    /**
     * @param \Neo\Modules\Broadcast\Models\BroadcasterConnection $connection
     * @param Network                                             $network
     * @return TConfig
     */
    abstract public static function buildConfig(BroadcasterConnection $connection, Network $network): BroadcasterConfig;

    public function getType(): BroadcasterType {
        return $this->broadcasterType;
    }

    public function getBroadcasterId(): int {
        return $this->config->connectionID;
    }

    public function getNetworkId(): int {
        return $this->config->networkID;
    }

    /**
     * @return TConfig
     */
    public function getConfig(): BroadcasterConfig {
        return $this->config;
    }

    /**
     * Tell if the broadcaster has the specified capability
     *
     * @param BroadcasterCapability $capability
     * @return bool
     */
    public function hasCapability(BroadcasterCapability $capability): bool {
        return in_array($capability, $this->capabilities, true);
    }

    /**
     * From a list of `ExternalBroadcasterResourceId`, get the first one matching the given type, throw if none can be found
     *
     * @param array<ExternalBroadcasterResourceId> $resources
     * @param ExternalResourceType                 $type
     * @return ExternalBroadcasterResourceId
     * @throw MissingExternalResourceException
     */
    protected function getResourceByType(array $resources, ExternalResourceType $type): ExternalBroadcasterResourceId {
        $matches = array_filter($resources, static fn(ExternalBroadcasterResourceId $resource) => $resource->type = $type);

        if (count($matches) === 0) {
            throw new MissingExternalResourceException($this->broadcasterType, $type);
        }

        return $matches[0];
    }
}
