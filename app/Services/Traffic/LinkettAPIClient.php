<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - LinkettAPIClient.php
 */

namespace Neo\Services\Traffic;

use Illuminate\Support\Facades\Log;
use Neo\Exceptions\ThirdPartyAPIException;
use Neo\Services\API\APIClient;
use Neo\Services\API\APIClientInterface;

class LinkettAPIClient implements APIClientInterface {

    protected APIClient $client;
    protected string $apiKey;

    public function __construct(string $apiKey) {
        $this->apiKey = $apiKey;
        $this->client = new APIClient();
    }

    /**
     * @inheritDoc
     * @param \Neo\Services\API\Endpoint $endpoint
     * @param int|string|array|null      $payload
     * @param array                      $headers
     * @return array|mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \JsonException
     * @throws \Neo\Exceptions\ThirdPartyAPIException
     */
    public function call($endpoint, $payload, array $headers = []) {
        $payload["key"] = $this->apiKey;

        // The Linkett API tends to return 503 errors once in a while (more often than not). So we allow for a few tries before actually failing
        $tries = 0;

        do {
            $response = $this->client->call($endpoint, $payload, [
                "Accept"     => "application/json",
                "Connection" => "close",
            ]);

            $tries++;
        } while (!$response->successful() && $tries < 5);

        if (!$response->successful()) {
            $jsonPayload = json_encode($payload, JSON_THROW_ON_ERROR);
            Log::channel("broadsign")->debug("linkett request:$endpoint->method [{$endpoint->getPath()}] $jsonPayload");
            Log::channel("broadsign")
               ->error("linkett response:{$response->status()} [{$endpoint->getPath()}] {$response->body()}");

            throw new ThirdPartyAPIException($response->body(), $response->status());
        }

        $responseBody = $response->json();

        // Execute post-request transformation if needed
        if ($endpoint->parse) {
            // Execute the parse on the response
            $responseBody = call_user_func($endpoint->parse, $responseBody, $this);
        }

        return $responseBody;
    }
}
