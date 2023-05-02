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
        $stack = new HandlerStack();
        $stack->setHandler(new CurlHandler());

        $contentType = $headers["Content-Type"] ?? "application/json";

//        dump($endpoint->options);
//        dump($endpoint->getUrl());
//        dump($endpoint->method);
//        dump($headers);
//        dump($payload);

        $clientOptions = array_merge([
//                                         "debug" => true,
                                     ], $endpoint->options);

        $client  = new Client($clientOptions);
        $request = new Request($endpoint->method, $endpoint->getUrl(), $headers);

        $options = [];

        if ($endpoint->format === 'multipart') {
            $options[RequestOptions::MULTIPART] = $payload;
        } else if ($request->getMethod() === "GET") {
            $options[RequestOptions::QUERY] = $payload;
        } else if ($contentType === "application/json") {
            $options[RequestOptions::JSON] = $payload;
        } else {
            $options[RequestOptions::BODY] = $payload;
        }

        return new Response($client->send($request, $options));
    }
}
