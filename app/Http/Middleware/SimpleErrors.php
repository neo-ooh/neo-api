<?php

namespace Neo\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;

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

        if (($exception = $response->exception) && (config('app.env') === 'production')) {
            if ($exception instanceof ValidationException) {
                return $response;
            }

            return new Response([
                "code"    => $response->getStatusCode(),
                "message" => $exception->getMessage(),
            ], 500);
        }

        return $response;
    }
}
