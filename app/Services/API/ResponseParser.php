<?php

namespace Neo\Services\API;

abstract class ResponseParser {

    public function __invoke(array $responseBody) {
        return (new static())->handle($responseBody);
    }

    abstract public function handle(array $responseBody);
}
