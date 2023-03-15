<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - HivestackClient.php
 */

namespace Neo\Modules\Properties\Services\Hivestack\API;

use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Neo\Modules\Properties\Services\Hivestack\HivestackConfig;
use Neo\Services\API\APIClient;
use Neo\Services\API\Endpoint;

class HivestackClient extends APIClient {
    public function __construct(protected HivestackConfig $config) {
    }

    protected function getAuthHeader(): array {
        return [
            "hs-auth" => "apikey " . $this->config->api_key,
        ];
    }

    /**
     * @throws RequestException
     * @throws GuzzleException
     */
    public function call(Endpoint $endpoint, mixed $payload, array $headers = []): Response {
        $endpoint->base = $this->config->api_url;

        $response = parent::call($endpoint, $payload, [
            ...$this->getAuthHeader(),
            "Accept" => "application/json",
        ]);

        if (!$response->successful()) {
            $response->throw();
        }

        return $response;
    }
}
