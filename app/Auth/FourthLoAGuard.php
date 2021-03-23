<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - FourthLoAGuard.php
 */

namespace Neo\Auth;

/**
 * Class FourthLoAGuard
 *
 * FourthLoAGuard `Fourth Level of authentication guard` ensure that the current user has its second factor
 * authentication validated, its terms of service approved, and that its account is not locked.
 *
 * @package Neo\Auth
 */
class FourthLoAGuard extends JwtGuard {
    protected bool $allowDisabledAccount = false;
    protected bool $allowNonValidated2FA = false;
    protected bool $allowNonApprovedTos = false;
}
