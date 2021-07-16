<?php

namespace Neo\Services\Traffic;

use Facade\FlareClient\Http\Exceptions\BadResponse;
use Illuminate\Support\Facades\Log;
use Neo\Services\API\APIClient;
use Neo\Services\API\APIClientInterface;
use Neo\Services\API\Endpoint;

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

        // The linkett API tends to return 503 errors oonce n a while (more often than not). So we allow for a few tries before actually failing
        $tries = 0;

        do {
            $response = $this->client->call($endpoint, $payload, [
                "Accept"      => "application/json",
                "Connection"  => "close",
            ]);

            $tries++;
        } while (!$response->successful() && $tries < 5);

        if (!$response->successful()) {
            $jsonPayload = json_encode($payload, JSON_THROW_ON_ERROR);
            Log::channel("broadsign")->debug("pisignage request:$endpoint->method [{$endpoint->getPath()}] $jsonPayload",);
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
