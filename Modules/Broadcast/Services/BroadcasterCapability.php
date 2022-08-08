<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - BroadcasterCapability.php
 */

namespace Neo\Modules\Broadcast\Services;

enum BroadcasterCapability {
    /**
     * Support for listing locations, players and display types from the broadcaster
     */
    case Locations;

    /**
     * Support for turning screens on/off during the night. Same hours for all days of the week
     */
    case LocationsSleepSimple;

    /**
     * Support for scheduling content on its platform.
     * eg. BroadSign and PiSignage supports it, SignageOS doesn't and relies on Connect for the scheduling and playlist generation
     */
    case Scheduling;

    /**
     * Support for querying campaigns performances (repetitions/impressions)
     */
    case Reporting;

    /**
     * Support for getting a screenshot on the fly from players
     */
    case InstantScreenshot;

    /**
     * Support for triggering a salve of screenshots over a period
     */
    case ScreenshotsBurst;

    /**
     * Support for directories holding resources
     */
    case Containers;
}
