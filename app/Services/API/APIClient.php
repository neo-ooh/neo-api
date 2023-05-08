<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - APIClient.php
 */

namespace Neo\Services\API;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Handler\CurlHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\RequestOptions;
use Illuminate\Http\Client\Response;

class APIClient implements APIClientInterface {
    protected Client|null $_client = null;

    public function __construct() {
        $this->connect();
    }

    protected function connect() {
        if ($this->_client !== null) {
            return;
        }

        $stack = HandlerStack::create(new CurlHandler());

        $clientOptions = [
//            "debug"   => true,
"handler" => $stack,
        ];

        $this->_client = new Client($clientOptions);
    }

    /**
     * Execute a call to the given endpoint using with given body and headers
     *
     * @param Endpoint $endpoint
     * @param mixed    $payload
     * @param array    $headers
     * @return Response
     * @throws GuzzleException
     */
    public function call(Endpoint $endpoint, mixed $payload, array $headers = []): Response {
        // Make sure we are connected
        $this->connect();

//        dump($endpoint->options);
//        dump($endpoint->getUrl());
//        dump($endpoint->method);
//        dump($headers);
//        dump($payload);

        $request     = new Request($endpoint->method, $endpoint->getUrl(), $headers);
        $contentType = $headers["Content-Type"] ?? "application/json";
        $options     = [...$endpoint->options];

        if ($endpoint->format === 'multipart') {
            $options[RequestOptions::MULTIPART] = $payload;
        } else if ($request->getMethod() === "GET") {
            $options[RequestOptions::QUERY] = $payload;
        } else if ($endpoint->format === 'json' || $contentType === "application/json") {
            $options[RequestOptions::JSON] = $payload;
        } else {
            $options[RequestOptions::BODY] = $payload;
        }

        return new Response($this->_client->send($request, $options));
    }

    public function __serialize(): array {
        return collect(get_object_vars($this))->filter(fn($v, string $key) => $key !== "_client")->all();
    }
}
