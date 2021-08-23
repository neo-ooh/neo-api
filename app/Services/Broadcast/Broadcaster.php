<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - Broadcaster.php
 */

namespace Neo\Services\Broadcast;

/**
 * Broadcaster types.
 * Each broadcaster has specificities and support different features
 */
class Broadcaster  {
    public const BROADSIGN = "broadsign";
    public const PISIGNAGE = "pisignage";
    public const ODOO = "odoo";
}
