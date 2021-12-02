<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - Creative.php
 */

namespace Neo\Services\Broadcast\BroadSign\Models;

use JsonException;
use Log;
use Neo\Services\API\Parsers\MultipleResourcesParser;
use Neo\Services\Broadcast\BroadSign\API\BroadsignClient;
use Neo\Services\Broadcast\BroadSign\API\BroadSignEndpoint as Endpoint;
use Neo\Services\Broadcast\BroadSign\API\Parsers\ResourceIDParser;
use Neo\Services\Broadcast\BroadSign\API\Parsers\SingleResourcesParser;

/**
 * Class Creatives
 *
 * @package Neo\BroadSign\Models
 *
 * @property bool   $active
 * @property int    $approval_status
 * @property int    $approved_by_user_id
 * @property string $approved_on_utc
 * @property int    $archive_priority
 * @property int    $archive_status
 * @property int    $archived_by
 * @property string $archived_on_utc
 * @property string $attributes
 * @property int    $bmb_host_id
 * @property string $checksum2
 * @property int    $checksum2_type
 * @property int    $container_id
 * @property string $creation_tm
 * @property int    $creation_user_id
 * @property int    $domain_id
 * @property string $external_id
 * @property string $feeds
 * @property int    $id
 * @property string $mime
 * @property string $name
 * @property string $originalfilename
 * @property int    $parent_id
 * @property int    $size
 */
class Creative extends BroadSignModel {

    protected static string $unwrapKey = "content";

    protected static array $updatable = [
        "active",
        "approval_status",
        "archive_priority",
        "attributes",
        "bmb_host_id",
        "container_id",
        "domain_id",
        "external_id",
        "parent_id",
        "feeds",
        "id",
        "name",
    ];

    protected static function actions(): array {
        return [
            "all"                 => Endpoint::get("/content/v11")
                                             ->unwrap(static::$unwrapKey)
                                             ->parser(new MultipleResourcesParser(static::class)),
            "import_from_url"     => Endpoint::post("/content/v11/import_from_url")
                                             ->unwrap(static::$unwrapKey)
                                             ->parser(new ResourceIDParser()),
            "create_dynamic"      => Endpoint::post("/content/v11/add")
                                             ->unwrap(static::$unwrapKey)
                                             ->parser(new ResourceIDParser())
                                             ->multipart(),
            "get"                 => Endpoint::get("/content/v11/{id}")
                                             ->unwrap(static::$unwrapKey)
                                             ->parser(new SingleResourcesParser(static::class)),
            "update"              => Endpoint::put("/content/v11")
                                             ->unwrap(static::$unwrapKey)
                                             ->parser(new SingleResourcesParser(static::class)),
            "addResourceCriteria" => Endpoint::post("/resource_criteria/v7/add"),
        ];
    }

    /**
     * Imports the creative in BroadSign.
     * The `attributes`, `name`, `parent_id` and `url` are required to be set to use thi smethod
     * The `url` attribute must be a valid URL to the creative file.
     *
     * @return mixed
     */
    public function import() {
        return static::import_from_url([
            "attributes" => $this->attributes,
            "name"       => $this->name,
            "parent_id"  => $this->parent_id,
            "url"        => $this->url,
        ]);
    }

    /**
     * Creates a new dynamic creative (External Ad-Copy) in broadsign and returns its ID.
     *
     * @param BroadsignClient $client
     * @param string          $name
     * @param array           $attributes
     * @return array|BroadSignModel|null
     * @throws JsonException
     */
    public static function makeDynamic(BroadSignClient $client, string $name, array $attributes) {
        // I haven't been able to build a Request in the format expected by BroadSign for this action.
        // As a solution this specific request will be handled separately from the other. Instead of using GuzzleHttp, we will directly use PHP cUrl handles.

        $endpoint       = static::actions()["create_dynamic"];
        $endpoint->base = $client->getConfig()->apiURL;


        $boundary = "__X__BROADSIGN_REQUEST__";

        $req = curl_init($endpoint->getUrl());
        curl_setopt($req, CURLOPT_SSLCERT, $client->getConfig()->getCertPath());
        curl_setopt($req, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($req, CURLOPT_RETURNTRANSFER, 1);

        $body = [
            'metadata' => json_encode([
                "name"         => $name,
                "parent_id"    => $client->getConfig()->customerId,
                "container_id" => $client->getConfig()->adCopiesContainerId,
                "size"         => "-1",
                "mime"         => "",
                "attributes"   => http_build_query($attributes, '', '\n')
            ], JSON_THROW_ON_ERROR),
            'file'     => 'C:\\void',
        ];
        curl_setopt($req, CURLOPT_POST, 1);
        curl_setopt($req, CURLOPT_POSTFIELDS, $body);
        curl_setopt($req, CURLOPT_HEADER, "Content-Type: multipart/mixed; boundary=" . $boundary);

        $response = curl_exec($req);

        Log::debug('HTTP Status Code: ' . curl_getinfo($req, CURLINFO_HTTP_CODE) . PHP_EOL);
        Log::debug($response);

        curl_close($req);

        $jsonResponse = json_decode($response, true, 512, JSON_THROW_ON_ERROR);
        $responseBody = $jsonResponse[$endpoint->unwrapKey];
        $responseBody = call_user_func($endpoint->parse, $responseBody);

        return $responseBody;
    }

    /**
     * @param int $criteriaID
     * @param int $type
     *
     */
    public function addCriteria(int $criteriaID, int $type): void {
        $this->addResourceCriteria([
            "active"      => true,
            "criteria_id" => $criteriaID,
            "parent_id"   => $this->id,
            "type"        => $type,
        ]);
    }

    public function approve(): void {
        $this->active           = 1;
        $this->approval_status  = 1;
        $this->archive_priority = 1;
        $this->external_id      = "";
        $this->save();
    }
}
