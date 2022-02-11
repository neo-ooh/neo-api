<?php

namespace Neo\Http\Controllers;

use Gate;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Neo\Enums\Capability;
use Neo\Http\Requests\PropertiesTraffic\ListTrafficRequest;
use Neo\Http\Requests\PropertiesTraffic\StoreTrafficRequest;
use Neo\Http\Requests\PropertiesTraffic\UpdatePropertyTrafficSettingsRequest;
use Neo\Jobs\Traffic\EstimateWeeklyTrafficFromMonthJob;
use Neo\Models\Property;
use Neo\Models\PropertyTrafficMonthly;

class PropertiesTrafficController extends Controller {
    public function index(ListTrafficRequest $request, Property $property): Response {

        $yearTraffic = $property->traffic->data()
                                         ->orderBy("month")
                                         ->get()
                                         ->groupBy("year");

        return new Response($yearTraffic);
    }

    public function store(StoreTrafficRequest $request, Property $property): Response {
        $values = ["traffic" => $request->input("traffic")];

        if (Gate::allows(Capability::properties_edit) && $request->has("temporary")) {
            $values["temporary"] = $request->input("temporary");

            if ($values["traffic"] === null && $values["temporary"] === null) {
                // remove the record instead of adding it
                PropertyTrafficMonthly::query()->where([
                    "property_id" => $property->actor_id,
                    "year"        => $request->input("year"),
                    "month"       => $request->input("month"),
                ])->delete();

                EstimateWeeklyTrafficFromMonthJob::dispatch($property->getKey(), $request->input("year"), $request->input("month") + 1);

                $property->touch("last_review_at");

                return new Response([], 200);
            }
        }


        $traffic = PropertyTrafficMonthly::query()->updateOrCreate([
            "property_id" => $property->actor_id,
            "year"        => $request->input("year"),
            "month"       => $request->input("month"),
        ], $values);

        EstimateWeeklyTrafficFromMonthJob::dispatch($property->getKey(), $request->input("year"), $request->input("month") + 1);

        $property->touch("last_review_at");

        return new Response($traffic, 201);
    }

    public function update(UpdatePropertyTrafficSettingsRequest $request, Property $property): Response {
        $trafficSettings                         = $property->traffic;
        $trafficSettings->is_required            = $request->input("is_required");
        $trafficSettings->start_year             = $request->input("start_year");
        $trafficSettings->grace_override         = $request->input("grace_override");
        $trafficSettings->input_method           = $request->input("input_method");
        $trafficSettings->missing_value_strategy = $request->input("missing_value_strategy");
        $trafficSettings->placeholder_value      = $request->input("placeholder_value");

        $forcePull = $trafficSettings->getOriginal("input_method") === 'MANUAL' && $trafficSettings->input_method === 'LINKETT';

        $trafficSettings->save();

        $trafficSettings->source()->sync([]);

        if ($trafficSettings->input_method === 'LINKETT') {
            $trafficSettings->source()
                            ->attach($request->input("source_id"), [
                                "uid" => $request->input("venue_id")
                            ]);
        }

        Cache::forget($trafficSettings->getRollingWeeklyTrafficCacheKey());

        if ($forcePull) {
            Artisan::queue("property:pull-traffic $property->actor_id");
        }

        return new Response($trafficSettings->load(["source"]));
    }

}
