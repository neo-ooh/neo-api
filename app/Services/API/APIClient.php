<?php

namespace Neo\Services\API;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\MultipartStream;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Request;

class APIClient implements APIClientInterface {
    /**
     * Execute a call to the given endpoint using with given body and headers
     *
     * @param Endpoint $endpoint
     * @param mixed    $payload
     * @param array    $headers
     * @return Response
     */
    public function call($endpoint, $payload, array $headers = []) {
        $client = new Client($endpoint->options);
        $request = new \GuzzleHttp\Psr7\Request($endpoint->method, $endpoint->getUrl(), $headers);

        $options = [];

        if($endpoint->format === 'multipart') {
            $options["multipart"] = $payload;
        } else {
            $options["json"] = $payload;
        }

        return new Response($client->send($request, $options));
    }
}
