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
     * @param       $endpoint
     * @param       $payload
     * @param array $headers
     * @throws ClientException
     * @return mixed
     */
    public function call($endpoint, $payload, array $headers = []);
}
