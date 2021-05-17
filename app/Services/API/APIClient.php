<?php

namespace Neo\Services\API;

use GuzzleHttp\Psr7\MultipartStream;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class APIClient {
    /**
     * Execute a call to the given endpoint using with given body and headers
     *
     * @param Endpoint $endpoint
     * @param mixed    $payload
     * @param array    $headers
     * @return Response
     */
    public function call(Endpoint $endpoint, $payload, array $headers = []): Response {
        $request = Http::withoutVerifying()
                       ->withOptions($endpoint->options)
                       ->withHeaders($headers);

        if ($endpoint->format === "multipart") {
            $boundary = "__X__CONNECT_REQUEST__";
            $request->contentType("multipart/mixed; boundary=$boundary");
            $payload = new MultipartStream($payload, $boundary);
        }

        return $request->{strtolower($endpoint->method)}($endpoint->getUrl(), $payload);
    }
}
