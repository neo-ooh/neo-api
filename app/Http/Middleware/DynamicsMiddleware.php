<?php

namespace Neo\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class DynamicsMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        /** @var Response $response */
        $response     = $next($request);
        $responseJSON = json_decode($response->content(), true) ?: [];

        if(array_key_exists('content', $responseJSON))
            $responseJSON = $responseJSON['content'];

        $factor = random_int(90, 110) / 100.0;

        $formatted = [
            "timestamp" => time(),
            "refresh" => config('services.meteo-media.record-lifespan', 0) * $factor,
            "content" => $responseJSON,
            "status" => $response->getStatusCode()
        ];

        $response->setContent(json_encode($formatted));
        $response->setMaxAge(config('services.meteo-media.record-lifespan', 0) * $factor);

        return $response;
    }
}
