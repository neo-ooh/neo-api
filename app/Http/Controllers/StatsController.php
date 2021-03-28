<?php

namespace Neo\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Date;
use Neo\Models\Campaign;
use Neo\Models\Player;
use Neo\Models\Schedule;

class StatsController extends Controller
{
    public function index() {
        $stats = [
            "campaigns" => [],
            "schedules" => [],
            "players" => [],
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
