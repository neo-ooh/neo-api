<?php

namespace Neo\Services\Broadcast\BroadSign\API\Parsers;

use Neo\Services\API\ResponseParser;

class ResourceIDParser extends ResponseParser {
    public function handle(array $responseBody, ...$args): array {
        return $responseBody[0]["id"];
    }
}
