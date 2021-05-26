<?php

namespace Neo\Services\Broadcast\PiSignage\API;

use Cache;
use Facade\FlareClient\Http\Exceptions\BadResponse;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Log;
use Neo\Services\API\APIClient;
use Neo\Services\API\APIClientInterface;
use Neo\Services\API\Endpoint;
use Neo\Services\Broadcast\PiSignage\PiSignageConfig;

class PiSignageClient implements APIClientInterface {

    protected APIClient $client;
    protected PiSignageConfig $config;

    public function __construct(PiSignageConfig $config) {
        $this->client = new APIClient();
        $this->config = $config;
    }

    public function getConfig(): PiSignageConfig {
        return $this->config;
    }

    /**
     * @param Endpoint $endpoint
     * @param mixed             $payload
     * @param array             $headers
     * @return Response
     * @throws BadResponse
     */
    public function call($endpoint, $payload, array $headers = []) {
        // Set the base path for the request to the API.
        $endpoint->base = $this->config->apiURL;

        // Add the connection auth header
        $headers["Authorization"] = "Basic " . $this->config->apiToken;

        $uriParams = $endpoint->getParamsList();
        if (count($uriParams) > 0) {
            if ($uriParams[0] === "id" && is_numeric($payload)) {
                $endpoint->setParam("id", $payload);
            } else if(is_array($payload)) {
                foreach ($uriParams as $param) {
                    $endpoint->setParam($param, $payload[$param]);
                }
            }
        }

        if(is_numeric($payload)) {
            $payload = null;
        }

        if ($endpoint->cache === 0 || strtolower($endpoint->method) !== 'get') {
            // Bypass the cache if we are not making a `get` request
            return $this->call_impl__($endpoint, $payload, $headers);
        }

        // Cache and return the response
        return Cache::remember((string)$endpoint, $endpoint->cache, fn() => $this->call_impl__($endpoint, $payload, $headers));
    }

    /**
     * @throws BadResponse
     * @throws \JsonException
     */
    protected function call_impl__(Endpoint $endpoint, $payload, array $headers) {
        // Execute the request
        $response = $this->client->call($endpoint, $payload, $headers);

        // In case the resource wasn't found (404), return null
        if ($response->status() === 404) {
            return null;
        }

        $responseBody = $response->json();

        if (!$response->successful()) {
            // Request was not successful, log the exchange
            $jsonPaylod = json_encode($payload, JSON_THROW_ON_ERROR);
            Log::channel("broadsign")->debug("pisignage request:$endpoint->method [{$endpoint->getPath()}] $jsonPaylod", );
            Log::channel("broadsign")
               ->error("pisignage response:{$response->status()} [{$endpoint->getPath()}] {$response->body()}");

            throw new BadResponse($response->body(), $response->status());
        }

        if(array_key_exists("data", $responseBody)) {
            // Unwrap response content
            $responseBody = $responseBody["data"];
        }

        // Execute post-request transformation if needed
        if ($endpoint->parse) {
            // Execute the parse on the response
            $responseBody = call_user_func($endpoint->parse, $responseBody, $this);
        }

        return $responseBody;
    }
}
