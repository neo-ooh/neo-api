<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ReachClient.php
 */

namespace Neo\Modules\Properties\Services\Reach;

use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Neo\Modules\Properties\Services\Exceptions\RequestException;
use Neo\Services\API\APIClient;
use Neo\Services\API\Endpoint;

class ReachClient extends APIClient {
    public function __construct(protected ReachConfig $config) {
        parent::__construct();
    }

    /**
     * @throws GuzzleException
     * @throws RequestException
     */
    protected function getAuthToken() {
        return Cache::tags(["reach-data", "inventory-{$this->config->inventoryID}"])
                    ->remember(/**
                     * @throws GuzzleException
                     * @throws RequestException
                     */ "reach-{$this->config->inventoryID}-token", 280, function () {
                        $authEndpoint       = new Endpoint("POST", "/oauth/token");
                        $authEndpoint->base = $this->config->auth_url;

                        $requestHeader = [
                            "Accept"       => "application/json",
                            "Content-type" => "application/json",
                        ];

                        $requestPayload = [
                            'client_id'  => $this->config->client_id,
                            'username'   => $this->config->api_username,
                            'password'   => $this->config->api_key,
                            'scope'      => 'offline_access',
                            'realm'      => 'Username-Password-Authentication',
                            'audience'   => 'https://platform.broadsign.com/',
                            'grant_type' => 'http://auth0.com/oauth/grant-type/password-realm',
                        ];

                        $response = parent::call($authEndpoint, $requestPayload, $requestHeader);

                        if ($response->failed()) {
                            throw new RequestException($authEndpoint->toRequest($requestPayload, $requestHeader), $response);
                        }

                        return $response->json("access_token");
                    });
    }

    /**
     * @return array
     * @throws GuzzleException
     * @throws RequestException
     */
    protected function getAuthHeader(): array {
        return [
            "Authorization" => "Bearer " . $this->getAuthToken(),
        ];
    }

    /**
     * @throws RequestException
     * @throws GuzzleException
     */
    public function call(Endpoint $endpoint, mixed $payload, array $headers = []): Response {
        if (strlen($endpoint->base) === 0) {
            $endpoint->base = $this->config->api_url;
        }

        $requestHeader = [
            ...$this->getAuthHeader(),
            "Accept" => "application/json",
            ...$headers,
        ];

        $response = parent::call($endpoint, $payload, $requestHeader);

        if (!$response->successful()) {
            throw new RequestException($endpoint->toRequest($payload, $headers), $response);
        }

        return $response;
    }
}
