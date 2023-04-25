<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - PullCityGeolocationJob.php
 */

namespace Neo\Jobs;

use Geocoder\Laravel\ProviderAndDumperAggregator;
use Geocoder\Provider\Geonames\Model\GeonamesAddress;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use MatanYadaev\EloquentSpatial\Objects\Point;
use Neo\Models\City;

class PullCityGeolocationJob implements ShouldQueue {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(protected readonly int $cityID) {
    }

    public function handle(): void {
        /** @var City|null $city */
        $city = City::query()->find($this->cityID);

        if (!$city) {
            return;
        }

        $cityString = $city->name . ", " . $city->province->slug . ", CA";

        /** @var ProviderAndDumperAggregator $geocoder */
        $geocoder = app('geocoder')->using("geonames");
        /** @var Collection<GeonamesAddress> $results */
        $results = $geocoder->geocode($cityString)->get();
        /** @var GeonamesAddress|null $location */
        $location = $results->firstWhere(fn(GeonamesAddress $address) => $address->getFcode() === 'PPL') ?? $results->first();

        if (!$location) {
            return;
        }

        $city->geolocation = new Point($location->getCoordinates()->getLatitude(), $location->getCoordinates()->getLongitude());
        $city->save();

        MatchCityWithMarketJob::dispatch($city->getKey());
    }
}
