<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - FirstLoAGuard.php
 */

namespace Neo\Auth;

/**
 * Class FirstLoAGuard
 *
 * FirstLoAGuard `First Level of Authentication guard` only ensure that the user has a valid token, but does NOT
 * validate its two factor authentication, acceptance of terms of service, or that the account is unlocked.
 *
 * @package Neo\Auth
 */
class FirstLoAGuard extends JwtGuard {
    protected bool $allowDisabledAccount = true;
    protected bool $allowNonValidated2FA = true;
    protected bool $allowNonApprovedTos = true;
}
