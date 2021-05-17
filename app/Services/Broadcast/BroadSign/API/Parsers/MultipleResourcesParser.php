<?php

namespace Neo\Services\Broadcast\BroadSign\API\Parsers;

use Illuminate\Support\Collection;
use Neo\Services\API\ResponseParser;

class MultipleResourcesParser extends ResponseParser {
    protected string $type;

    public function __construct(string $type) {
        $this->type = $type;
    }

    public function handle(array $responseBody): Collection {
        return collect($responseBody)->map(fn($data) => new $this->type($data));
    }
}
