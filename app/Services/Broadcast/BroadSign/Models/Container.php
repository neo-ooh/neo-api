<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - Container.php
 */

namespace Neo\Services\Broadcast\BroadSign\Models;

use Neo\Services\Broadcast\BroadSign\API\BroadsignClient;
use Neo\Services\Broadcast\BroadSign\API\Parsers\MultipleResourcesParser;
use Neo\Services\Broadcast\BroadSign\API\Parsers\SingleResourcesParser;
use Neo\Services\Broadcast\BroadSign\API\BroadSignEndpoint as Endpoint;

/**
 * A Container is a directory in BroadSign resources structure.
 *
 * @package Neo\BroadSign\Models
 *
 * @property bool        active
 * @property int         container_id
 * @property int         domain_id
 * @property int         group_id
 * @property int         id
 * @property string      name
 * @property int         parent_id
 * @property string      parent_resource_type
 *
 * @property  ?Container parent
 *
 * @method Container get(BroadsignClient $api, int $container_id)
 *
 * @link    https://docs.broadsign.com/swag/swagger-ui-master/dist/#/default/get_container_v9
 */
class Container extends BroadSignModel {
    protected static string $unwrapKey = "container";

    protected static function actions (): array {
        return [
            "get" => Endpoint::get("/container/v9/{id}")
                             ->unwrap(static::$unwrapKey)
                             ->parser(new SingleResourcesParser(static::class))
                             ->cache(3600*23),
            "byParent" => Endpoint::get("/container/v9/scoped")
                                  ->unwrap(static::$unwrapKey)
                                  ->parser(new MultipleResourcesParser(static::class))
                                  ->cache(3600*23),
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    public static function inContainer(BroadsignClient $client, $containerId) {
        return static::byParent($client, ["parent_container_ids" => $containerId]);
    }

    public function parent (): ?Container {
        if ($this->container_id === 0) {
            return null;
        }

        return $this->get($this->container_id);
    }

    /*
    |--------------------------------------------------------------------------
    | Custom Mechanisms
    |--------------------------------------------------------------------------
    */

    /**
     * Replicate itself inside our own database with all its parents. These methods can be called even if the container
     * has already been replicated, handling errors and duplications.
     */
    public function replicate (): void {
        // Make sure our parent container is already in the DDB if we have one
        if ($this->container_id !== 0) {
            $this->parent->replicate();
        }

        \Neo\Models\Container::query()->updateOrInsert([
            "id" => $this->id,
        ],
            [
                "parent_id" => $this->container_id === 0 ? null : $this->container_id,
                "name"      => $this->name,
            ]);
    }
}
