<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - SingleResourcesParser.php
 */

namespace Neo\Services\Broadcast\BroadSign\API\Parsers;

class SingleResourcesParser extends \Neo\Services\API\Parsers\SingleResourcesParser {
    public function handle(array $responseBody, ...$args) {
        return new $this->type($args[0], $responseBody[0]);
    }
}
