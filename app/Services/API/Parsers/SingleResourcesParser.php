<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - SingleResourcesParser.php
 */

namespace Neo\Services\API\Parsers;

class SingleResourcesParser extends ResponseParser {
    protected string $type;

    public function __construct(string $type) {
        $this->type = $type;
    }

    public function handle(array $responseBody, ...$args) {
        return new $this->type($args[0], $responseBody);
    }
}
