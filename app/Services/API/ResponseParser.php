<?php

namespace Neo\Services\API;

abstract class ResponseParser {

    public function __invoke(array $responseBody, ...$args) {
        return $this->handle($responseBody, ...$args);
    }

    abstract public function handle(array $responseBody, ...$args);
}
