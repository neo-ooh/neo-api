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
use GuzzleHttp\Psr7\Request;
use Illuminate\Http\Client\HttpClientException;
use Illuminate\Http\Client\Response;

class RequestException extends HttpClientException {
    /**
     * The request instance.
     *
     * @var Request
     */
    public Request $request;
    /**
     * The response instance.
     *
     * @var Response
     */
    public Response $response;

    /**
     * Create a new exception instance.
     *
     * @param Request  $request
     * @param Response $response
     */
    public function __construct(Request $request, Response $response) {
        parent::__construct("[Request]\n" . Message::toString($request) . "\n\n[Response]\n" . Message::toString($response->toPsrResponse()), $response->status());

        $this->request  = $request;
        $this->response = $response;
    }
}
