<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - MonthlyTrafficController.php
 */

namespace Neo\Modules\Properties\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;
use Neo\Enums\Capability;
use Neo\Http\Controllers\Controller;
use Neo\Modules\Properties\Http\Requests\PropertiesTraffic\StoreTrafficRequest;
use Neo\Modules\Properties\Jobs\Traffic\EstimateWeeklyTrafficFromMonthJob;
use Neo\Modules\Properties\Models\MonthlyTrafficDatum;
use Neo\Modules\Properties\Models\Property;

class MonthlyTrafficController extends Controller {
	public function store(StoreTrafficRequest $request, Property $property) {
		$values = ["traffic" => $request->input("traffic")];

		if (Gate::allows(Capability::properties_traffic_manage->value) && $request->has("temporary")) {
			$values["temporary"] = $request->input("temporary");

			if ($values["traffic"] === null && $values["temporary"] === null) {
				// remove the record instead of adding it
				MonthlyTrafficDatum::query()->where([
					                                    "property_id" => $property->actor_id,
					                                    "year"        => $request->input("year"),
					                                    "month"       => $request->input("month"),
				                                    ])->delete();

				EstimateWeeklyTrafficFromMonthJob::dispatch($property->getKey(), $request->input("year"), $request->input("month") + 1);

				$property->touch("last_review_at");

				return new Response([], 200);
			}
		}


		$traffic = MonthlyTrafficDatum::query()->updateOrCreate([
			                                                        "property_id" => $property->actor_id,
			                                                        "year"        => $request->input("year"),
			                                                        "month"       => $request->input("month"),
		                                                        ], $values);

		EstimateWeeklyTrafficFromMonthJob::dispatch($property->getKey(), $request->input("year"), $request->input("month") + 1);

		return new Response($traffic, 201);
	}
}
