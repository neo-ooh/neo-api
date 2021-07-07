<?php

namespace Neo\Http\Middleware;

use Closure;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class SimpleErrors {
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next) {
        /** @var Response $response */
        $response = $next($request);

        if($exception = $response->exception) {
            return new Response([
                "code"    => $response->getStatusCode() ,
                "message" => $exception->getMessage(),
            ], 500);
        }

        return $response;
    }
}
