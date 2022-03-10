<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ResponseParser.php
 */

namespace Neo\Services\API\Parsers;

abstract class ResponseParser {

    public function __invoke(array $responseBody, ...$args) {
        return $this->handle($responseBody, ...$args);
    }

    abstract public function handle(array $responseBody, ...$args);
}
