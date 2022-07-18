<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - StatsController.php
 */

namespace Neo\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Date;
use Neo\Modules\Broadcast\Models\Campaign;
use Neo\Modules\Broadcast\Models\Player;
use Neo\Modules\Broadcast\Models\Schedule;

class StatsController extends Controller {
    public function index() {
        $stats = [
            "campaigns" => [],
            "schedules" => [],
            "players"   => [],
        ];

        // Active Campaigns
        $stats["campaigns"]["active"] = Campaign::query()
                                                ->where("start_date", "<", Date::now())
                                                ->where("end_date", '>', Date::now())
                                                ->count();

        // Active Schedules
        $stats["schedules"]["active"] = Schedule::query()
                                                ->where("start_date", "<", Date::now())
                                                ->where("end_date", '>', Date::now())
                                                ->count();

        // Total players
        $stats["players"]["total"] = Player::query()->count();

        return new Response($stats);
    }
}
