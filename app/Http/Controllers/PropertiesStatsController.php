<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - PropertiesStatsController.php
 */

namespace Neo\Http\Controllers;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Response;
use Neo\Http\Requests\PropertiesStatistics\ShowPropertiesStatisticsRequest;
use Neo\Models\Actor;

class PropertiesStatsController {
    public function show(ShowPropertiesStatisticsRequest $request, Actor $actor) {
        if ($actor->is_property) {
            return new Response("Property cannot be used on this endpoint", 400);
        }

        // Is there any children bellow ?
        /** @var Collection $childGroups */
        $childGroups = $actor->selectActors()
                             ->directChildren()
                             ->where("is_group", "=", true)
                             ->orderBy("name")
                             ->get();

        if ($childGroups->isEmpty()) {
            // No group children, and not a property, return 404;
            return new Response(null, 404);
        }

        $actor->properties = $childGroups->append("compound_traffic");

        $actor->properties->makeHidden("property");

        return new Response($actor);
    }
}
