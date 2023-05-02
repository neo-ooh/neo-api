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
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Neo\Services\API\APIClient;
use Neo\Services\API\Endpoint;

class ReachClient extends APIClient {
    public function __construct(protected ReachConfig $config) {
    }

    protected function getAuthToken() {
        return Cache::tags(["reach-data", "inventory-{$this->config->inventoryID}"])
                    ->remember("reach-{$this->config->inventoryID}-token", 280, function () {
                        $authEndpoint       = new Endpoint("POST", "/oauth/token");
                        $authEndpoint->base = $this->config->auth_url;

                        $response = parent::call($authEndpoint, [
                            'client_id'  => $this->config->client_id,
                            'username'   => $this->config->api_username,
                            'password'   => $this->config->api_key,
                            'scope'      => 'offline_access',
                            'realm'      => 'Username-Password-Authentication',
                            'audience'   => 'https://platform.broadsign.com/',
                            'grant_type' => 'http://auth0.com/oauth/grant-type/password-realm',
                        ],                       [
                                                     "Accept"       => "application/json",
                                                     "Content-type" => "application/json",
                                                 ]);

                        if ($response->failed()) {
                            $response->throw();
                        }

                        return $response->json("access_token");
                    });
    }

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

        $response = parent::call($endpoint, $payload, [
            ...$this->getAuthHeader(),
            "Accept" => "application/json",
            ...$headers,
        ]);
        
        if (!$response->successful()) {
            $response->throw();
        }

        return $response;
    }
}
