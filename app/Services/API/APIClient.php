<?php

namespace Neo\Services\API;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\CurlHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\RequestOptions;
use Illuminate\Http\Client\Response;
use Symfony\Component\Console\Output\ConsoleOutput;

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
//        $stack = new HandlerStack();
//        $stack->setHandler(new CurlHandler());

        $client  = new Client($endpoint->options);
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
