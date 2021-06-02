<?php

namespace Neo\Services\Broadcast\BroadSign\API\Parsers;

use Neo\Services\API\Parsers\ResponseParser;

class ResourceIDParser extends ResponseParser {
    public function handle(array $responseBody, ...$args) {
        return $responseBody[0]["id"];
    }
}
