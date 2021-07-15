<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - SumResponseParser.php
 */

namespace Neo\Services\Traffic;

use Neo\Services\API\Parsers\ResponseParser;

class SumResponseParser extends ResponseParser {
    public function handle(array $responseBody, ...$args) {
        return $responseBody["sum"];
    }
}
