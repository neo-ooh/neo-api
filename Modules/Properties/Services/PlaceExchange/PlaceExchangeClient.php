<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - PlaceExchangeClient.php
 */

namespace Neo\Modules\Properties\Services\PlaceExchange;

use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Neo\Modules\Properties\Services\Exceptions\RequestException;
use Neo\Modules\Properties\Services\Exceptions\RequestNotFoundException;
use Neo\Services\API\APIAuthenticationError;
use Neo\Services\API\APIClient;
use Neo\Services\API\Endpoint;

class PlaceExchangeClient extends APIClient {
    protected string|null $accessToken = null;

    public function __construct(protected PlaceExchangeConfig $config) {
        parent::__construct();
    }

    public function isLoggedIn(): bool {
        return $this->accessToken !== null;
    }

    /**
     * @throws GuzzleException
     * @throws APIAuthenticationError
     */
    public function login(): bool {
        // PX rate limit on the Auth endpoint is quite strict, so we cache our auth token to respect it.
        // Try to get an access token from cache
        $cacheKey = $this->config->inventoryUUID. "-access-token";
        $accessToken = Cache::get($cacheKey, null);

        if($accessToken !== null) {
            $this->accessToken = $accessToken;
            return true;
        }

        // Token is missing, retrieve it
        $authEndpoint       = Endpoint::post("/token");
        $authEndpoint->base = $this->config->api_url;

        $response = parent::call($authEndpoint, [
            "username" => $this->config->api_username,
            "password" => $this->config->api_key,
        ]);


        if ($response->failed()) {
            throw new APIAuthenticationError($response->body());
        }

        $responseJson = $response->json();
        $accessToken = $responseJson["access_token"];

        // Get the token lifespan, remove 2 minutes (120 seconds) from it to make sure we have enough validity left for the job
        $tokenLifespanSec = max(0, (int)$responseJson["expires_in"] - 120);

        Cache::put($cacheKey, $accessToken, $tokenLifespanSec);

        $this->accessToken = $accessToken;

        return true;
    }

    /**
     * @param Endpoint $endpoint
     * @param mixed    $payload
     * @param array    $headers
     * @return Response
     * @throws APIAuthenticationError
     * @throws GuzzleException
     * @throws RequestException
     */
    public function call(Endpoint $endpoint, mixed $payload, array $headers = []): Response {
        if (strlen($endpoint->base) === 0) {
            $endpoint->base = $this->config->api_url . "/orgs/{org_id}";
            $endpoint->setParam("org_id", $this->config->org_id);
        }

        if (!$this->isLoggedIn()) {
            $this->login();
        }

        $requestHeaders = [
            "Authorization" => "Bearer " . $this->accessToken,
            "Accept"        => "application/json",
            ...$headers,
        ];

        $response = parent::call($endpoint, $payload, $requestHeaders);

        if($response->notFound()) {
            throw new RequestNotFoundException($endpoint->toRequest($payload, $headers), $response);
        }

        if (!$response->successful()) {
            throw new RequestException($endpoint->toRequest($payload, $headers), $response);
        }

        return $response;
    }
}
