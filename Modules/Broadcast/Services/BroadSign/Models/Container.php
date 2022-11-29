<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - Container.php
 */

namespace Neo\Modules\Broadcast\Services\BroadSign\Models;

use Illuminate\Support\Collection;
use Neo\Modules\Broadcast\Enums\ExternalResourceType;
use Neo\Modules\Broadcast\Services\BroadSign\API\BroadSignClient;
use Neo\Modules\Broadcast\Services\BroadSign\API\BroadSignEndpoint as Endpoint;
use Neo\Modules\Broadcast\Services\BroadSign\API\Parsers\SingleResourcesParser;
use Neo\Modules\Broadcast\Services\ResourceCastable;
use Neo\Modules\Broadcast\Services\Resources\Container as ContainerResource;
use Neo\Services\API\Parsers\MultipleResourcesParser;
use Spatie\DataTransferObject\Exceptions\UnknownProperties;

/**
 * A Container is a directory in BroadSign resources structure.
 *
 * @implements ResourceCastable<ContainerResource>
 *
 * @property bool   $active
 * @property int    $container_id
 * @property int    $domain_id
 * @property int    $group_id
 * @property int    $id
 * @property string $name
 * @property int    $parent_id
 * @property string $parent_resource_type
 *
 * @method static static|null get(BroadSignClient $api, int $container_id)
 * @method static Collection<static> byParent(BroadSignClient $api, array $params)
 *
 * @link    https://docs.broadsign.com/swag/swagger-ui-master/dist/#/default/get_container_v9
 */
class Container extends BroadSignModel implements ResourceCastable {
    protected static string $unwrapKey = "container";

    protected static function actions(): array {
        return [
            "get"      => Endpoint::get("/container/v9/{id}")
                                  ->unwrap(static::$unwrapKey)
                                  ->parser(new SingleResourcesParser(static::class))
                                  ->cache(3600 * 23),
            "byParent" => Endpoint::get("/container/v9/scoped")
                                  ->unwrap(static::$unwrapKey)
                                  ->parser(new MultipleResourcesParser(static::class)),
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    /**
     * @param BroadSignClient $client
     * @param int             $containerId
     * @return Collection<static>
     */
    public static function inContainer(BroadSignClient $client, int $containerId): Collection {
        return static::byParent($client, ["parent_container_ids" => $containerId]);
    }

    /**
     * @return static|null
     */
    public function getParent(): Container|null {
        if ($this->container_id === 0) {
            return null;
        }

        return static::get($this->api, $this->container_id);
    }

    /*
    |--------------------------------------------------------------------------
    | Custom Mechanisms
    |--------------------------------------------------------------------------
    */

    /**
     * @throws UnknownProperties
     */
    public function toResource(): ContainerResource {
        return new ContainerResource([
            "broadcaster_id" => $this->getBroadcasterId(),
            "external_id"    => $this->getKey(),
            "name"           => $this->name,
            "parent"         => $this->container_id ? [
                "broadcaster_id" => $this->getBroadcasterId(),
                "type"           => ExternalResourceType::Container,
                "external_id"    => $this->container_id,
            ] : null,
        ]);
    }
}
