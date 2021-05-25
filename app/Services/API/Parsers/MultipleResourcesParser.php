<?php

namespace Neo\Services\API\Parsers;

use Illuminate\Support\Collection;
use Neo\Services\API\Parsers\ResponseParser;

class MultipleResourcesParser extends ResponseParser {
    protected string $type;

    public function __construct(string $type) {
        $this->type = $type;
    }

    public function handle(array $responseBody, ...$args): Collection {
        return collect($responseBody)->map(fn($data) => new $this->type($args[0], $data));
    }
}
