<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - AddressController.php
 */

namespace Neo\Modules\Properties\Http\Controllers;

use Illuminate\Http\Response;
use MatanYadaev\EloquentSpatial\Objects\Point;
use Neo\Http\Controllers\Controller;
use Neo\Jobs\PullAddressGeolocationJob;
use Neo\Models\Address;
use Neo\Models\City;
use Neo\Models\Province;
use Neo\Modules\Properties\Http\Requests\Address\UpdateAddressRequest;
use Neo\Modules\Properties\Models\Property;

class AddressController extends Controller {
    public function update(UpdateAddressRequest $request, Property $property) {
        /** @var Province $province */
        $province = Province::query()
                            ->where("slug", "=", $request->input("province"))
                            ->first();

        /** @var City $city */
        $city = City::query()->firstOrCreate([
                                                 "name"        => $request->input("city"),
                                                 "province_id" => $province->id,
                                             ]);

        $address           = $property->address ?? new Address();
        $address->line_1   = trim($request->input("line_1"));
        $address->line_2   = trim($request->input("line_2"));
        $address->city_id  = $city->id;
        $address->zipcode  = trim($request->input("zipcode", $address->zipcode ?? ""));
        $address->timezone = $request->input("timezone", $address->timezone) ?? "";

        if ($request->has("longitude") && $request->has("latitude")) {
            $address->geolocation = new Point($request->input("latitude"), $request->input("longitude"));
        }

        $address->save();

        $property->address()->associate($address);
        $property->save();

        if ($request->input("refresh_geo")) {
            PullAddressGeolocationJob::dispatchSync($address);
        }

        return new Response($address);
    }
}
