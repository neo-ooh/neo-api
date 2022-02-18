<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ContractIsNotDraftException.php
 */

namespace Neo\Exceptions\Odoo;

use Symfony\Component\HttpKernel\Exception\HttpException;

class ContractAlreadyExistException extends HttpException {
    public function __construct($contractId = null) {
        parent::__construct(400, "Contract $contractId already exist and cannot be imported again.");
    }
}
