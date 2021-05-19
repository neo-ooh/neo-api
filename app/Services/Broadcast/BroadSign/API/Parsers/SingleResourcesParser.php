<?php

namespace Neo\Services\Broadcast\BroadSign\API\Parsers;

use Neo\Services\API\ResponseParser;

class SingleResourcesParser extends ResponseParser {
    protected string $type;

    public function __construct(string $type) {
        $this->type = $type;
    }

    public function handle(array $responseBody, ...$args) {
        return new $this->type($args[0], $responseBody[0]);
    }
}
