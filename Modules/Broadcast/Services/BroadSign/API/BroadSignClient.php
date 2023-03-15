<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - BroadSignClient.php
 */

namespace Neo\Modules\Broadcast\Services\BroadSign\API;

use Cache;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;
use JsonException;
use Neo\Exceptions\ThirdPartyAPIException;
use Neo\Modules\Broadcast\Services\BroadSign\BroadSignConfig;
use Neo\Services\API\APIClient;
use Neo\Services\API\APIClientInterface;

class BroadSignClient implements APIClientInterface {

    protected APIClient $client;
    protected BroadSignConfig $config;

    public function __construct(BroadSignConfig $config) {
        $this->client = new APIClient();
        $this->config = $config;
    }

    public function getConfig(): BroadSignConfig {
        return $this->config;
    }

    /**
     * @param BroadSignEndpoint     $endpoint
     * @param int|string|array|null $payload
     * @param array                 $headers
     * @return false|mixed|string|null
     * @throws GuzzleException
     * @throws JsonException|ThirdPartyAPIException
     */
    public function call($endpoint, mixed $payload, array $headers = []): mixed {
        // Set the base path for the request to the API.
        $endpoint->base = $this->config->apiURL;

        // Add the connection certificate to the endpoint
        $endpoint->options["cert"] = $this->config->getCertPath();

        // Add the domain Id if requested
        if ($endpoint->includeDomainID && is_array($payload)) {
            $payload["domain_id"] = $this->config->domainId;
        }

        // Use the payload to fill in the uri paramters
        $endpoint->setParams($payload);

        // Is the payload is an int or a string, we can assume it was an ID value for the URI, so we set it to null
        // to prevent sending a useless body with ou request
        if (is_int($payload) || is_string($payload)) {
            $payload = null;
        }

        // Execute the request
        try {
            if ($endpoint->cache === 0 || strtolower($endpoint->method) !== 'get') {
                // Bypass the cache if we are not making a `get` request
                return $this->call_impl__($endpoint, $payload, $headers);
            }

            // Cache and return the response
            return Cache::remember((string)$endpoint, $endpoint->cache, fn() => $this->call_impl__($endpoint, $payload, $headers));
        } catch (ThirdPartyAPIException $exception) {
            Log::error($exception->getMessage(), [
                $endpoint,
                $payload,
            ]);

            throw $exception;
        }
    }

    /**
     * @param BroadSignEndpoint $endpoint
     * @param                   $payload
     * @param array             $headers
     * @return false|mixed|string|null
     * @throws ThirdPartyAPIException
     * @throws JsonException
     * @throws GuzzleException
     */
    protected function call_impl__(BroadSignEndpoint $endpoint, $payload, array $headers): mixed {
        if (config('app.env') !== 'production') {
            Log::debug("[BroadSign] $endpoint->method@{$endpoint->getPath()}", [json_encode($payload, JSON_THROW_ON_ERROR)]);
        }

        $event = uniqid("$endpoint->method@$endpoint->path", true);
        clock()->event("[BroadSign] $endpoint->method@$endpoint->path")->name($event)->color("purple")->begin();

        // Execute the request
        $response = $this->client->call($endpoint, $payload, $headers);

        clock()->event($event)->end();

        // In case the resource wasn't found (404), return null
        if ($response->status() === 404) {
            return null;
        }

        if (!$response->successful()) {
            // Request was not successful, log the exchange
            $jsonPayload = json_encode($payload, JSON_THROW_ON_ERROR);
            Log::channel("broadsign")->debug("request:$endpoint->method [{$endpoint->getPath()}] $jsonPayload");
            Log::channel("broadsign")
               ->error("response:{$response->status()} [{$endpoint->getPath()}] {$response->body()}");

            throw new ThirdPartyAPIException($jsonPayload, $response->status(), $response->body());
        }

        // Unwrap response content if needed
        if (isset($endpoint->unwrapKey)) {
            $responseBody = $response->json();
            $responseBody = $responseBody[$endpoint->unwrapKey];
        } else {
            $responseBody = $response->body();
        }

        // Execute post-request transformation if needed
        if ($endpoint->parse) {
            // Execute the parse on the response
            $responseBody = call_user_func($endpoint->parse, $responseBody, $this);
        }

        return $responseBody;
    }
}
