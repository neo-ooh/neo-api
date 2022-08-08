<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - APIClientInterface.php
 */

namespace Neo\Services\API;

use GuzzleHttp\Exception\ClientException;

interface APIClientInterface {
    /**
     * @param                       $endpoint
     * @param int|string|array|null $payload
     * @param array                 $headers
     * @return mixed
     * @throws ClientException
     */
    public function call($endpoint, mixed $payload, array $headers = []);
}
