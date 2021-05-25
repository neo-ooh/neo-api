<?php

namespace Neo\Services\Broadcast\BroadSign\API\Parsers;

class SingleResourcesParser extends \Neo\Services\API\Parsers\SingleResourcesParser {
    public function handle(array $responseBody, ...$args) {
        return new $this->type($args[0], $responseBody[0]);
    }
}
