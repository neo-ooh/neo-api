<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - DisplayType.php
 */

namespace Neo\Modules\Broadcast\Services\BroadSign\Models;

use Illuminate\Support\Collection;
use Neo\Modules\Broadcast\Enums\ExternalResourceType;
use Neo\Modules\Broadcast\Services\BroadSign\API\BroadSignClient;
use Neo\Modules\Broadcast\Services\BroadSign\API\BroadSignEndpoint as Endpoint;
use Neo\Modules\Broadcast\Services\BroadSign\API\Parsers\SingleResourcesParser;
use Neo\Modules\Broadcast\Services\ResourceCastable;
use Neo\Modules\Broadcast\Services\Resources\DisplayType as DisplayTypeResource;
use Neo\Services\API\Parsers\MultipleResourcesParser;
use Spatie\DataTransferObject\Exceptions\UnknownProperties;

/**
 * Class Support
 *
 * @implements ResourceCastable<DisplayTypeResource>
 *
 * @property bool   $active
 * @property int    $container_id
 * @property int    $domain_id
 * @property bool   $enforce_orientation
 * @property bool   $enforce_resolution
 * @property int    $id
 * @property string $name
 * @property int    $orientation
 * @property int    $res_height
 * @property int    $res_width
 *
 * @method static Collection<static> all(BroadSignClient $client)
 * @method static static|null get(BroadSignClient $client, $formatId)
 * @method static array<static> get_multiple(BroadSignClient $client, array $payload)
 *
 */
class DisplayType extends BroadSignModel implements ResourceCastable {

    protected static string $unwrapKey = "display_unit_type";

    protected static function actions(): array {
        return [
            "all"          => Endpoint::get("/display_unit_type/v6")
                                      ->unwrap(static::$unwrapKey)
                                      ->parser(new MultipleResourcesParser(static::class)),
            "get"          => Endpoint::get("/display_unit_type/v6/{id}")
                                      ->unwrap(static::$unwrapKey)
                                      ->parser(new SingleResourcesParser(static::class))
                                      ->cache(3600),
            "get_multiple" => Endpoint::get("/display_unit_type/v6/by_id")
                                      ->unwrap(static::$unwrapKey)
                                      ->parser(new MultipleResourcesParser(static::class)),
        ];
    }

    /**
     * @param BroadSignClient $client
     * @param array<int>      $displayTypesIds
     * @return array<static>
     */
    public static function getMultiple(BroadSignClient $client, array $displayTypesIds): array {
        return static::get_multiple($client, ["ids" => implode(",", $displayTypesIds)]);
    }

    /**
     * @return DisplayTypeResource
     * @throws UnknownProperties
     */
    public function toResource(): DisplayTypeResource {
        return new DisplayTypeResource([
            "broadcaster_id" => $this->getBroadcasterId(),
            "type"           => ExternalResourceType::DisplayType,
            "external_id"    => $this->getKey(),
            "name"           => $this->name,
        ]);
    }
}
