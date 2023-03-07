<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - Creative.php
 */

namespace Neo\Modules\Broadcast\Services\BroadSign\Models;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use JsonException;
use Neo\Modules\Broadcast\Enums\CreativeType;
use Neo\Modules\Broadcast\Services\BroadSign\API\BroadSignClient;
use Neo\Modules\Broadcast\Services\BroadSign\API\BroadSignEndpoint as Endpoint;
use Neo\Modules\Broadcast\Services\BroadSign\API\Parsers\ResourceIDParser;
use Neo\Modules\Broadcast\Services\BroadSign\API\Parsers\SingleResourcesParser;
use Neo\Modules\Broadcast\Services\Resources\Creative as CreativeResource;
use Neo\Modules\Broadcast\Services\Resources\CreativeStorageType;
use Neo\Services\API\Parsers\MultipleResourcesParser;
use RuntimeException;

/**
 * Remove all quotation marks (simple and double) from a string
 *
 * @param string $str
 * @return string
 */
function stripQuotes(string $str): string {
    return str_replace(["'", "\""], "", $str);
}

/**
 * Class Creatives
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
 *
 * @method static static|null get(BroadSignClient $client, int $creativeId)
 * @method null addResourceCriteria(array $payload)
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
            "addResourceCriteria" => Endpoint::post("/resource_criteria/v7/add")
                                             ->unwrap("resource_criteria")
                                             ->parser(new ResourceIDParser()),
        ];
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


    protected static function getAttributesForCreative(CreativeResource $creative, CreativeStorageType $storage) {
        $attributes           = [];
        $attributes["height"] = $creative->height;
        $attributes["width"]  = $creative->width;

        if ($storage === CreativeStorageType::Link) {
            $attributes["expire_on_empty_remote_dir"] = "false";                                // Don;t expire if connection is lost
            $attributes["io_strategy"]                = "esf";                                  // ???
            $attributes["source_append_id"]           = "false";                                // Append player ID to url (no)
            $attributes["source_expiry"]              = "0";                                    // Not sure
            $attributes["source_refresh"]             = $creative->refresh_rate_minutes;        // URL refresh interval (minutes)

            $attributes["source"] = $creative->url;             // URL to the resource
        }

        if ($creative->type === CreativeType::Static) {
            if ($creative->extension === "mp4") {
                $attributes["duration"] = $creative->length_ms;
            }
        }

        return implode('\n', array_map(static fn(string $k, string $v) => "$k=$v", array_keys($attributes), array_values($attributes)));
    }

    public static function import(BroadSignClient $client, CreativeResource $creative, CreativeStorageType $storageType): int {
        return match ($creative->type) {
            CreativeType::Static => static::importStaticCreative($client, $creative, $storageType),
            CreativeType::Url    => static::importDynamicCreative($client, $creative),
        };
    }

    /**
     * @param CreativeResource    $creative
     * @param CreativeStorageType $storageType
     * @return int ID of the created creative
     * @throws JsonException
     */
    protected static function importStaticCreative(BroadSignClient $client, CreativeResource $creative, CreativeStorageType $storageType): int {
        // Prepare the creative metadata for BroadSign
        $metadata = [
            "name"             => stripQuotes($creative->name),
            "originalfilename" => stripQuotes($creative->fileName),
            "feeds"            => "",
            "attributes"       => static::getAttributesForCreative($creative, $storageType),
            "mime"             => $creative->extension,
        ];

        switch ($storageType) {
            case CreativeStorageType::File:
                $metadata["size"] = Storage::disk("public")->size($creative->path);

                // Get the creative in a temporary file for the upload
                $tempFile = tmpfile();
                fwrite($tempFile, file_get_contents($creative->url));
                fseek($tempFile, 0);
                $creativePath = stream_get_meta_data($tempFile)['uri'];

                $response = static::executeRequest(
                    client  : $client,
                    creative: $creative,
                    payload : $metadata,
                    file    : $creativePath
                );

                fclose($tempFile);

                return $response["id"];
            case CreativeStorageType::Link:
                $metadata["size"] = "-1";
                $response         = static::executeRequest(
                    client  : $client,
                    creative: $creative,
                    payload : $metadata
                );

                return $response["id"];
        }

        return 0;
    }

    /**
     * @throws JsonException
     */
    protected static function importDynamicCreative(BroadSignClient $client, CreativeResource $creative): int {
        // Prepare the creative metadata for BroadSign
        $metadata = [
            "name"             => stripQuotes($creative->name),
            "originalfilename" => stripQuotes($creative->fileName),
            "size"             => "-1",
            "feeds"            => "",
            "attributes"       => static::getAttributesForCreative($creative, CreativeStorageType::Link),
            "mime"             => "",
        ];

        $response = static::executeRequest(
            client  : $client,
            creative: $creative,
            payload : $metadata
        );

        return $response["id"];
    }

    /**
     * @throws JsonException
     */
    protected static function executeRequest(BroadSignClient $client, CreativeResource $creative, array $payload, $file = null): array {
        // Complete the payload
        $payload["domain_id"] = $client->getConfig()->domainId;

        // If the creative is associated with an advertiser, parent it to this one, otherwise, use the default one.
        $payload["container_id"] = $creative->advertiser === null ? $client->getConfig()->adCopiesContainerId : null;
        $payload["parent_id"]    = $creative->advertiser !== null ? (int)$creative->advertiser->external_id : $client->getConfig()->customerId;

        $metadata = json_encode($payload, JSON_THROW_ON_ERROR);

        // Get the endpoint
        $endpoint       = Endpoint::post("/content/v11/add")
                                  ->unwrap("content")
                                  ->parser(new ResourceIDParser());
        $endpoint->base = $client->getConfig()->apiURL;

        // Prepare the request command
        $req          = [];
        $req[]        = "curl -s";                                                  // curl with silent output
        $req[]        = "-w '\n%{http_code}'";                                      // display http status code on 2nd line
        $req[]        = "-POST " . $endpoint->getUrl();                             // POST method + URL
        $req[]        = "-E" . $client->getConfig()->getCertPath();                 // BroadSign cert auth
        $req[]        = "-H 'Content-Type: multipart/mixed'";                       // Request Content Type
        $req[]        = "-F 'metadata=$metadata;type=application/json'";            // Request metadata
        $req[]        = $file ? "-F 'file=@$file'" : "-F 'file=dummy.txt'";         // Request file
        $curl_command = implode(" ", $req);
        $curl_command .= " 2>&1"; // Redirect error output to standard output

        // Execute the request
        $output    = [];
        $exit_code = 0;


        if (config('app.env') !== 'production') {
            Log::debug("[BroadSign] $endpoint->method@{$endpoint->getPath()}", [json_encode($payload, JSON_THROW_ON_ERROR)]);
            clock([
                      "endpoint" => "$endpoint->method@{$endpoint->getPath()}",
                      "payload"  => $payload,
                  ]);
        }

        exec($curl_command, $output, $exit_code);

        if ($exit_code !== 0 || (int)$output[1] !== 200) {
            throw new RuntimeException("Error while executing cURL request: `$curl_command`; Error:" . implode(", ", $output));
        }

        $responseBody = json_decode($output[0], true, 512, JSON_THROW_ON_ERROR)[$endpoint->unwrapKey];
        $responseBody = call_user_func($endpoint->parse, $responseBody);

        // On success, we decode the response
        return [
            "id"     => $responseBody,
            "status" => (int)$output[1],
        ];
    }

}
