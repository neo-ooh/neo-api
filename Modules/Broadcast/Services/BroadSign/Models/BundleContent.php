<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - Bundle.php
 */

namespace Neo\Modules\Broadcast\Services\BroadSign\Models;

use Illuminate\Support\Collection;
use Neo\Modules\Broadcast\Services\BroadSign\API\BroadSignClient;
use Neo\Modules\Broadcast\Services\BroadSign\API\BroadSignEndpoint;
use Neo\Modules\Broadcast\Services\BroadSign\API\Parsers\ResourceIDParser;
use Neo\Modules\Broadcast\Services\BroadSign\API\Parsers\SingleResourcesParser;
use Neo\Services\API\Parsers\MultipleResourcesParser;

/**
 * A Bundle Content is a link between a bundle and an ad-copy
 *
 * @property bool $active
 * @property int  $content_id Ad-Copy ID
 * @property int  $domain_id
 * @property int  $id
 * @property int  $parent_id  Bundle ID
 *
 * @method static Collection<static> byParent(BroadSignClient $client, string[] $array)
 */
class BundleContent extends BroadSignModel {
    protected static string $unwrapKey = "bundle_content";

    protected static function actions(): array {
        return [
            "all"      => BroadSignEndpoint::get("/bundle_content/v5")
                                           ->unwrap(static::$unwrapKey)
                                           ->parser(new MultipleResourcesParser(static::class)),
            "get"      => BroadSignEndpoint::get("/bundle_content/v5/{id}")
                                           ->unwrap(static::$unwrapKey)
                                           ->parser(new SingleResourcesParser(static::class)),
            "create"   => BroadSignEndpoint::post("/bundle_content/v5/add")
                                           ->unwrap(static::$unwrapKey)
                                           ->parser(new ResourceIDParser()),
            "update"   => BroadSignEndpoint::put("/bundle_content/v5")
                                           ->unwrap(static::$unwrapKey)
                                           ->parser(new SingleResourcesParser(static::class)),
            "byParent" => BroadSignEndpoint::get("/bundle_content/v5/by_parent")
                                           ->unwrap(static::$unwrapKey)
                                           ->parser(new MultipleResourcesParser(static::class)),
        ];
    }

    protected static array $updatable = [
        "active",
        "domain_id",
        "id",
    ];

    /**
     * @param BroadSignClient $client
     * @param int             $bundleId
     * @return Collection<static>
     */
    public static function byBundle(BroadSignClient $client, int $bundleId): Collection {
        return static::byParent($client, ["parent_id" => $bundleId])->where("active", "=", true);
    }
}
