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

class ContractNotFoundException extends HttpException {
    public function __construct($contractName = null) {
        parent::__construct(400, "No contract named $contractName could be found.");
    }
}
