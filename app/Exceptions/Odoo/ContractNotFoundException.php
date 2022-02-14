<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ContractNotFoundException.php
 */

namespace Neo\Exceptions\Odoo;

use Symfony\Component\HttpKernel\Exception\HttpException;

class ContractNotFoundException extends HttpException {
    public function __construct($contractName = null, \Throwable $previous = null, array $headers = [], $code = 0) {
        parent::__construct(404, "Not contract named $contractName could be found", $previous, $headers, $code);
    }
}
