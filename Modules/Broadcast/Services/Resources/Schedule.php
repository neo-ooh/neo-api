<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - Schedule.php
 */

namespace Neo\Modules\Broadcast\Services\Resources;

class Schedule extends ExternalBroadcastResource {
    public string $name;

    public string $start_date;
    public string $start_time;

    public string $end_date;
    public string $end_time;

    public int $broadcast_days;

    public int $order;

    public bool $active;
}