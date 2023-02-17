<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - GroupTrafficStatsController.php
 */

namespace Neo\Modules\Properties\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Neo\Enums\ActorType;
use Neo\Http\Controllers\Controller;
use Neo\Http\Requests\ActorsTrafficStatisticsRequest;
use Neo\Models\Actor;
use Neo\Models\Utils\ActorsGetter;
use Neo\Modules\Properties\Models\MonthlyTrafficDatum;

class GroupTrafficStatsController extends Controller {
    public function show(ActorsTrafficStatisticsRequest $request, Actor $actor) {
        // We want to list all the direct children of the provided actor. For each of them , we list all their own children that are properties, and aggregate their traffic

        $directChildren = ActorsGetter::from($actor)->selectChildren(recursive: false)->getActors();

        $childrenTrafficStats = [];
        foreach ($directChildren as $child) {
            if ($child->type === ActorType::User) {
                // ignore users
                continue;
            }

            if ($child->type === ActorType::Property) {
                // If the child is a property, directly pull its traffic.
                $traffic = $child->property->traffic->data;
            } else {
                // If the child is a group, we do a selection of itself, all its children, and load traffic values that belongs to them, aggregating them by year and month.
                $children = ActorsGetter::from($child)->selectFocus()->selectChildren(recursive: true)->getSelection();

                $traffic = MonthlyTrafficDatum::query()
                                              ->select(['year', 'month'])
                                              ->addSelect(DB::raw("SUM(`traffic`) as `traffic`"))
                                              ->addSelect(DB::raw("SUM(`temporary`) as `temporary`"))
                                              ->addSelect(DB::raw("SUM(`final_traffic`) as `final_traffic`"))
                                              ->groupBy(["year", "month"])
                                              ->whereIn("property_id", $children)
                                              ->get();

            }

            $childrenTrafficStats[] = [
                "actor_id" => $child->getKey(),
                "actor"    => $child->makeHidden("property"),
                "traffic"  => $traffic,
            ];
        }

        return new Response($childrenTrafficStats);
    }
}
