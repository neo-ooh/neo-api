<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ContractIsDraftException.php
 */

namespace Neo\Exceptions\Odoo;

use Symfony\Component\HttpKernel\Exception\HttpException;

class ContractIsDraftException extends HttpException {
    public function __construct($contractName = null) {
        parent::__construct(400, "Cannot pull contract $contractName as it is still in proposal state.");
    }
}
