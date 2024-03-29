<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - VistarClient.php
 */

namespace Neo\Modules\Properties\Services\Vistar;

use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\Client\Response;
use Neo\Modules\Properties\Services\Exceptions\RequestException;
use Neo\Services\API\APIAuthenticationError;
use Neo\Services\API\APIClient;
use Neo\Services\API\Endpoint;

class VistarClient extends APIClient {
    protected string|null $authCookie = null;

    public function __construct(protected VistarConfig $config) {
        parent::__construct();
    }

    public function isLoggedIn() {
        return $this->authCookie !== null;
    }

    /**
     * @throws GuzzleException
     * @throws APIAuthenticationError
     */
    public function login(): bool {
        $authEndpoint       = Endpoint::post("/session");
        $authEndpoint->base = $this->config->api_url;

        $response = parent::call($authEndpoint, [
            "username" => $this->config->api_username,
            "password" => $this->config->api_key,
        ],                       [
                                     "Content-Type" => "application/json",
                                     "Accept"       => "application/json",
                                 ]);


        if ($response->failed()) {
            throw new APIAuthenticationError();
        }

        $this->authCookie = $response->headers()["Set-Cookie"][0];

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
            $endpoint->base = $this->config->api_url;
        }

        if (!$this->isLoggedIn()) {
            $this->login();
        }

        $requestHeaders = [
            "Accept" => "application/json",
            "Cookie" => $this->authCookie,
            ...$headers,
        ];

        $response = parent::call($endpoint, $payload, $requestHeaders);

        if (!$response->successful()) {
            throw new RequestException($endpoint->toRequest($payload, $requestHeaders), $response);
        }

        return $response;
    }
}
