<?php

namespace Neo\Services\API;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

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
        $request = Http::withoutVerifying()
                       ->withOptions($endpoint->options)
                       ->withHeaders($headers);

        if ($endpoint->format === "multipart") {
//            $request->asMultipart();
            $request->withOptions($request->mergeOptions($payload));
            $payload = [];
//            $boundary = "__X__CONNECT_REQUEST__";
//            $request->contentType("multipart/mixed; boundary=$boundary");
//            $payload = new MultipartStream($payload, $boundary);
        }

        return $request->{strtolower($endpoint->method)}($endpoint->getUrl(), $payload);
    }
}
