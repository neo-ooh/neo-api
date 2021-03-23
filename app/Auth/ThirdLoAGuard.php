<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ThirdLoAGuard.php
 */

namespace Neo\Auth;

/**
 * Class ThirdLoAGuard
 *
 * ThirdLoAGuard `Third Level of authentication guard` ensure that the current user is NOT locked, has its second
 * factor authentication validated. It does NOT ensure that the current user has approved the terms of service
 *
 * @package Neo\Auth
 */
class ThirdLoAGuard extends JwtGuard {
    protected bool $allowDisabledAccount = false;
    protected bool $allowNonValidated2FA = false;
    protected bool $allowNonApprovedTos = true;
}
