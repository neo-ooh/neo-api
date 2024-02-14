<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - CouldNotFetchThirdPartyDataException.php
 */

namespace Neo\Modules\Dynamics\Exceptions;

use Exception;
use Psr\Http\Message\ResponseInterface;

class CouldNotFetchThirdPartyDataException extends Exception {
	public function __construct(ResponseInterface $response, string $context = "") {
		parent::__construct($response->getBody() . '\n\n' . $context, $response->getStatusCode());
	}
}
