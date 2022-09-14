<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - BroadcastResourceType.php
 */


namespace Neo\Modules\Broadcast\Enums;

enum BroadcastResourceType: string {
    case Creative = 'creative';
    case Content = 'content';
    case Schedule = 'schedule';
    case Campaign = 'campaign';
}
