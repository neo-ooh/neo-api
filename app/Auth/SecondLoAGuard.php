<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <Valentin Dufois>
 *
 * @neo/api - SecondLoAGuard.php
 */

namespace Neo\Auth;

/**
 * Class SecondLoAGuard
 *
 * SecondLoAGuard `Second Level of Authentication guard` ensure that the current user's account is unlocked, It does
 * NOT ensure that the current user has approved the terms of service or that its second level auth has been validated
 *
 * @package Neo\Auth
 */
class SecondLoAGuard extends JwtGuard {
    protected bool $allowDisabledAccount = false;
    protected bool $allowNonValidated2FA = true;
    protected bool $allowNonApprovedTos = true;
}
