<?php

namespace Neo\Services\API;

use GuzzleHttp\Exception\ClientException;

interface APIClientInterface {
    /**
     * @param       $endpoint
     * @param       $payload
     * @param array $headers
     * @throws ClientException
     * @return mixed
     */
    public function call($endpoint, $payload, array $headers = []);
}
