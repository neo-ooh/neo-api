<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - RequestException.php
 */

namespace Neo\Modules\Properties\Services\Exceptions;

use GuzzleHttp\Psr7\Message;
use Illuminate\Http\Client\HttpClientException;
use Illuminate\Http\Client\Response;

class RequestException extends HttpClientException {
    /**
     * The response instance.
     *
     * @var Response
     */
    public $response;

    /**
     * Create a new exception instance.
     *
     * @param Response $response
     * @return void
     */
    public function __construct(Response $response) {
        parent::__construct(Message::toString($response->toPsrResponse()), $response->status());

        $this->response = $response;
    }
}
