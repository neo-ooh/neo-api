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
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Neo\Services\API\APIAuthenticationError;
use Neo\Services\API\APIClient;
use Neo\Services\API\Endpoint;

class PlaceExchangeClient extends APIClient {
    protected string|null $accessToken = null;

    public function __construct(protected PlaceExchangeConfig $config) {
        parent::__construct();
    }

    public function isLoggedIn() {
        return $this->accessToken !== null;
    }

    /**
     * @throws GuzzleException
     * @throws APIAuthenticationError
     */
    protected function login(): bool {
        $authEndpoint       = Endpoint::post("/token");
        $authEndpoint->base = $this->config->api_url;

        $response = parent::call($authEndpoint, [
            "username" => $this->config->api_username,
            "password" => $this->config->api_key,
        ]);


        if ($response->failed()) {
            throw new APIAuthenticationError();
        }

        $this->accessToken = $response->json()["access_token"];

        return true;
    }

    /**
     * @throws RequestException
     * @throws GuzzleException
     * @throws APIAuthenticationError
     */
    public function call(Endpoint $endpoint, mixed $payload, array $headers = []): Response {
        if (strlen($endpoint->base) === 0) {
            $endpoint->base = $this->config->api_url . "/orgs/{org_id}";
            $endpoint->setParam("org_id", $this->config->org_id);
        }

        if (!$this->isLoggedIn()) {
            $this->login();
        }

        $response = parent::call($endpoint, $payload, [
            "Authorization" => "Bearer " . $this->accessToken,
            "Accept"        => "application/json",
            ...$headers,
        ]);

        if (!$response->successful()) {
            $response->throw();
        }

        return $response;
    }
}
