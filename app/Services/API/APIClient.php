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

use Clockwork\Clockwork;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Profiling\Clockwork\Profiler;
use GuzzleHttp\Profiling\Middleware;
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
    public function call(Endpoint $endpoint, $payload, array $headers = []): Response {
//        $stack = new HandlerStack();
//        $stack->setHandler(new CurlHandler());

        $handlerStack = HandlerStack::create();
        $handlerStack->unshift(new Middleware(new Profiler(resolve(Clockwork::class)->timeline())));

        $clientOptions = array_merge([
                                         "handler" => $handlerStack,
                                         //                                         "debug"   => true,
                                     ], $endpoint->options);

        $client  = new Client($clientOptions);
        $request = new Request($endpoint->method, $endpoint->getUrl(), $headers);

        // Create a middleware that echoes parts of the request.
//        $tapMiddleware = Middleware::tap(function ($request) {
//            (new ConsoleOutput())->write($request->getBody());
//        });

        $options = [/*'handler' => $tapMiddleware($stack), 'debug' => true*/];

        if ($endpoint->format === 'multipart') {
            $options[RequestOptions::MULTIPART] = $payload;
        } else if ($request->getMethod() === "GET") {
            $options[RequestOptions::QUERY] = $payload;
        } else {
            $options[RequestOptions::JSON] = $payload;
        }

        return new Response($client->send($request, $options));
    }
}
