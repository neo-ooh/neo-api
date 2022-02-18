<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - CannotAccessAnotherSalespersonContractException.php
 */

namespace Neo\Exceptions\Odoo;

use Symfony\Component\HttpKernel\Exception\HttpException;

class CannotAccessAnotherSalespersonContractException extends HttpException {
    public function __construct() {
        parent::__construct(400, "This contract belongs to someone else. You are not allowed to access another salesperson contract.");
    }
}
