<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ResourceIDParser.php
 */

namespace Neo\Modules\Broadcast\Services\BroadSign\API\Parsers;

use Neo\Services\API\Parsers\ResponseParser;

class ResourceIDParser extends ResponseParser {
    public function handle(array $responseBody, ...$args) {
        return $responseBody[0]["id"];
    }
}
