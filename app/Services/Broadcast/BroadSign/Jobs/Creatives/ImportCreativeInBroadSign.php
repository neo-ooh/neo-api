<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ImportCreativeInBroadSign.php
 */

namespace Neo\Services\Broadcast\BroadSign\Jobs\Creatives;

use DateInterval;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Neo\Models\Creative;
use Neo\Services\Broadcast\BroadSign\API\BroadSignEndpoint as Endpoint;
use Neo\Services\Broadcast\BroadSign\API\Parsers\ResourceIDParser;
use Neo\Services\Broadcast\BroadSign\BroadSignConfig;
use Neo\Services\Broadcast\BroadSign\Jobs\BroadSignJob;

/**
 * Class ImportCreative
 *
 * @package Neo\Jobs
 *
 * Imports the specified creative in BroadSign and register its BroadSign ID.
 */
class ImportCreativeInBroadSign extends BroadSignJob implements ShouldBeUnique {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected int $creativeID;
    protected string $creativeName;

    public function uniqueId(): int {
        return $this->creativeID;
    }

    /**
     * Create a new job instance.
     *
     * @param int $creativeID ID of the creative to import
     */
    public function __construct(BroadSignConfig $config, int $creativeID) {
        parent::__construct($config);

        $this->creativeID = $creativeID;
    }

    /**
     * Execute the job.
     *
     * @return void
     * @throws Exception
     */
    public function handle(): void {
        /** @var Creative $creative */
        $creative = Creative::query()->findOrFail($this->creativeID);

        if ($creative->getExternalId($this->config->networkID) !== null) {
            // This creative already has an id, do nothing.
            return;
        }

        // Depending on the creative type, we perform different operations
        switch ($creative->type) {
            case Creative::TYPE_STATIC:
                $this->importStaticCreative($creative);
                break;
            case Creative::TYPE_DYNAMIC:
                $this->importDynamicCreative($creative);
                break;
        }

        // Schedule job to target the creative accordingly
        TargetCreative::dispatch($this->config, $creative->id);
    }

    protected function getAttributesForCreative(Creative $creative) {
        $attributes           = [];
        $attributes["height"] = $creative->frame->height;
        $attributes["width"]  = $creative->frame->width;

        if ($creative->type === Creative::TYPE_DYNAMIC) {
            $attributes["expire_on_empty_remote_dir"] = "false";                                // Don;t expire if connection is lost
            $attributes["io_strategy"]                = "esf";                                  // ???
            $attributes["source"]                     = $creative->properties->url;             // URL to the resource
            $attributes["source_append_id"]           = "false";                                // Append player ID to url (no)
            $attributes["source_expiry"]              = "0";                                    // Not sure
            $attributes["source_refresh"]             = $creative->properties->refresh_interval;// URL refresh interval (minutes)
        } else {
            switch ($creative->properties->extension) {
                case "mp4":
                    $attributes["height"]   = $creative->frame->height;
                    $attributes["width"]    = $creative->frame->width;
                    $attributes["duration"] = (new DateInterval("PT" . $creative->content->duration . "S"))->format("%H:%I:%S");
            }
        }

        return implode('\n', array_map(static fn(string $k, string $v) => "$k=$v", array_keys($attributes), array_values($attributes)));
    }

    /**
     * @throws Exception
     */
    protected function importStaticCreative(Creative $creative): void {
        // Prepare the creative metadata for BroadSign
        $metadata = [
            "name"             => $creative->owner->email . " - " . $creative->original_name,
            "originalfilename" => $creative->original_name,
            "size"             => Storage::size($creative->properties->file_path),
            "feeds"            => "",
            "attributes"       => $this->getAttributesForCreative($creative),
            "mime"             => $creative->properties->extension,
        ];


        // Get the creative in a temporary file for the upload
        $tempFile = tmpfile();
        fwrite($tempFile, file_get_contents($creative->properties->file_url));
        fseek($tempFile, 0);
        $creativePath = stream_get_meta_data($tempFile)['uri'];

        $response = $this->executeRequest($metadata, $creativePath);

        fclose($tempFile);

        $creative->external_ids()->create([
            "network_id"  => $this->config->networkID,
            "external_id" => $response["id"],
        ]);
        $creative->save();
    }

    protected function importDynamicCreative(Creative $creative): void {
        // Prepare the creative metadata for BroadSign
        $metadata = [
            "name"             => $creative->owner->email . " - " . $creative->original_name,
            "originalfilename" => $creative->original_name,
            "size"             => "-1",
            "feeds"            => "",
            "attributes"       => $this->getAttributesForCreative($creative),
            "mime"             => "",
        ];

        $response = $this->executeRequest($metadata);

        $creative->external_ids()->create([
            "network_id"  => $this->config->networkID,
            "external_id" => $response["id"],
        ]);
        $creative->save();
    }

    protected function executeRequest(array $payload, $file = null) {
        // Complete the payload
        $payload["domain_id"]    = $this->config->domainId;
        $payload["container_id"] = $this->config->adCopiesContainerId;
        $payload["parent_id"]    = $this->config->customerId;

        $metadata = json_encode($payload, JSON_THROW_ON_ERROR);

        // Get the endpoint
        $endpoint       = Endpoint::post("/content/v11/add")
                                  ->unwrap("content")
                                  ->parser(new ResourceIDParser());
        $endpoint->base = $this->config->apiURL;

        // Prepare the request command
        $req          = [];
        $req[]        = "curl -s";                                           // curl with silent output
        $req[]        = "-w '\n%{http_code}'";                               // display http status code on 2nd line
        $req[]        = "-POST " . $endpoint->getUrl();                      // POST method + URL
        $req[]        = "-E" . $this->config->getCertPath();                 // BroadSign cert auth
        $req[]        = "-H 'Content-Type: multipart/mixed'";                // Request Content Type
        $req[]        = "-F 'metadata=$metadata;type=application/json'";     // Request metadata
        $req[]        = $file ? "-F 'file=@$file'" : "-F 'file=dummy.txt'";  // Request file
        $curl_command = implode(" ", $req);

        // Execute the request
        $output    = [];
        $exit_code = 0;

        exec($curl_command, $output, $exit_code);

        if ($exit_code !== 0 || (int)$output[1] !== 200) {
            throw new \Error("Error while executing cURL request: " . implode(", ", $output));
        }

        $responseBody = json_decode($output[0], true, 512, JSON_THROW_ON_ERROR)[$endpoint->unwrapKey];
        $responseBody = call_user_func($endpoint->parse, $responseBody, $this);

        // On success, we decode the response
        return [
            "id"     => $responseBody,
            "status" => (int)$output[1],
        ];
    }
}

