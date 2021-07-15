<?php

namespace Neo\Services\Traffic;

use Exception;
use Facade\FlareClient\Http\Exceptions\BadResponse;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Support\Facades\Log;
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
     */
    public function call($endpoint, $payload, array $headers = []) {
        $payload["key"] = $this->apiKey;

        $response = $this->client->call($endpoint, $payload, ["Accept" => "application/json"]);

        if(!$response->successful()) {
            $jsonPayload = json_encode($payload, JSON_THROW_ON_ERROR);
            Log::channel("broadsign")->debug("pisignage request:$endpoint->method [{$endpoint->getPath()}] $jsonPayload", );
            Log::channel("broadsign")
               ->error("pisignage response:{$response->status()} [{$endpoint->getPath()}] {$response->body()}");

            throw new BadResponse($response->body(), $response->status());
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
